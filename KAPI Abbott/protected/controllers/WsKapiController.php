<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of WsKapiController
 *
 * @author elperez
 */
class WsKapiController extends Controller {

    public $acc_id = 282;
    public $status = 0;

    public function actionGetTerminalInfo() {
        $request = $this->_readJsonRequest();
        $isPlaying = '';
        $playingBalance = '';
        $playerMode = '';
        $currentCasino = '';
        $mappedCasinos = '';
        $minmaxAmount = '';
        $sessionMode = '';
        $membershipCardNo = '';
        $siteCode = '';
        $startDateTime = '';
        $stackerBatchID = '';
        $isStarted = '';
        $transMsg = '';
        $errCode = '';
        $siteName = '';

        $terminalName = trim(trim($request['TerminalName']));
        //@author JunJun S. Hernandez
        //Check if Terminal Name is not blank. If not blank then
        if (isset($terminalName) && $terminalName != '') {
            $terminalName = htmlentities($terminalName);

            //If Terminal Name is valid. If valid then
            if (Utilities::validateInput($terminalName)) {

                //Start of declaration of models to be used.
                $terminalsModel         = new TerminalsModel();
                $terminalSessionsModel  = new TerminalSessionsModel();
                $terminalServicesModel  = new TerminalServicesModel();
                $siteDenominationModel  = new SiteDenominationModel();
                $gamingSessionModel     = new GamingSessionsModel();
                $commonController       = new CommonController();
                $sitesModel             = new SitesModel();
                $membercards            = new MemberCardsModel();
                //End of declaration of models to be used.
                //Check if terminal is EGM
                $_terminalName = Yii::app()->params['SitePrefix'] . $terminalName;
                $terminalID = $terminalsModel->getTerminalSiteIDSolo($_terminalName);
                $terminaltype = $terminalsModel->checkTerminalType($terminalID['TerminalID']);

                if ($terminaltype == 1) {
                    $sc = Yii::app()->params['SitePrefix'] . $request['TerminalName'];
                    $TerminalID = array();
                    //Load data with the use of models.
                    $TerminalID = $terminalsModel->getTerminalIDByCode($sc);

                    //Check if the Terminal  Exists
                    if (!empty($TerminalID)) {
                        //Check if the Site is Active
                        $sitestat = $sitesModel->checkStatusByTerminal($TerminalID[0]['TerminalID']);
                        $TerminalDetails = $terminalsModel->getTerminalSiteIDSolo($sc);
                        if ($sitestat['Status'] == 1) {
                            //Check Terminal if found by TerminalID which is not empty. If it exists or not empty then,
                            if (!empty($TerminalID)) {
                                //Check if Terminal is active
                                if ($TerminalID[0]['Status'] == 1) {
                                    //Check if Terminal has Mapped Casino
                                    $cnt_mapped = $terminalServicesModel->checkHasMappedCasino($TerminalDetails['TerminalID']);
                                    if ($cnt_mapped['cnt'] > 0) {
                                        //Check Active EGM Session
                                        $isactiveEgmSession = $gamingSessionModel->chkActiveEgmSession($TerminalID[0]['TerminalID']);
                                        //if no egm in regular, try vip
                                        if ($isactiveEgmSession ==  false) {
                                            $isactiveEgmSession = $gamingSessionModel->chkActiveEgmSession($TerminalID[1]['TerminalID']);
                                        } 
                                        if (!empty($isactiveEgmSession)) {
                                            $isStarted = 1;
                                            $stackerBatchID = $isactiveEgmSession['StackerBatchID'];

                                            $isPlaying = $terminalSessionsModel->isSessionActive($TerminalID[0]['TerminalID']);
                                            $siteID = $terminalsModel->getSiteIDByTerminalID($TerminalID[0]['TerminalID']);

                                            //Check if session is active. If active then
                                            if ($isPlaying > 0) {
                                                $TerminalID = $TerminalID[0]['TerminalID'];
                                                $pm = $terminalsModel->checkVIP($TerminalID);
                                                $sitec = $sitesModel->getSiteCode($siteID);
                                                $siteName = $sitesModel->getSiteNameByTerminalID($siteID);
                                                $currentCasino = $terminalSessionsModel->getCurrentCasino($TerminalID);
                                                $mappedCasinos = $terminalServicesModel->getCasinoByTerminal($TerminalID);
                                                $serviceID = $terminalSessionsModel->getServiceID($TerminalID);
                                                $siteCode = substr($sitec, 5);
                                                $sessionMode = $terminalSessionsModel->getPlayerMode($TerminalID);
                                                //Get Playing Balance
                                                if ($sessionMode == 0) {
                                                    $ServiceID = $terminalSessionsModel->getServiceID($TerminalID);
                                                    $amount = $terminalSessionsModel->getCurrentBalance($TerminalID, $serviceID);
                                                }
                                                //If User-based then
                                                else {
                                                    $cardnumber = $terminalSessionsModel->getCardNumber($TerminalID);
                                                    if (isset($cardnumber)) {
                                                        $ServiceID = $terminalSessionsModel->getServiceID($TerminalID);
                                                        $return_transfer = 1;
                                                        $amount = $commonController->getPlayingBalanceUserBased($TerminalID, $ServiceID, $cardnumber, $return_transfer, $sessionMode);
                                                    } else {
                                                        $amount = 0;
                                                        $message = 'Card Info not found.';
                                                        $errCode = 4;
                                                    }
                                                }
                                                $playingBalance = $amount;
                                                //If Regular then
                                                if ($pm == 0) {
                                                    $playerMode = 1;
                                                }
                                                //If VIP then
                                                else {
                                                    $playerMode = 2;
                                                }
                                                $minmaxAmount1 = $siteDenominationModel->getRegMinMaxInfoWithAlias($siteID);
                                                $minmaxAmount2 = $siteDenominationModel->getVIPMinMaxInfoWithAlias($siteID);

                                                foreach ($minmaxAmount1 as $regVal) {
                                                    $regVal1 = array("RegMin" => (float) $regVal['RegMin']);
                                                    $regVal2 = array("RegMax" => (float) $regVal['RegMax']);
                                                    $regVal = array_merge($regVal1, $regVal2);
                                                    foreach ($minmaxAmount2 as $vipVal) {
                                                        $vipVal1 = array("VIPMin" => (float) $vipVal['VIPMin']);
                                                        $vipVal2 = array("VIPMax" => (float) $vipVal['VIPMax']);
                                                        $vipVal = array_merge($vipVal1, $vipVal2);
                                                        $minmaxAmount = array_merge($regVal, $vipVal);
                                                    }
                                                }
                                                
                                                $message = "Success";
                                                $membershipCardNo = $terminalSessionsModel->getLoyaltyCardNumber($TerminalID);
                                                $startDateTime = $terminalSessionsModel->getStartDateTime($TerminalID);
                                                $this->_sendResponse(200, CommonController::getTerminalInfoResponse($isStarted, $isPlaying, $playingBalance, $playerMode, $currentCasino, $mappedCasinos, $minmaxAmount, $sessionMode, $membershipCardNo, $siteCode, $startDateTime, $stackerBatchID, $message, $errCode, $siteName));
                                            } else {
                                                if (isset($TerminalID[1]['TerminalID'])) {
                                                    $isPlayingVIP = $terminalSessionsModel->isSessionActive($TerminalID[1]['TerminalID']);

                                                    //Check if VIP session is active. If active then
                                                    if ($isPlayingVIP > 0) {
                                                        $TerminalID = $TerminalID[1]['TerminalID'];
                                                        $pm = $terminalsModel->checkVIP($TerminalID);
                                                        $sitec = $sitesModel->getSiteCode($siteID);
                                                        $siteName = $sitesModel->getSiteNameByTerminalID($siteID);
                                                        $currentCasino = $terminalSessionsModel->getCurrentCasino($TerminalID);
                                                        $mappedCasinos = $terminalServicesModel->getCasinoByTerminal($TerminalID);
                                                        $serviceID = $terminalSessionsModel->getServiceID($TerminalID);
                                                        $siteCode = substr($sitec, 5);
                                                        $sessionMode = $terminalSessionsModel->getPlayerMode($TerminalID);
                                                        //Get Playing Balance
                                                        if ($sessionMode == 0) {
                                                            $ServiceID = $terminalSessionsModel->getServiceID($TerminalID);
                                                            $amount = $commonController->getPlayingBalanceByID($TerminalID, $ServiceID);
                                                        }
                                                        //If User-based then
                                                        else {
                                                            $cardnumber = $terminalSessionsModel->getCardNumber($TerminalID);
                                                            if (isset($cardnumber)) {
                                                                $ServiceID = $terminalSessionsModel->getServiceID($TerminalID);
                                                                $return_transfer = 1;
                                                                $amount = $commonController->getPlayingBalanceUserBased($TerminalID, $ServiceID, $cardnumber, $return_transfer, $sessionMode);
                                                            } else {
                                                                $amount = 0;
                                                                $message = 'Card Info not found.';
                                                                $errCode = 4;
                                                            }
                                                        }
                                                        $playingBalance = $amount;
                                                        //If Regular then
                                                        if ($pm == 0) {
                                                            $playerMode = 1;
                                                        }
                                                        //If VIP then
                                                        else {
                                                            $playerMode = 2;
                                                        }
                                                        $minmaxAmount1 = $siteDenominationModel->getRegMinMaxInfoWithAlias($siteID);
                                                        $minmaxAmount2 = $siteDenominationModel->getVIPMinMaxInfoWithAlias($siteID);

                                                        foreach ($minmaxAmount1 as $regVal) {
                                                            $regVal1 = array("RegMin" => (float) $regVal['RegMin']);
                                                            $regVal2 = array("RegMax" => (float) $regVal['RegMax']);
                                                            $regVal = array_merge($regVal1, $regVal2);
                                                            foreach ($minmaxAmount2 as $vipVal) {
                                                                $vipVal1 = array("VIPMin" => (float) $vipVal['VIPMin']);
                                                                $vipVal2 = array("VIPMax" => (float) $vipVal['VIPMax']);
                                                                $vipVal = array_merge($vipVal1, $vipVal2);
                                                                $minmaxAmount = array_merge($regVal, $vipVal);
                                                            }
                                                        }
                                                        $message = "Success";
                                                        $membershipCardNo = $terminalSessionsModel->getLoyaltyCardNumber($TerminalID);
                                                        $startDateTime = $terminalSessionsModel->getStartDateTime($TerminalID);
                                                        $this->_sendResponse(200, CommonController::getTerminalInfoResponse($isStarted, $isPlayingVIP, $playingBalance, $playerMode, $currentCasino, $mappedCasinos, $minmaxAmount, $sessionMode, $membershipCardNo, $siteCode, $startDateTime, $stackerBatchID, $message, $errCode, $siteName));
                                                    }//If no active session then
                                                    else {
                                                        $siteID = $terminalsModel->getSiteIDByTerminalID($TerminalID[0]['TerminalID']);
                                                        $siteName = $sitesModel->getSiteNameByTerminalID($siteID);
                                                        $mappedCasinos = $terminalServicesModel->getCasinoByTerminal($TerminalID[0]['TerminalID']);
                                                        $minmaxAmount1 = $siteDenominationModel->getRegMinMaxInfoWithAlias($siteID);
                                                        $minmaxAmount2 = $siteDenominationModel->getVIPMinMaxInfoWithAlias($siteID);
                                                        foreach ($minmaxAmount1 as $regVal) {
                                                            $regVal1 = array("RegMin" => (float) $regVal['RegMin']);
                                                            $regVal2 = array("RegMax" => (float) $regVal['RegMax']);
                                                            $regVal = array_merge($regVal1, $regVal2);
                                                            foreach ($minmaxAmount2 as $vipVal) {
                                                                $vipVal1 = array("VIPMin" => (float) $vipVal['VIPMin']);
                                                                $vipVal2 = array("VIPMax" => (float) $vipVal['VIPMax']);
                                                                $vipVal = array_merge($vipVal1, $vipVal2);
                                                                $minmaxAmount = array_merge($regVal, $vipVal);
                                                            }
                                                        }
                                                        //get MID in egmsessions
                                                        $mid = $gamingSessionModel->getMIDByTerminalID($TerminalID[0]['TerminalID']);
                                                        if ($mid == false)
                                                        {
                                                            $mid = $gamingSessionModel->getMIDByTerminalID($TerminalID[1]['TerminalID']);
                                                        }
                                                        //get card number
                                                        $membershipCardNo = $membercards->getCardNumber($mid['MID']);
                                                        
                                                        $sitec = $sitesModel->getSiteCode($siteID);
                                                        $message = "Success";
                                                        $playingBalance = 0;
                                                        $playerMode = 0;
                                                        $currentCasino = 0;
                                                        $siteCode = substr($sitec, 5);
                                                        $sessionMode = 0;
                                                        $startDateTime = '';
                                                        $errCode = 0;
                                                        $this->_sendResponse(200, CommonController::getTerminalInfoResponse(1, $isPlaying, $playingBalance, $playerMode, $currentCasino, $mappedCasinos, $minmaxAmount, $sessionMode, $membershipCardNo, $siteCode, $startDateTime, '', $message, $errCode, $siteName));
                                                    }
                                                } else {
                                                    $message = "Cannot find Terminal.";
                                                    $errCode = 3;
                                                    $this->_sendResponse(200, CommonController::getTerminalInfoResponse(0, $isPlaying, $playingBalance, $playerMode, $currentCasino, $mappedCasinos, $minmaxAmount, $sessionMode, $membershipCardNo, $siteCode, $startDateTime, '', $message, $errCode, $siteName));
                                                }
                                            }
                                        } else {
                                            $siteID = $terminalsModel->getSiteIDByTerminalID($TerminalID[0]['TerminalID']);
                                            $siteName = $sitesModel->getSiteNameByTerminalID($siteID);
                                            $mappedCasinos = $terminalServicesModel->getCasinoByTerminal($TerminalID[0]['TerminalID']);
                                            $minmaxAmount1 = $siteDenominationModel->getRegMinMaxInfoWithAlias($siteID);
                                            $minmaxAmount2 = $siteDenominationModel->getVIPMinMaxInfoWithAlias($siteID);
                                            foreach ($minmaxAmount1 as $regVal) {
                                                $regVal1 = array("RegMin" => (float) $regVal['RegMin']);
                                                $regVal2 = array("RegMax" => (float) $regVal['RegMax']);
                                                $regVal = array_merge($regVal1, $regVal2);
                                                foreach ($minmaxAmount2 as $vipVal) {
                                                    $vipVal1 = array("VIPMin" => (float) $vipVal['VIPMin']);
                                                    $vipVal2 = array("VIPMax" => (float) $vipVal['VIPMax']);
                                                    $vipVal = array_merge($vipVal1, $vipVal2);
                                                    $minmaxAmount = array_merge($regVal, $vipVal);
                                                }
                                            }
                                            $sitec = $sitesModel->getSiteCode($siteID);
                                            $playingBalance = 0;
                                            $playerMode = 0;
                                            $currentCasino = 0;
                                            $membershipCardNo = '';
                                            $siteCode = substr($sitec, 5);
                                            $sessionMode = 0;
                                            $startDateTime = '';
                                            $message = "Terminal has no active EGM session.";
                                            $errCode = 50;
                                            $this->_sendResponse(200, CommonController::getTerminalInfoResponse(0, $isPlaying, $playingBalance, $playerMode, $currentCasino, $mappedCasinos, $minmaxAmount, $sessionMode, $membershipCardNo, $siteCode, $startDateTime, '', $message, $errCode, $siteName));
                                        }
                                    } else {
                                        $message = "There are no mapped casino in this terminal.";
                                        $errCode = 49;
                                        $this->_sendResponse(200, CommonController::getTerminalInfoResponse(0, $isPlaying, $playingBalance, $playerMode, $currentCasino, $mappedCasinos, $minmaxAmount, $sessionMode, $membershipCardNo, $siteCode, $startDateTime, '', $message, $errCode, $siteName));
                                    }
                                } else {
                                    $message = "Terminal is Inactive.";
                                    $errCode = 48;
                                    $this->_sendResponse(200, CommonController::getTerminalInfoResponse(0, $isPlaying, $playingBalance, $playerMode, $currentCasino, $mappedCasinos, $minmaxAmount, $sessionMode, $membershipCardNo, $siteCode, $startDateTime, '', $message, $errCode, $siteName));
                                }
                            }
                            //If TerminalID was empty means it is not found. If it doesn't exists or empty then
                            else {
                                $message = "Cannot find Terminal.";
                                $errCode = 3;
                                $this->_sendResponse(200, CommonController::getTerminalInfoResponse(0, $isPlaying, $playingBalance, $playerMode, $currentCasino, $mappedCasinos, $minmaxAmount, $sessionMode, $membershipCardNo, $siteCode, $startDateTime, '', $message, $errCode, $siteName));
                            }
                        } else {
                            $message = "Site is Inactive.";
                            $errCode = 56;
                            $this->_sendResponse(200, CommonController::getTerminalInfoResponse(0, $isPlaying, $playingBalance, $playerMode, $currentCasino, $mappedCasinos, $minmaxAmount, $sessionMode, $membershipCardNo, $siteCode, $startDateTime, '', $message, $errCode, $siteName));
                        }
                    } else {
                        $message = "Cannot find Terminal.";
                        $errCode = 3;
                        $this->_sendResponse(200, CommonController::getTerminalInfoResponse(0, $isPlaying, $playingBalance, $playerMode, $currentCasino, $mappedCasinos, $minmaxAmount, $sessionMode, $membershipCardNo, $siteCode, $startDateTime, '', $message, $errCode, $siteName));
                    }
                } else {
                    $message = "Terminal type is not EGM.";
                    $errCode = 57;
                    $this->_sendResponse(200, CommonController::getTerminalInfoResponse(0, $isPlaying, $playingBalance, $playerMode, $currentCasino, $mappedCasinos, $minmaxAmount, $sessionMode, $membershipCardNo, $siteCode, $startDateTime, '', $message, $errCode, $siteName));
                }
                $sc = Yii::app()->params['SitePrefix'] . $request['TerminalName'];
                $TerminalID = array();
                //Load data with the use of models.
                $TerminalID = $terminalsModel->getTerminalIDByCode($sc);
            }
            //If Terminal Name is invalid. If invalid then
            else {
                $message = "Terminal Name may contain special characters.";
                $errCode = 2;
                $this->_sendResponse(200, CommonController::getTerminalInfoResponse(0, $isPlaying, $playingBalance, $playerMode, $currentCasino, $mappedCasinos, $minmaxAmount, $sessionMode, $membershipCardNo, $siteCode, $startDateTime, '', $message, $errCode, $siteName));
            }
        }
        //If Terminal Name is blank. If blank then
        else {
            $message = "Terminal Name is not set or blank.";
            $errCode = 1;
            $this->_sendResponse(200, CommonController::getTerminalInfoResponse(0, $isPlaying, $playingBalance, $playerMode, $currentCasino, $mappedCasinos, $minmaxAmount, $sessionMode, $membershipCardNo, $siteCode, $startDateTime, '', $message, $errCode, $siteName));
        }
        //End @author
    }

    //@author JunJun S. Hernandez
    public function actionGetPlayingBalance() {
        $request = $this->_readJsonRequest();
        $TerminalID = '';
        $siteID = '';
        $isPlaying = '';
        $amount = 0;
        $message = '';
        $errCode = '';

        $terminalName = trim($request['TerminalName']);
        //Check if Terminal Name is not blank. If not blank then
        if (isset($terminalName) && $terminalName != '') {
            $terminalName = htmlentities($terminalName);

            //If Terminal Name is valid. If valid then
            if (Utilities::validateInput($terminalName)) {

                //Start of declaration of models to be used.
                $commonController = new CommonController();
                $terminalsModel = new TerminalsModel();
                $terminalSessionsModel = new TerminalSessionsModel();
                //End of declaration of models to be used.

                $sc = Yii::app()->params['SitePrefix'] . $request['TerminalName'];

                //Load data with the use of models.
                $TerminalID = $terminalsModel->getTerminalIDByCode($sc);

                //Check Terminal if found by TerminalID which is not empty. If it exists or not empty then,
                if (!empty($TerminalID)) {

                    $CheckTerminalType = $terminalsModel->getTerminalIDByCodeEGMType($sc);

                    if (!empty($CheckTerminalType)) {

                        $isPlaying = $terminalSessionsModel->isSessionActive($TerminalID[0]['TerminalID']);

                        $siteID = $terminalsModel->getSiteIDByTerminalID($TerminalID[0]['TerminalID']);

                        //Check if session is active. If active then
                        if ($isPlaying > 0) {
                            $TerminalID = $TerminalID[0]['TerminalID'];
                            $sessionMode = $terminalSessionsModel->getPlayerMode($TerminalID);
                            //Check if UserMode is Terminal-based. If Terminal-based then
                            if ($sessionMode == 0) {
                                $ServiceID = $terminalSessionsModel->getServiceID($TerminalID);
                                $message = 'Success';
                                $amount = $commonController->getPlayingBalanceByID($TerminalID, $ServiceID);
                            }
                            //If User-based then
                            else {
                                $cardnumber = $terminalSessionsModel->getCardNumber($TerminalID);
                                if (isset($cardnumber)) {
                                    $ServiceID = $terminalSessionsModel->getServiceID($TerminalID);
                                    $return_transfer = 1;
                                    $amount = $commonController->getPlayingBalanceUserBased($TerminalID, $ServiceID, $cardnumber, $return_transfer, $sessionMode);
                                    $message = 'Success';
                                } else {
                                    $amount = 0;
                                    $message = 'Card Info not found.';
                                    $errCode = 4;
                                }
                            }
                        }
                        //If no active session then
                        else {
                            if (isset($TerminalID[1]['TerminalID'])) {
                                $isPlaying = $terminalSessionsModel->isSessionActive($TerminalID[1]['TerminalID']);
                                $siteID = $terminalsModel->getSiteIDByTerminalID($TerminalID[1]['TerminalID']);
                                $TerminalID = $TerminalID[1]['TerminalID'];
                                if ($isPlaying > 0) {
                                    $sessionMode = $terminalSessionsModel->getPlayerMode($TerminalID);
                                    //Check if UserMode is Terminal-based. If Terminal-based then
                                    if ($sessionMode == 0) {
                                        $ServiceID = $terminalSessionsModel->getServiceID($TerminalID);
                                        $message = 'Success';
                                        $amount = $commonController->getPlayingBalanceByID($TerminalID, $ServiceID);
                                    }
                                    //If User-based then
                                    else {
                                        $cardnumber = $terminalSessionsModel->getCardNumber($TerminalID);
                                        if (isset($cardnumber)) {
                                            $ServiceID = $terminalSessionsModel->getServiceID($TerminalID);
                                            $return_transfer = 1;
                                            $amount = $commonController->getPlayingBalanceUserBased($TerminalID, $ServiceID, $cardnumber, $return_transfer, $sessionMode);
                                            $message = 'Success';
                                        } else {
                                            $amount = 0;
                                            $message = 'Card Info not found.';
                                            $errCode = 4;
                                        }
                                    }
                                } else {
                                    $amount = 0;
                                    $message = 'Terminal has no active session.';
                                    $errCode = 4;
                                }
                            } else {
                                $amount = 0;
                                $message = "Cannot find Terminal.";
                                $errCode = 3;
                            }
                        }
                        //send success response
                        $this->_sendResponse(200, CommonController::getPlayingBalanceResponse($amount, $message, $errCode));
                    } else {
                        $message = "Invalid Terminal. Please use EGM terminal";
                        $errCode = 3;
                        $this->_sendResponse(200, CommonController::getPlayingBalanceResponse($amount, $message, $errCode));
                    }
                }


                //If TerminalID was empty means it is not found. If it doesn't exists or empty then
                else {
                    $message = "Cannot find Terminal.";
                    $errCode = 3;
                    $this->_sendResponse(200, CommonController::getPlayingBalanceResponse($amount, $message, $errCode));
                }
            }
            //If Terminal Name is invalid. If invalid then
            else {
                $message = "Terminal Name may contain special characters.";
                $errCode = 2;
                $this->_sendResponse(200, CommonController::getPlayingBalanceResponse($amount, $message, $errCode));
            }
        }
        //If Terminal Name is blank. If blank then
        else {
            $message = "Terminal Name is not set or blank.";
            $errCode = 1;
            $this->_sendResponse(200, CommonController::getPlayingBalanceResponse($amount, $message, $errCode));
        }
    }

    //End @author
    //@author JunJun S. Hernandez
    public function actionGetMembershipInfo() {
        $request = $this->_readJsonRequest();
        $TerminalID = '';
        $status = '';
        $nickname = '';
        $gender = '';
        $classification = '';
        $mappedCasinos = '';
        $message = '';
        $errCode = '';

        //$terminalName = trim($request['TerminalName']);
        $cardNumber = trim($request['MembershipCardNumber']);

        //Check if Terminal Name is not blank. If not blank then
//        if (((isset($terminalName) && $terminalName) != '') && ((isset($cardNumber) && $cardNumber) != '')) {
          if (((isset($cardNumber) && $cardNumber) != '')) {
            //$terminalName = htmlentities($terminalName);
            $cardNumber = htmlentities($cardNumber);

            //If Terminal Name is valid. If valid then
//            if (Utilities::validateInput($terminalName) && Utilities::validateInput($cardNumber)) {
            if (Utilities::validateInput($cardNumber)) {
                //Start of declaration of models to be used.
                $terminalsModel = new TerminalsModel();
                $terminalSessionsModel = new TerminalSessionsModel();
                $terminalServicesModel = new TerminalServicesModel();
                $membersModel = new MembersModel();
                $memberCardsModel = new MemberCardsModel();
                $memberInfoModel = new MemberInfoModel();
                $memberServices = new MemberServicesModel();
                $refServices = new RefServicesModel();
                //End of declaration of models to be used.

                $message = "Success";
                //$sc = Yii::app()->params['SitePrefix'] . $request['TerminalName'];

                //Load data with the use of models.
                //$TerminalID = $terminalsModel->getTerminalIDByCode($sc);

                $MID = $memberCardsModel->getMID($cardNumber);
                if (!empty($MID)) {
                    $status = $memberCardsModel->getCardStatus($cardNumber); //Get Card Status
                    $memberinfo = $memberInfoModel->getMemberInfoByMID($MID);
                    $isVIP = $membersModel->isVip($MID);
                    if ($isVIP == 0) {
                        $classification = "Regular";
                    }
                    else {
                        $classification = "VIP";
                    }
                    $name = $memberinfo['NickName'];
                    if (!empty($name)) {
                        $nickname = $name;
                    } else {
                        if (!empty($memberinfo['FirstName'])) {
                            $nickname = $memberinfo['FirstName'];
                        } else {
                            $nickname = '';
                        }
                    }
                    $gender = $memberinfo['Gender'] == 1 ? "Male" : "Female";
                    //$mappedCasinos = $terminalServicesModel->getCasinoByTerminal($TerminalID);
                    $services = $memberServices->getServiceIDByMID($MID);
                    if (count($services) > 0)
                    {
                        foreach ($services as $service)
                        {
                            $mappedCasinos[] = $refServices->getServiceInfo($service['ServiceID']);
                        }
                    }
                    $message = 'Success';
                    $errCode = 0;
                } else {
                    $message = "Cannot find Card Number.";
                    $errCode = 3;
                    $status = "Card does not exist";
                }
                    $this->_sendResponse(200, CommonController::getMembershipInfo($status, $nickname, $gender, $classification, $mappedCasinos, $message, $errCode));
                            
                //Check Terminal if found by TerminalID which is not empty. If it exists or not empty then,
//                if (!empty($TerminalID)) {
//                    $isPlaying = $terminalSessionsModel->isSessionActive($TerminalID[0]['TerminalID']);
//
//                    //Check if session is active. If active then
//                    if ($isPlaying > 0) {
//                        $TerminalID = $TerminalID[0]['TerminalID'];
//                        $match = $terminalSessionsModel->getMatchedTerminalIDAndCardNumber($TerminalID, $cardNumber);
//                        $countMatch = '';
//                        if (isset($match)) {
//                            $countMatch = count($match);
//                        } else {
//                            $countMatch = 0;
//                        }
//                        if ($countMatch > 0) {
//                            $MID = $memberCardsModel->getMID($cardNumber);
//                            if (!empty($MID)) {
//                                $status = $memberCardsModel->getCardStatus($cardNumber); //Get Card Status
//                                $memberinfo = $memberInfoModel->getMemberInfoByMID($MID);
//                                $isVIP = $membersModel->isVip($MID);
//                                if ($isVIP == 0) {
//                                    $classification = "Regular";
//                                }
//                                else {
//                                    $classification = "VIP";
//                                }
//                                $name = $memberinfo['NickName'];
//                                if (!empty($name)) {
//                                    $nickname = $name;
//                                } else {
//                                    if (!empty($memberinfo['FirstName'])) {
//                                        $nickname = $memberinfo['FirstName'];
//                                    } else {
//                                        $nickname = '';
//                                    }
//                                }
//                                $gender = $memberinfo['Gender'] == 1 ? "Male" : "Female";
//                                $mappedCasinos = $terminalServicesModel->getCasinoByTerminal($TerminalID);
//                                $message = 'Success';
//                                $errCode = 0;
//                            } else {
//                                $message = "Cannot find Card Number.";
//                                $errCode = 3;
//                            }
//                            $this->_sendResponse(200, CommonController::getMembershipInfo($status, $nickname, $gender, $classification, $mappedCasinos, $message, $errCode));
//                        } else {
//                            $message = 'Terminal Name and Card Number did not match.';
//                            $errCode = 3;
//                            $this->_sendResponse(200, CommonController::getMembershipInfo($status, $nickname, $gender, $classification, $mappedCasinos, $message, $errCode));
//                        }
//                    }
//                    //If no active session then
//                    else {
//                        if (isset($TerminalID[1]['TerminalID'])) {
//                            $isPlaying = $terminalSessionsModel->isSessionActive($TerminalID[1]['TerminalID']);
//
//                            //Check if session is active. If active then
//                            if ($isPlaying > 0) {
//                                $TerminalID = $TerminalID[1]['TerminalID'];
//                                $match = $terminalSessionsModel->getMatchedTerminalIDAndCardNumber($TerminalID, $cardNumber);
//                                $countMatch = '';
//                                if (isset($match)) {
//                                    $countMatch = count($match);
//                                } else {
//                                    $countMatch = 0;
//                                }
//                                if ($countMatch > 0) {
//                                    $MID = $memberCardsModel->getMID($cardNumber);
//                                    if (!empty($MID)) {
//                                        $status = $memberCardsModel->getCardStatus($cardNumber); //Get Card Status
//                                        $memberinfo = $memberInfoModel->getMemberInfoByMID($MID);
//                                        $name = $memberinfo['NickName'];
//                                        if (!empty($name)) {
//                                            $nickname = $name;
//                                        } else {
//                                            if (!empty($memberinfo['FirstName'])) {
//                                                $nickname = $memberinfo['FirstName'];
//                                            } else {
//                                                $nickname = '';
//                                            }
//                                        }
//                                        $gender = $memberinfo['Gender'] == 1 ? "Male" : "Female";
//                                        $mappedCasinos = $terminalServicesModel->getCasinoByTerminal($TerminalID);
//                                        $message = 'Success';
//                                        $errCode = 0;
//                                    } else {
//                                        $message = "Cannot find Card Number.";
//                                        $errCode = 3;
//                                    }
//                                    $this->_sendResponse(200, CommonController::getMembershipInfo($status, $nickname, $gender, $classification, $mappedCasinos, $message, $errCode));
//                                } else {
//                                    $message = 'Terminal Name and Card Number did not match.';
//                                    $errCode = 3;
//                                    $this->_sendResponse(200, CommonController::getMembershipInfo($status, $nickname, $gender, $classification, $mappedCasinos, $message, $errCode));
//                                }
//                            }
//                            //If no active session then
//                            else {
//                                $message = 'Terminal has no active session.';
//                                $errCode = 3;
//                                $this->_sendResponse(200, CommonController::getMembershipInfo($status, $nickname, $gender, $classification, $mappedCasinos, $message, $errCode));
//                            }
//                        } else {
//                            $message = "Cannot find Terminal.";
//                            $errCode = 3;
//                            $this->_sendResponse(200, CommonController::getMembershipInfo($status, $nickname, $gender, $classification, $mappedCasinos, $message, $errCode));
//                        }
//                    }
//                }
//                //If TerminalID was empty means it is not found. If it doesn't exists or empty then
//                else {
//                    $message = "Cannot find Terminal.";
//                    $errCode = 3;
//                    $this->_sendResponse(200, CommonController::getMembershipInfo($status, $nickname, $gender, $classification, $mappedCasinos, $message, $errCode));
//                }
            }
            //If Terminal Name is invalid. If invalid then
//            else if (!Utilities::validateInput($terminalName) && Utilities::validateInput($cardNumber)) {
//                $message = "Terminal Name may contain special characters.";
//                $errCode = 2;
//                $this->_sendResponse(200, CommonController::getMembershipInfo($status, $nickname, $gender, $classification, $mappedCasinos, $message, $errCode));
//            }
            //If Card Number is invalid. If invalid then
//            else if (Utilities::validateInput($terminalName) && !Utilities::validateInput($cardNumber)) {
            else if (!Utilities::validateInput($cardNumber)) {
                $message = "Card Number may contain special characters.";
                $errCode = 2;
                $this->_sendResponse(200, CommonController::getMembershipInfo($status, $nickname, $gender, $classification, $mappedCasinos, $message, $errCode));
            }
            //If Terminal Name and Card Number is invalid. If invalid then
//            else if (!Utilities::validateInput($terminalName) && !Utilities::validateInput($cardNumber)) {
//                $message = "Terminal Name and Card Number may contain special characters.";
//                $errCode = 2;
//                $this->_sendResponse(200, CommonController::getMembershipInfo($status, $nickname, $gender, $classification, $mappedCasinos, $message, $errCode));
//            }
        }
        //If Terminal Name is blank. If blank then
//        else if (((!isset($request['TerminalName']) && $request['TerminalName']) == '') && (isset($request['MembershipCardNumber']) && $request['MembershipCardNumber']) != '') {
//            $message = "Terminal Name is not set or blank.";
//            $errCode = 1;
//            $this->_sendResponse(200, CommonController::getMembershipInfo($status, $nickname, $gender, $classification, $mappedCasinos, $message, $errCode));
//        }
        //If Card Number is blank. If blank then
//        else if (((isset($request['TerminalName']) && $request['TerminalName']) != '') && (!isset($request['MembershipCardNumber']) && $request['MembershipCardNumber']) == '') {
        else if ((!isset($request['MembershipCardNumber']) && $request['MembershipCardNumber']) == '') {
            $message = "CardNumber is not set or blank.";
            $errCode = 1;
            $this->_sendResponse(200, CommonController::getMembershipInfo($status, $nickname, $gender, $classification, $mappedCasinos, $message, $errCode));
        }
        //If Terminal Name and Card Number is blank. If blank then
//        else if (((!isset($request['TerminalName']) && $request['TerminalName']) == '') && ((!isset($request['MembershipCardNumber']) && $request['MembershipCardNumber']) == '')) {
        else if (((!isset($request['MembershipCardNumber']) && $request['MembershipCardNumber']) == '')) {
            $message = "Terminal Name and CardNumber are not set or blank.";
            $errCode = 1;
            $this->_sendResponse(200, CommonController::getMembershipInfo($status, $nickname, $gender, $classification, $mappedCasinos, $message, $errCode));
        }
    }

    //End @author
    //@author JunJun S. Hernandez
    //Check previous transaction using terminal name and tracking ID used
    public function actionCheckTransaction() {
        $request = $this->_readJsonRequest();
        $status = "";
        $datetime = "";
        $transactionid = "";
        $amount = "";
        $voucherticketbarcode = "";
        $expirationdate = "";
        $message = "";
        $errCode = "";
        $details = "";

        $terminalName = trim($request['TerminalName']);
        $trackingID = trim($request['TrackingID']);

        //Check if Terminal Name is not blank. If not blank then
        if (((isset($terminalName) && $terminalName) != '') && ((isset($trackingID) && $trackingID) != '')) {
            $terminalName = htmlentities($terminalName);
            $trackingID = htmlentities($trackingID);

            //If Terminal Name is valid. If valid then
            if (Utilities::validateInput($terminalName) && Utilities::validateInput($trackingID)) {

                //Start of declaration of models to be used.
                $terminalsModel = new TerminalsModel();
                $terminalSessionsModel = new TerminalSessionsModel();
                $gamingRequestLogs = new GamingRequestLogs();
                //End of declaration of models to be used.

                $sc = Yii::app()->params['SitePrefix'] . $terminalName;
                //Load data with the use of models.
                $terminalData = $terminalsModel->getTerminalIDByCode($sc);

                //Check Terminal if found by TerminalID which is not empty. If it exists or not empty then,
                if (!empty($terminalData)) {
                    $TerminalID = $terminalData[0]['TerminalID'];
                    $transactionDetails1 = $gamingRequestLogs->getDetailsByTerminalAndTrackingID($TerminalID, $trackingID);
                    
                    if(!empty($transactionDetails1)) {
                        $details = $transactionDetails1;
                    } else {
                        $TerminalID = $terminalData[1]['TerminalID'];
                        $transactionDetails2 = $gamingRequestLogs->getDetailsByTerminalAndTrackingID($TerminalID, $trackingID);
                        $details = $transactionDetails2;
                    }
                    
                    if (!empty($details) || $details!="" || $details!=false) {
                        $status = $details['Status'];

                        switch ($status) {
                            case 0 :
                                $strstatus = 'Pending';
                                break;
                            case 1 :
                                $strstatus = 'Sucess';
                                break;
                            case 2 :
                                $strstatus = 'Failed';
                                break;
                            case 3 :
                                $strstatus = 'Fulfillment Approved';
                                break;
                            case 4 :
                                $strstatus = 'Fulfillment Denied';
                                break;
                        }

                        if (isset($details['DateCreated'])) {
                            $datetime = $details['DateCreated'];
                        } else {
                            $datetime = '';
                        }
                        if (isset($details['TransactionDetailsID'])) {
                            $transactionid = $details['TransactionDetailsID'];
                        } else {
                            $transactionid = '';
                        }
                        if (isset($details['TransactionDetailsID'])) {
                            $transactionid = $details['TransactionDetailsID'];
                        } else {
                            $transactionid = '';
                        }
                        if (isset($details['ReportedAmount'])) {
                            $amount = $details['ReportedAmount'];
                        } else {
                            $amount = '';
                        }
                        if (isset($details['Option1'])) {
                            $voucherticketbarcode = $details['Option1'];
                        } else {
                            $voucherticketbarcode = '';
                        }
                        if (isset($details['Option2'])) {
                            $expirationdate = $details['Option2'];
                        } else {
                            $expirationdate = '';
                        }

                        $message = 'Success.';
                        $this->_sendResponse(200, CommonController::checkTransaction($status, $datetime, $transactionid, $amount, $voucherticketbarcode, $expirationdate, $message, $errCode));
                    } else {
                        $message = 'Terminal Name and TrackingID did not match.';
                        $errCode = 15;
                        $this->_sendResponse(200, CommonController::checkTransaction($status, $datetime, $transactionid, $amount, $voucherticketbarcode, $expirationdate, $message, $errCode));
                    }
                } else {
                    $message = 'Cannot find Terminal.';
                    $errCode = 7;
                    $this->_sendResponse(200, CommonController::checkTransaction($status, $datetime, $transactionid, $amount, $voucherticketbarcode, $expirationdate, $message, $errCode));
                }
            }
            //If Terminal Name is invalid. If invalid then
            else if (!Utilities::validateInput($terminalName) && Utilities::validateInput($trackingID)) {
                $message = "Terminal Name may contain special characters.";
                $errCode = 2;
                $this->_sendResponse(200, CommonController::checkTransaction($status, $datetime, $transactionid, $amount, $voucherticketbarcode, $expirationdate, $message, $errCode));
            }
            //If Tracking ID is invalid. If invalid then
            else if (Utilities::validateInput($terminalName) && !Utilities::validateInput($trackingID)) {
                $message = "Tracking ID may contain special characters.";
                $errCode = 11;
                $this->_sendResponse(200, CommonController::checkTransaction($status, $datetime, $transactionid, $amount, $voucherticketbarcode, $expirationdate, $message, $errCode));
            }
            //If Terminal Name and Tracking ID is invalid. If invalid then
            else if (!Utilities::validateInput($terminalName) && !Utilities::validateInput($trackingID)) {
                $message = "Terminal Name and Tracking ID may contain special characters.";
                $errCode = 12;
                $this->_sendResponse(200, CommonController::checkTransaction($status, $datetime, $transactionid, $amount, $voucherticketbarcode, $expirationdate, $message, $errCode));
            }
        }
        //If Terminal Name is blank. If blank then
        else if (((!isset($request['TerminalName']) && $request['TerminalName']) == '') && (isset($request['TrackingID']) && $request['TrackingID']) != '') {
            $message = "Terminal Name is not set or blank.";
            $errCode = 1;
            $this->_sendResponse(200, CommonController::checkTransaction($status, $datetime, $transactionid, $amount, $voucherticketbarcode, $expirationdate, $message, $errCode));
        }
        //If Tracking ID is blank. If blank then
        else if (((isset($request['TerminalName']) && $request['TerminalName']) != '') && (!isset($request['TrackingID']) && $request['TrackingID']) == '') {
            $message = "Tracking ID is not set or blank.";
            $errCode = 13;
            $this->_sendResponse(200, CommonController::checkTransaction($status, $datetime, $transactionid, $amount, $voucherticketbarcode, $expirationdate, $message, $errCode));
        }
        //If Terminal Name and Card Number is blank. If blank then
        else if (((!isset($request['TerminalName']) && $request['TerminalName']) == '') && ((!isset($request['TrackingID']) && $request['TrackingID']) == '')) {
            $message = "Terminal Name and Tracking ID are not set or blank.";
            $errCode = 14;
            $this->_sendResponse(200, CommonController::checkTransaction($status, $datetime, $transactionid, $amount, $voucherticketbarcode, $expirationdate, $message, $errCode));
        }
    }

    //End @author
    //Provide Login Information casino serivce password
    //@author JunJun S. Hernandez
    public function actionGetLoginInfo() {
        $request = $this->_readJsonRequest();
        $login = "";
        $hashedpassword = "";
        $plainpassword = "";
        $message = "";
        $errCode = "";

        $terminalName = trim($request['TerminalName']);
        $casinoID = trim($request['CasinoID']);

        //Check if Terminal Name is not blank. If not blank then
        if (((isset($terminalName) && $terminalName) != '') && ((isset($casinoID) && $casinoID) != '')) {
            $terminalName = htmlentities($terminalName);
            $casinoID = htmlentities($casinoID);

            //If Terminal Name is valid. If valid then
            if (Utilities::validateInput($terminalName) && Utilities::validateInput($casinoID)) {

                //Start of declaration of models to be used.
                $terminalsModel = new TerminalsModel();
                $terminalSessionsModel = new TerminalSessionsModel();
                $terminalServicesModel = new TerminalServicesModel();
                $memberServicesModel = new MemberServicesModel();
                $memberCardsModel = new MemberCardsModel();
                //End of declaration of models to be used.

                $sc = Yii::app()->params['SitePrefix'] . $terminalName;
                //Load data with the use of models.
                $TerminalID = $terminalsModel->getTerminalIDByCode($sc);
                $TerminalDetails = $terminalsModel->getTerminalSiteIDSolo($sc);
                //Check Terminal if found by TerminalID which is not empty. If it exists or not empty then,
                if (!empty($TerminalID)) {
                    //Check if terminal is Active
                    if ($TerminalID[0]['Status'] == 1) {
                        //Check if terminal is mapped
                        $cnt_mapped = $terminalServicesModel->checkHasMappedCasino($TerminalDetails['TerminalID']);
                        if ($cnt_mapped['cnt'] > 0) {

                            $CheckTerminalType = $terminalsModel->getTerminalIDByCodeEGMType($sc);

                            if (!empty($CheckTerminalType)) {

                                $isPlaying = $terminalSessionsModel->isSessionActive($TerminalID[0]['TerminalID']);
                                //Check if session is active. If active then
                                if ($isPlaying > 0) {
                                    $TerminalID = $TerminalID[0]['TerminalID'];
                                    $sessionMode = $terminalSessionsModel->getPlayerMode($TerminalID);
                                    if ($sessionMode == 0) {
                                        $match = $terminalServicesModel->getMatchedTerminalAndServiceID($TerminalID, $casinoID);
                                        if ($match > 0) {
                                            $details = $terminalServicesModel->getPasswordsByTerminalAndServiceID($TerminalID, $casinoID);
                                            $login = $sc;
                                            $plainpassword = $details[0]['ServicePassword'];
                                            $hashedpassword = $details[0]['HashedServicePassword'];
                                            $message = 'Success';
                                            $errCode = 0;
                                            $this->_sendResponse(200, CommonController::getLoginInfo($login, $hashedpassword, $plainpassword, $message, $errCode));
                                        } else {
                                            $message = 'Terminal Name and Casino ID did not match.';
                                            $errCode = 20;
                                            $this->_sendResponse(200, CommonController::getLoginInfo($login, $hashedpassword, $plainpassword, $message, $errCode));
                                        }
                                    } else {
                                        $cardNumber = $terminalSessionsModel->getCardNumber($TerminalID);
                                        $MID = $memberCardsModel->getMID($cardNumber);
                                        $match = $memberServicesModel->getMatchedTerminalAndServiceID($MID, $casinoID);

                                        if ($match > 0) {
                                            $details = $memberServicesModel->getDetailsByMIDAndCasinoID($MID, $casinoID);
                                            $login = $details['ServiceUsername'];
                                            $plainpassword = $details['ServicePassword'];
                                            $hashedpassword = $details['HashedServicePassword'];
                                            $message = 'Success';
                                            $errCode = 0;
                                            $this->_sendResponse(200, CommonController::getLoginInfo($login, $hashedpassword, $plainpassword, $message, $errCode));
                                        } else {
                                            $message = 'Terminal Name and Casino ID did not match.';
                                            $errCode = 20;
                                            $this->_sendResponse(200, CommonController::getLoginInfo($login, $hashedpassword, $plainpassword, $message, $errCode));
                                        }
                                    }
                                }
                                //If no active session then
                                else {
                                    if (isset($TerminalID[1]['TerminalID'])) {
                                        $isPlaying = $terminalSessionsModel->isSessionActive($TerminalID[1]['TerminalID']);
                                        //Check if session is active. If active then
                                        if ($isPlaying > 0) {
                                            $TerminalID = $TerminalID[1]['TerminalID'];
                                            $sessionMode = $terminalSessionsModel->getPlayerMode($TerminalID);
                                            if ($sessionMode == 0) {
                                                $match = $terminalServicesModel->getMatchedTerminalAndServiceID($TerminalID, $casinoID);
                                                if ($match > 0) {
                                                    $details = $terminalServicesModel->getPasswordsByTerminalAndServiceID($TerminalID, $casinoID);
                                                    $login = $sc;
                                                    $plainpassword = $details[0]['ServicePassword'];
                                                    $hashedpassword = $details[0]['HashedServicePassword'];
                                                    $message = 'Success';
                                                    $errCode = 0;
                                                    $this->_sendResponse(200, CommonController::getLoginInfo($login, $hashedpassword, $plainpassword, $message, $errCode));
                                                } else {
                                                    $message = 'Terminal Name and Casino ID did not match.';
                                                    $errCode = 20;
                                                    $this->_sendResponse(200, CommonController::getLoginInfo($login, $hashedpassword, $plainpassword, $message, $errCode));
                                                }
                                            } else {
                                                $cardNumber = $terminalSessionsModel->getCardNumber($TerminalID);
                                                $MID = $memberCardsModel->getMID($cardNumber);
                                                $match = $memberServicesModel->getMatchedTerminalAndServiceID($MID, $casinoID);

                                                if ($match > 0) {
                                                    $details = $memberServicesModel->getDetailsByMIDAndCasinoID($MID, $casinoID);
                                                    $login = $details['ServiceUsername'];
                                                    $plainpassword = $details['ServicePassword'];
                                                    $hashedpassword = $details['HashedServicePassword'];
                                                    $message = 'Success';
                                                    $errCode = 0;
                                                    $this->_sendResponse(200, CommonController::getLoginInfo($login, $hashedpassword, $plainpassword, $message, $errCode));
                                                } else {
                                                    $message = 'Terminal Name and Casino ID did not match.';
                                                    $errCode = 20;
                                                    $this->_sendResponse(200, CommonController::getLoginInfo($login, $hashedpassword, $plainpassword, $message, $errCode));
                                                }
                                            }
                                        }
                                        //If no active session then
                                        else {
                                            $message = 'Terminal has no active session.';
                                            $errCode = 3;
                                            $this->_sendResponse(200, CommonController::getLoginInfo($login, $hashedpassword, $plainpassword, $message, $errCode));
                                        }
                                    } else {
                                        $message = "Cannot find Terminal.";
                                        $errCode = 3;
                                        $this->_sendResponse(200, CommonController::getLoginInfo($login, $hashedpassword, $plainpassword, $message, $errCode));
                                    }
                                }
                            } else {
                                $message = "Invalid Terminal. Please use EGM terminal";
                                $errCode = 3;
                                $this->_sendResponse(200, CommonController::getLoginInfo($login, $hashedpassword, $plainpassword, $message, $errCode));
                            }
                        } else {
                            $message = "There are no mapped casino in this terminal.";
                            $errCode = 49;
                            $this->_sendResponse(200, CommonController::getLoginInfo($login, $hashedpassword, $plainpassword, $message, $errCode));
                        }
                    } else {
                        $message = "Terminal is Inactive.";
                        $errCode = 48;
                        $this->_sendResponse(200, CommonController::getLoginInfo($login, $hashedpassword, $plainpassword, $message, $errCode));
                    }
                }
                //If TerminalID was empty means it is not found. If it doesn't exists or empty then
                else {
                    $message = "Cannot find Terminal.";
                    $errCode = 3;
                    $this->_sendResponse(200, CommonController::getLoginInfo($login, $hashedpassword, $plainpassword, $message, $errCode));
                }
            }
            //If Terminal Name is invalid. If invalid then
            else if (!Utilities::validateInput($terminalName) && Utilities::validateInput($casinoID)) {
                $message = "Terminal Name may contain special characters.";
                $errCode = 2;
                $this->_sendResponse(200, CommonController::getLoginInfo($login, $hashedpassword, $plainpassword, $message, $errCode));
            }
            //If Tracking ID is invalid. If invalid then
            else if (Utilities::validateInput($terminalName) && !Utilities::validateInput($casinoID)) {
                $message = "Casino ID may contain special characters.";
                $errCode = 16;
                $this->_sendResponse(200, CommonController::getLoginInfo($login, $hashedpassword, $plainpassword, $message, $errCode));
            }
            //If Terminal Name and Tracking ID is invalid. If invalid then
            else if (!Utilities::validateInput($terminalName) && !Utilities::validateInput($casinoID)) {
                $message = "Terminal Name and Casino ID may contain special characters.";
                $errCode = 17;
                $this->_sendResponse(200, CommonController::getLoginInfo($login, $hashedpassword, $plainpassword, $message, $errCode));
            }
        }
        //If Terminal Name is blank. If blank then
        else if (((!isset($request['TerminalName']) && $request['TerminalName']) == '') && (isset($request['CasinoID']) && $request['CasinoID']) != '') {
            $message = "Terminal Name is not set or blank.";
            $errCode = 1;
            $this->_sendResponse(200, CommonController::getLoginInfo($login, $hashedpassword, $plainpassword, $message, $errCode));
        }
        //If Tracking ID is blank. If blank then
        else if (((isset($request['TerminalName']) && $request['TerminalName']) != '') && (!isset($request['CasinoID']) && $request['CasinoID']) == '') {
            $message = "Casino ID is not set or blank.";
            $errCode = 18;
            $this->_sendResponse(200, CommonController::getLoginInfo($login, $hashedpassword, $plainpassword, $message, $errCode));
        }
        //If Terminal Name and Card Number is blank. If blank then
        else if (((!isset($request['TerminalName']) && $request['TerminalName']) == '') && ((!isset($request['CasinoID']) && $request['CasinoID']) == '')) {
            $message = "Terminal Name and Casino ID are not set or blank.";
            $errCode = 19;
            $this->_sendResponse(200, CommonController::getLoginInfo($login, $hashedpassword, $plainpassword, $message, $errCode));
        }
    }

    //End @author
    //Create Egm Session
    //@author gvjagolino
    public function actionCreateEgmSession() {
        $request = $this->_readJsonRequest();
        $message = "";
        $errCode = "";
        $APIName = "Create EGM Session";
        
        $membershipcardnumber = trim($request['MembershipCardNumber']);
        $terminalName = trim($request['TerminalName']);
        $casinoID = trim($request['CasinoID']);

        //Check if Terminal Name is not blank. If not blank then
        if (((isset($membershipcardnumber) && $membershipcardnumber) != '') && ((isset($terminalName) && $terminalName) != '') && ((isset($casinoID) && $casinoID) != '')) {
            $membershipcardnumber = htmlentities($membershipcardnumber);
            $terminalName = htmlentities($terminalName);
            $casinoID = htmlentities($casinoID);

            //If Terminal Name is valid. If valid then
            if (Utilities::validateInput($membershipcardnumber) && Utilities::validateInput($terminalName) && Utilities::validateInput($casinoID)) {
                
                //Start of declaration of models to be used.
                $terminalsModel = new TerminalsModel();
                $gamingSessionsModel = new GamingSessionsModel();
                $terminalServicesModel = new TerminalServicesModel();
                $memberCardsModel = new MemberCardsModel();
                $memberServicesModel = new MemberServicesModel();
                $refServices = new RefServicesModel();
                $siteaccounts = new SiteAccountsModel();
                $membersModel = new MembersModel();
                
                $sc = Yii::app()->params['SitePrefix'] . $terminalName;
                $MID = $memberCardsModel->getMID($membershipcardnumber);

                if (!empty($MID)) {
                    $isCardVip = $memberServicesModel->isVip($MID);
                    if ($isCardVip > 0) {
                        $sc = $sc . 'Vip';
                    } else {
                        $isCardNumberVip = $membersModel->isVip($MID);
                        if ($isCardNumberVip > 0) {
                            $sc = $sc . 'Vip';
                        } else {
                            $sc = $sc;
                        }
                    }
                } else {
                    $transMsg = "Invalid Membership Card Number";
                    $errCode = 24;
                    $this->_sendResponse(200, CommonController::creteEgmSessionResponse(0, '', $transMsg, $errCode));
                    exit;
                }
                $TerminalDetails = $terminalsModel->getTerminalSiteIDSolo($sc);

                //Check if Terminal is an EGM
                if (!empty($TerminalDetails)) {
                    $terminaltype = $terminalsModel->checkTerminalType($TerminalDetails['TerminalID']);
                } else {
                    $terminaltype = 0;
                }
                if ($terminaltype == 1) {
                    //Check if Member Card is Active
                    $status = $memberCardsModel->checkCardStatus($membershipcardnumber);
                    //if $status is Active it will return numeric 1 else it will return the Error Message
                    if (is_numeric($status) && $status == 1) {
                        $MID = $memberCardsModel->getMID($membershipcardnumber);
                        if (!empty($MID)) {
                            //Check Terminal if found by TerminalID which is not empty. If it exists or not empty then,
                            if (!empty($TerminalDetails)) {
                                //Check Terminal Status
                                if ($TerminalDetails['Status'] == 1) {
                                    $cnt_mapped = $terminalServicesModel->checkHasMappedCasino($TerminalDetails['TerminalID'], $casinoID);
                                    if ($cnt_mapped['cnt'] > 0) {
                                        $TerminalID = $TerminalDetails['TerminalID'];
                                        $siteid = $TerminalDetails['SiteID'];

                                        $this->acc_id = $siteaccounts->getVirtualCashier($siteid);

                                        //check if casino id is a number
                                        if (!is_numeric($casinoID)) {
                                            $message = "Invalid Casino ID";
                                            $errCode = 45;
                                            $this->_sendResponse(200, CommonController::creteEgmSessionResponse(0, '', $message, $errCode));

                                            exit;
                                        }

                                        //check if casino id is valid
                                        $ServiceName = $refServices->getServiceNameById($casinoID);
                                        if ($ServiceName == 'false' || $ServiceName == '') {
                                            $message = "Invalid Casino ID";
                                            $errCode = 45;
                                            $this->_sendResponse(200, CommonController::creteEgmSessionResponse(0, '', $message, $errCode));

                                            exit;
                                        }
                                        //check if casino user mode is user-based
                                        $usermode = $refServices->getServiceUserMode($casinoID);
                                        if ($usermode != 1) {
                                            $message = "Casino is not supported.";
                                            $errCode = 62;
                                            $this->_sendResponse(200, CommonController::creteEgmSessionResponse(0, '', $message, $errCode));

                                            exit;
                                        }
                                        //check if casino is mapped on the given terminal
                                        $match = $terminalServicesModel->getMatchedTerminalAndServiceID($TerminalID, $casinoID);
                                        if ($match > 0) {

                                            $isactiveEgmSessionTerminal = $gamingSessionsModel->chkActiveEgmSession($TerminalID);

                                            if (!$isactiveEgmSessionTerminal) {
                                                $isactiveEgmSessionMID = $gamingSessionsModel->chkActiveEgmSessionByMID($MID);

                                                if (!$isactiveEgmSessionMID) {
                                                    $egmsessionid = $gamingSessionsModel->insertEgmSession($MID, $TerminalID, $casinoID, $this->acc_id);

                                                    $egmdetails = $gamingSessionsModel->getlastinsertedegmsession($egmsessionid);

                                                    if (!empty($egmdetails)) {
                                                        $datecreated = $egmdetails['DateCreated'];

                                                        $transMsg = "EGM Session Successfully Created";
                                                        $errCode = 0;
                                                        $this->_sendResponse(200, CommonController::creteEgmSessionResponse(1, $datecreated, $transMsg, $errCode));
                                                    } else {
                                                        $message = 'Failed to start a session.';
                                                        $errCode = 42;
                                                        Utilities::errorLogger($message, $APIName, 
                                                              "TerminalID:".$TerminalDetails['TerminalID']." | ".
                                                              "MID: ".$MID." | ".
                                                              "SiteID: ".$TerminalDetails['SiteID']." | ");
                                                        $this->_sendResponse(200, CommonController::creteEgmSessionResponse(0, '', $message, $errCode));
                                                    }
                                                } else {
                                                    $message = 'Card number is already being used in a current active session.';
                                                    $errCode = 58;
                                                    Utilities::errorLogger($message, $APIName, 
                                                              "TerminalID:".$TerminalDetails['TerminalID']." | ".
                                                              "MID: ".$MID." | ".
                                                              "SiteID: ".$TerminalDetails['SiteID']." | ");
                                                    $this->_sendResponse(200, CommonController::creteEgmSessionResponse(0, '', $message, $errCode));
                                                }
                                            } else {
                                                $message = 'Terminal already has an active session.';
                                                $errCode = 37;
                                                Utilities::errorLogger($message, $APIName, 
                                                              "TerminalID:".$TerminalDetails['TerminalID']." | ".
                                                              "MID: ".$MID." | ".
                                                              "SiteID: ".$TerminalDetails['SiteID']." | ".
                                                              "CasinoID: ".$casinoID);
                                                $this->_sendResponse(200, CommonController::creteEgmSessionResponse(0, '', $message, $errCode));
                                            }
                                        } else {
                                            $message = 'Terminal Name and Casino ID did not match.';
                                            $errCode = 15;
                                            $this->_sendResponse(200, CommonController::creteEgmSessionResponse(0, '', $message, $errCode));
                                        }
                                    } else {
                                        $message = 'The casino is not mapped in this terminal.';
                                        $errCode = 49;
                                        $this->_sendResponse(200, CommonController::creteEgmSessionResponse(0, '', $message, $errCode));
                                    }
                                }//
                                else {
                                    $message = 'Terminal is Inactive.';
                                    $errCode = 48;
                                    $this->_sendResponse(200, CommonController::creteEgmSessionResponse(0, '', $message, $errCode));
                                }
                            } else {
                                $message = "Cannot find Terminal.";
                                $errCode = 7;
                                $this->_sendResponse(200, CommonController::creteEgmSessionResponse(0, '', $message, $errCode));
                            }
                        } else {
                            $message = "Cannot find Card Number.";
                            $errCode = 8;
                            $this->_sendResponse(200, CommonController::creteEgmSessionResponse(0, '', $message, $errCode));
                        }
                    } else {
                        $message = $status;
                        $errCode = 24;
                        $this->_sendResponse(200, CommonController::creteEgmSessionResponse(0, '', $message, $errCode));
                    }
                } else {
                    $message = "Terminal type is not EGM";
                    $errCode = 57;
                    $this->_sendResponse(200, CommonController::creteEgmSessionResponse(0, '', $message, $errCode));
                }
            }
            //If membershipCardNumber is invalid. If invalid then
            else if (!Utilities::validateInput($membershipcardnumber) && Utilities::validateInput($membershipcardnumber)) {
                $message = "Membership Card Number may contain special characters.";
                $errCode = 5;
                $this->_sendResponse(200, CommonController::creteEgmSessionResponse(0, '', $message, $errCode));
            }
            //If Terminal Name is invalid. If invalid then
            else if (!Utilities::validateInput($terminalName) && Utilities::validateInput($casinoID)) {
                $message = "Terminal Name may contain special characters.";
                $errCode = 4;
                $this->_sendResponse(200, CommonController::creteEgmSessionResponse(0, '', $message, $errCode));
            }
            //If Tracking ID is invalid. If invalid then
            else if (!Utilities::validateInput($casinoID)) {
                $message = "Casino ID may contain special characters.";
                $errCode = 16;
                $this->_sendResponse(200, CommonController::creteEgmSessionResponse(0, '', $message, $errCode));
            }
        }
        //If Terminal Name is blank. If blank then
        else if (((!isset($request['MembershipCardNumber']) && $request['MembershipCardNumber']) == '') && (isset($request['TerminalName']) && $request['TerminalName']) != '' && (isset($request['CasinoID']) && $request['CasinoID']) != '') {
            $message = "Membership Card Number is not set or blank.";
            $errCode = 2;
            $this->_sendResponse(200, CommonController::creteEgmSessionResponse(0, '', $message, $errCode));
        }
        //If Terminal Name is blank. If blank then
        else if (((!isset($request['TerminalName']) && $request['TerminalName']) == '') && (isset($request['MembershipCardNumber']) && $request['MembershipCardNumber']) != '' && (isset($request['CasinoID']) && $request['CasinoID']) != '') {
            $message = "Terminal Name is not set or blank.";
            $errCode = 1;
            $this->_sendResponse(200, CommonController::creteEgmSessionResponse(0, '', $message, $errCode));
        }
        //If Tracking ID is blank. If blank then
        else if (((isset($request['MembershipCardNumber']) && $request['MembershipCardNumber']) != '') && ((isset($request['TerminalName']) && $request['TerminalName']) != '') && (!isset($request['CasinoID']) && $request['CasinoID']) == '') {
            $message = "Casino ID is not set or blank.";
            $errCode = 18;
            $this->_sendResponse(200, CommonController::creteEgmSessionResponse(0, '', $message, $errCode));
        } else if (((!isset($request['TerminalName']) && $request['TerminalName']) == '') && ((!isset($request['CasinoID']) && $request['CasinoID']) == '') && (isset($request['MembershipCardNumber']) && $request['MembershipCardNumber']) != '') {
            $message = "Terminal Name and Casino ID is not set or blank.";
            $errCode = 19;
            $this->_sendResponse(200, CommonController::creteEgmSessionResponse(0, '', $message, $errCode));
        } else if (((!isset($request['TerminalName']) && $request['TerminalName']) == '') && ((!isset($request['MembershipCardNumber']) && $request['MembershipCardNumber']) == '') && (isset($request['CasinoID']) && $request['CasinoID']) != '') {
            $message = "Membership Card Number and Terminal Name is not set or blank.";
            $errCode = 54;
            $this->_sendResponse(200, CommonController::creteEgmSessionResponse(0, '', $message, $errCode));
        } else if (((!isset($request['CasinoID']) && $request['CasinoID']) == '') && ((!isset($request['MembershipCardNumber']) && $request['MembershipCardNumber']) == '') && (isset($request['TerminalName']) && $request['TerminalName']) != '') {
            $message = "Membership Card Number and Casino ID is not set or blank.";
            $errCode = 53;
            $this->_sendResponse(200, CommonController::creteEgmSessionResponse(0, '', $message, $errCode));
        } else if (($request['MembershipCardNumber'] == '') && ($request['TerminalName'] == '') && ($request['CasinoID'] == '')) {
            $message = "Membership Card Number, Terminal Name, and Casino ID is not set or blank.";
            $errCode = 52;
            $this->_sendResponse(200, CommonController::creteEgmSessionResponse(0, '', $message, $errCode));
        }
    }

    //End @author

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

    /**
     * Description: Start a Session and Deposit Transaction 
     * @author gvjagolino
     */
    public function actionStartSession() {
        Yii::import('application.components.TerminalBasedTrans');
        Yii::import('application.components.UserBasedTrans');
        Yii::import('application.components.LoyaltyAPIWrapper');
        Yii::import('application.components.AsynchronousRequest');
        Yii::import('application.components.VoucherTicketAPIWrapper');

        $loyalty = new LoyaltyAPIWrapper();
        $voucherticket = new VoucherTicketAPIWrapper();

        $terminals = new TerminalsModel();
        $refServices = new RefServicesModel();
        $sitebalanceModel = new SiteBalanceModel();
        $sitesModel = new SitesModel();
        $loyaltyrequestlogs = new LoyaltyRequestLogsModel();
        $gamingRequestLogsModel = new GamingRequestLogs();

        $terminalBasedTrans = new TerminalBasedTrans();
        $userBasedTrans = new UserBasedTrans();
        $spyderReqLogsModel = new SpyderRequestLogsModel();
        $asynchronousRequest = new AsynchronousRequest();
        $vmsrequestlogs = new VMSRequestLogsModel();
        $siteaccounts = new SiteAccountsModel();
        $membercardsmodel = new MemberCardsModel();
        $stackerSummaryModel = new StackerSummaryModel();
        $stackerdetails = new StackerDetailsModel();
        $sitedenomination = new SiteDenominationModel();
        $terminalServicesModel = new TerminalServicesModel();
        $egmsessions = new EGMSessionModel();
        
        $request = $this->_readJsonRequest();
        $DateTime = '';
        $trackingID = '';
        $message = '';
        $errCode = '';
        $APIName = "Start Session";
        
        $paymentType = 1;
        $isCreditable = 1;
        //Clean inputs. Trim whitespaces
        $casinoID = trim($request['CasinoID']);
        $terminalname = trim($request['TerminalName']);
        $playerMode = trim($request['PlayerMode']);
        $cardNumber = trim($request['MembershipCardNumber']);
        $amount = trim($request['Amount']);
        $trackingID = trim($request['TrackingID']);
        $stackerbatchID = trim($request['StackerBatchID']);
        
        $casino = array();
        //Check if all required fields are set
        if (isset($casinoID) && $casinoID != '' && isset($terminalname) && $terminalname != '' && isset($playerMode) && $playerMode != '' && isset($cardNumber) && $cardNumber != '' && isset($amount) && $amount != '' && isset($trackingID) && $trackingID != '' && isset($stackerbatchID) && $stackerbatchID != '') {

            $casinoID = htmlentities($casinoID);
            $terminalname = htmlentities($terminalname);
            $playerMode = htmlentities($playerMode);
            $cardNumber = htmlentities($cardNumber);
            $amount = htmlentities($amount);
            $trackingID = htmlentities($trackingID);
            $stackerbatchID = htmlentities($stackerbatchID);
            //Validate Inputs
            if ((!Utilities::validateInput($casinoID)) || (!Utilities::validateInput($terminalname)) ||
                    (!Utilities::validateInput($playerMode)) || (!Utilities::validateInput($cardNumber)) ||
                    (!Utilities::validateInput($amount)) || (!Utilities::validateInput($trackingID)) || (!Utilities::validateInput($stackerbatchID))) {
                $message = 'Parameters contains invalid special characters';
                $this->_sendResponse(200, CommonController::startSessionResponse(2, $DateTime, $trackingID, $message, 21));

                exit;
            }
            //Append SitePrefix in entered terminal name
            $terminalcode = Yii::app()->params['SitePrefix'] . $terminalname;
            //Check if Terminal is EGM
            $terminaldetails = $terminals->getTerminalIDByCode($terminalcode); //get terminalID
            //Check if has returned value
            if (!empty($terminaldetails)) {
                $terminalID = $terminaldetails[0]['TerminalID'];
            } else {
                $message = "Invalid Terminal Name";
                $this->_sendResponse(200, CommonController::startSessionResponse(2, $DateTime, $trackingID, $message, 23));

                exit;
            }
            //Check if casino is mapped in the terminal
            $cnt_mapped = $terminalServicesModel->checkHasMappedCasino($terminalID, $casinoID);
            
            if ($cnt_mapped['cnt'] == 0) {
                $message = "The casino is not mapped in this terminal.";
                $this->_sendResponse(200, CommonController::startSessionResponse(2, $DateTime, $trackingID, $message, 63));

                exit;
            }
            //Check if casino ID is exist in egmsession together with terminal name and mid
            $casinoCnt = $egmsessions->checkIfCasinoExist($terminalID, $casinoID);
            if ($casinoCnt == 0) {
                $message = "Terminal name and Casino ID did not match.";
                $this->_sendResponse(200, CommonController::startSessionResponse(2, $DateTime, $trackingID, $message, 20));
            }
            //Check terminal type
            $ttype = $terminals->checkTerminalType($terminalID);
            if ($ttype == 0) {
                $message = "Terminal type is not EGM";
                $this->_sendResponse(200, CommonController::startSessionResponse(2, $DateTime, $trackingID, $message, 57));

                exit;
            }
            //Check Card Status
            $cardstat = $membercardsmodel->checkCardStatus($cardNumber);
            //will return integer 1 if Active, else Error Message
            if ($cardstat != 1) {
                $message = $cardstat;
                $this->_sendResponse(200, CommonController::startSessionResponse(2, $DateTime, $trackingID, $message, 24));

                exit;
            }

            if (!is_numeric($casinoID)) {
                $message = "Invalid Casino ID";
                $this->_sendResponse(200, CommonController::startSessionResponse(2, $DateTime, $trackingID, $message, 45));

                exit;
            }

            if (!is_numeric($stackerbatchID)) {
                $message = "Invalid Stacker Batch ID";
                $this->_sendResponse(200, CommonController::startSessionResponse(2, $DateTime, $trackingID, $message, 51));

                exit;
            }

            $usermode = $refServices->getServiceUserMode($casinoID);
            if ($usermode != 1) {
                $message = "Casino is not supported.";
                $this->_sendResponse(200, CommonController::startSessionResponse(2, $DateTime, $trackingID, $message, 62));

                exit;
            }
            //Get card info
            $cardinfo = $this->getCardInfo($cardNumber);
            if (!$cardinfo) {
                $message = 'Can\'t get card info';
                $MID = $membercardsmodel->getMID($cardNumber);
                Utilities::errorLogger($message, $APIName,
                          "MID: ".$MID." | ");
                return array('TransStatus' => 2, 'TransAmount' => Utilities::toDecimal($amount),
                    'TransDate' => '', 'TrackingID' => $trackingID, 'TransID' => '',
                    'TransMessage' => $message, 'ErrorCode' => 29);

                Yii::app()->end();
            }

            list($is_loyalty, $card_number, $loyalty, $casinos, $mid, $casinoarray_count, $isVIP) = $cardinfo;
            $casinoUsername = '';
            $casinoPassword = '';
            $casinoHashedPassword = '';
            $casinoServiceID = '';
            $casinoStatus = '';
            
            $casino = $this->loopAndFindCasinoService($casinos, "ServiceID", $casinoID);
            //Get Casino Array
            for ($ctr = 0; $ctr < count($casino); $ctr++) {

                $casinoUsername = $casino[$ctr]['ServiceUsername'];
                $casinoPassword = $casino[$ctr]['ServicePassword'];
                $casinoHashedPassword = $casino[$ctr]['HashedServicePassword'];
                $casinoServiceID = $casino[$ctr]['ServiceID'];
                $casinoStatus = $casino[$ctr]['Status'];
                $casinoIsVIP = $casino[$ctr]['isVIP'];
            }

            $terminalName = Yii::app()->params['SitePrefix'] . $terminalname;
            
            if ($isVIP == 1) {
                $terminalName = Yii::app()->params['SitePrefix'] . $terminalname . 'VIP';
            }

            $terminalID = $terminals->getTerminalSiteIDSolo($terminalName);

            if ($terminalID == false) {
                $message = "Invalid Terminal Name";
                $this->_sendResponse(200, CommonController::startSessionResponse(2, $DateTime, $trackingID, $message, 23));

                exit;
            }

            if ($terminalID['Status'] != 1) {
                $message = "Terminal is Inactive";
                $this->_sendResponse(200, CommonController::startSessionResponse(2, $DateTime, $trackingID, $message, 23));

                exit;
            }

            $terminalid = $terminalID['TerminalID'];
            $siteid = $terminalID['SiteID'];

            $this->acc_id = $siteaccounts->getVirtualCashier($siteid);

            $total = $stackerdetails->getStackerTotalAmount($stackerbatchID);

            if ($total['TotalAmount'] != $amount) {

                $message = "Error: Invalid Amount.";
                $this->_sendResponse(200, CommonController::startSessionResponse(2, $DateTime, $trackingID, $message, 60));

                Yii::app()->end();
                ;
            }

            $trans_id = $gamingRequestLogsModel->insertGamingRequestLogs($trackingID, $amount, '0', 'D', $siteid, $terminalid, $casinoID, $card_number, $mid);

            //check if tracking ID is unique
            if ($trans_id != false) {
                //Get Total Amount in Stacker Details

                if (!is_numeric($amount)) {
                    $bool = substr($amount, 0, 1) === 'T';
                    if (!$bool) {
                        $message = "Amount should be numeric or a Ticket";
                        $this->status = 2;
                        $gamingRequestLogsModel->updateGamingLogsStatus($trans_id, $this->status, null, null, null);
                        $this->_sendResponse(200, CommonController::startSessionResponse(2, $DateTime, $trackingID, $message, 22));

                        exit;
                    }

                    $paymentType = 3;
                    $vouchercode = $amount;
                    $source = Yii::app()->params['voucher_source'];
                    $trackingId = '';
                    $verifyVoucherResult = $voucherticket->validateVoucher($terminalname, $vouchercode, $this->acc_id, $source, $cardNumber, $trackingId);

                    //verify if vms API has no error/reachable
                    if (is_string($verifyVoucherResult)) {
                        $this->status = 2;
                        $gamingRequestLogsModel->updateGamingLogsStatus($trans_id, $this->status, null, null, null);
                        $message = $verifyVoucherResult;
                        $this->_sendResponse(200, CommonController::startSessionResponse(2, $DateTime, $trackingID, $message, 46));

                        Yii::app()->end();
                    }

                    //check if voucher is not yet claimed
                    if (isset($verifyVoucherResult['VerifyTicket']['ErrorCode']) && $verifyVoucherResult['VerifyTicket']['ErrorCode'] == 0) {
                        //check if amount is not blank 
                        if (isset($verifyVoucherResult['VerifyTicket']['Amount']) && $verifyVoucherResult['VerifyTicket']['Amount'] != '') {
                            $amount = $verifyVoucherResult['VerifyTicket']['Amount'];

                            $checkminumum = Yii::app()->params['allowminimumamount'];
                            if ($checkminumum == true) {
                                if ($isVIP == 1) {
                                    //Check Site Minimum and Maximum Denomination
                                    $vipdenom = $sitedenomination->getVIPMinMaxInfoWithAlias($siteid);
                                    //Check Minimum Denom
                                    if ($amount < $vipdenom[0]['VIPMin']) {
                                        $this->status = 2;
                                        $gamingRequestLogsModel->updateGamingLogsStatus($trans_id, $this->status, null, null, null);
                                        $message = 'Amount should be equal or greater than ' . number_format($vipdenom[0]['VIPMin'], 2) . '.';
                                        $this->_sendResponse(200, CommonController::startSessionResponse(2, $DateTime, $trackingID, $message, 47));

                                        Yii::app()->end();
                                    }
                                    //Check Maximum Denom
                                    if ($amount > $vipdenom[0]['VIPMax']) {
                                        $this->status = 2;
                                        $gamingRequestLogsModel->updateGamingLogsStatus($trans_id, $this->status, null, null, null);
                                        $message = 'Amount should be equal or less than ' . number_format($vipdenom[0]['VIPMax'], 2) . '.';
                                        $this->_sendResponse(200, CommonController::startSessionResponse(2, $DateTime, $trackingID, $message, 61));

                                        Yii::app()->end();
                                    }
                                } else {
                                    $regdenom = $sitedenomination->getRegMinMaxInfoWithAlias($siteid);
                                    //Check Regular Minimum Denomination
                                    if ($amount < $regdenom[0]['RegMin']) {
                                        $this->status = 2;
                                        $gamingRequestLogsModel->updateGamingLogsStatus($trans_id, $this->status, null, null, null);
                                        $message = 'Amount should be equal or greater than ' . number_format($regdenom[0]['RegMin'], 2) . '';
                                        $this->_sendResponse(200, CommonController::startSessionResponse(2, $DateTime, $trackingID, $message, 47));

                                        Yii::app()->end();
                                    }
                                    //Check Regular Maximum Denomination
                                    if ($amount > $regdenom[0]['RegMax']) {
                                        $this->status = 2;
                                        $gamingRequestLogsModel->updateGamingLogsStatus($trans_id, $this->status, null, null, null);
                                        $message = 'Amount should be equal or less than ' . number_format($regdenom[0]['RegMax'], 2) . '';
                                        $this->_sendResponse(200, CommonController::startSessionResponse(2, $DateTime, $trackingID, $message, 61));

                                        Yii::app()->end();
                                    }
                                }
                            }

                            //checking if casino is terminal based
                            if ($usermode == 0) {
                                $login_acct = $terminalName;
                                $terminal_pwd = $terminals->getTerminalPassword($terminalid, $casinoID);

                                if ($terminal_pwd['ServicePassword'] == '') {
                                    $this->status = 2;
                                    $gamingRequestLogsModel->updateGamingLogsStatus($trans_id, $this->status, null, null, null);
                                    $message = 'There are no mapped casino in this terminal';
                                    $this->_sendResponse(200, CommonController::startSessionResponse(2, $DateTime, $trackingID, $message, 49));

                                    Yii::app()->end();
                                }

                                $login_pwd = $terminal_pwd['ServicePassword'];

                                $sitebalance = $sitebalanceModel->getSiteBalance($siteid);
                                $sitebalance = Utilities::toMoney($sitebalance['Balance']);

                                $result = $terminalBasedTrans->start($terminalid, $siteid, 'D', $casinoID, Utilities::toInt($sitebalance), $amount, $this->acc_id, $card_number, $paymentType, $stackerbatchID, $casinoUsername, $casinoPassword, $casinoHashedPassword, $casinoServiceID, $mid, $usermode);
                            }


                            //checking if casino is user based
                            if ($usermode == 1) {
                                if (empty($casino))
                                {
                                    $this->status = 2;
                                    $gamingRequestLogsModel->updateGamingLogsStatus($trans_id, $this->status, null, null, null);
                                    $message = 'Amount should be equal or greater than ' . number_format($vipdenom[0]['VIPMin'], 2) . '.';
                                    $this->_sendResponse(200, CommonController::startSessionResponse(2, $DateTime, $trackingID, $message, 47));

                                    Yii::app()->end();
                                }
                                $sitebalance = $sitebalanceModel->getSiteBalance($siteid);
                                $sitebalance = Utilities::toMoney($sitebalance['Balance']);

                                $login_acct = $casinoUsername;
                                $login_pwd = $casinoHashedPassword;

                                if ($login_pwd == '') {
                                    $this->status = 2;
                                    $gamingRequestLogsModel->updateGamingLogsStatus($trans_id, $this->status, null, null, null);
                                    $message = 'There are no mapped casino in this terminal';
                                    $this->_sendResponse(200, CommonController::startSessionResponse(2, $DateTime, $trackingID, $message, 49));

                                    Yii::app()->end();
                                }
                                
                                $result = $userBasedTrans->start($terminalid, $siteid, 'D', $casinoID, Utilities::toInt($sitebalance), $amount, $this->acc_id, $card_number, $paymentType, $stackerbatchID, $casinoUsername, $casinoPassword, $casinoHashedPassword, $casinoServiceID, $mid, $usermode);
                            }

                            //Is cashier transaction successful
                            if (isset($result['trans_summary_id'])) {
                                //Insert to vmsrequestlogs
                                $vmsrequestlogsID = $vmsrequestlogs->insert($vouchercode, $this->acc_id, $terminalid, $trackingID);

                                //use voucher and check result
                                $useVoucherResult = $voucherticket->useVoucher($terminalname, $vouchercode, $this->acc_id, $source, $trackingID, $cardNumber);

                                if (isset($useVoucherResult['UseTicket']['ErrorCode']) && $useVoucherResult['UseTicket']['ErrorCode'] != 0) {
                                    $vmsrequestlogs->updateVMSRequestLogs($vmsrequestlogsID, 2);

                                    //verify tracking id, if tracking id is not found and voucher is unclaimed proceed to use voucher
                                    $verifyVoucherResult = $voucherticket->validateVoucher($terminalName, $vouchercode, $this->acc_id, $source, $cardNumber, $trackingId);

                                    //check if tracking result is not found that means transaction was not successful on the first try
                                    if (!isset($verifyVoucherResult['VerifyTicket']['ErrorCode']) && $verifyVoucherResult['VerifyTicket']['ErrorCode'] != 0) {

                                        //Insert to vmsrequestlogs
                                        $vmsrequestlogs->insert($vouchercode, $this->acc_id, $terminalid, $trackingID);

                                        $useVoucherResult = $voucherticket->useVoucher($terminalname, $vouchercode, $this->acc_id, $source, $trackingID, $cardNumber);

                                        if (isset($useVoucherResult['UseTicket']['ErrorCode']) && $useVoucherResult['UseTicket']['ErrorCode'] != 0) {
                                            $vmsrequestlogs->updateVMSRequestLogs($vmsrequestlogsID, 2);
                                            $message = 'VMS: ' . $useVoucherResult['UseTicket']['TransMsg'];
                                            $errorcodes = $useVoucherResult['UseTicket']['ErrorCode'];
                                            $result = array('TransMessage' => $message, 'ErrorCode' => $errorcodes);
                                        } else {
                                            //check if the useVoucher is successful, if success insert to vmsrequestlogs and status = 1 else 2
                                            $vmsrequestlogs->updateVMSRequestLogs($vmsrequestlogsID, 1);
                                        }
                                    }
                                } else {
                                    //check if the useVoucher is successful, if success insert to vmsrequestlogs and status = 1 else 2
                                    $vmsrequestlogs->updateVMSRequestLogs($vmsrequestlogsID, 1);
                                }
                            }
                        } else {
                            $message = 'Amount is not set';
                            $result = array('TransMessage' => $message, 'ErrorCode' => 30);
                        }
                    } else {
                        $message = 'VMS: ' . $verifyVoucherResult['VerifyTicket']['TransactionMessage'];
                        $errorcodes = $verifyVoucherResult['VerifyTicket']['ErrorCode'];
                        $result = array('TransMessage' => $message, 'ErrorCode' => $errorcodes);
                    }
                } else {

                    $paymentType = 1;
                    $isCreditable = 1;

                    $checkminumum = Yii::app()->params['allowminimumamount'];
                    if ($checkminumum == true) {
                        if ($isVIP == 1) {
                            //Check Site Minimum and Maximum Denomination
                            $vipdenom = $sitedenomination->getVIPMinMaxInfoWithAlias($siteid);
                            //Check Minimum Denom
                            if ($amount < $vipdenom[0]['VIPMin']) {
                                $this->status = 2;
                                $gamingRequestLogsModel->updateGamingLogsStatus($trans_id, $this->status, null, null, null);
                                $message = 'Amount should be equal or greater than ' . number_format($vipdenom[0]['VIPMin'], 2) . '.';
                                $this->_sendResponse(200, CommonController::startSessionResponse(2, $DateTime, $trackingID, $message, 47));

                                Yii::app()->end();
                            }
                            //Check Maximum Denom
                            if ($amount > $vipdenom[0]['VIPMax']) {
                                $this->status = 2;
                                $gamingRequestLogsModel->updateGamingLogsStatus($trans_id, $this->status, null, null, null);
                                $message = 'Amount should be equal or less than ' . number_format($vipdenom[0]['VIPMax'], 2) . '.';
                                $this->_sendResponse(200, CommonController::startSessionResponse(2, $DateTime, $trackingID, $message, 61));

                                Yii::app()->end();
                            }
                        } else {
                            $regdenom = $sitedenomination->getRegMinMaxInfoWithAlias($siteid);
                            //Check Regular Minimum Denomination
                            if ($amount < $regdenom[0]['RegMin']) {
                                $this->status = 2;
                                $gamingRequestLogsModel->updateGamingLogsStatus($trans_id, $this->status, null, null, null);
                                $message = 'Amount should be equal or greater than ' . number_format($regdenom[0]['RegMin'], 2) . '';
                                $this->_sendResponse(200, CommonController::startSessionResponse(2, $DateTime, $trackingID, $message, 47));

                                Yii::app()->end();
                            }
                            //Check Regular Maximum Denomination
                            if ($amount > $regdenom[0]['RegMax']) {
                                $this->status = 2;
                                $gamingRequestLogsModel->updateGamingLogsStatus($trans_id, $this->status, null, null, null);
                                $message = 'Amount should be equal or less than ' . number_format($regdenom[0]['RegMax'], 2) . '';
                                $this->_sendResponse(200, CommonController::startSessionResponse(2, $DateTime, $trackingID, $message, 61));

                                Yii::app()->end();
                            }
                        }
                    }

//                        if($amount % 100 != 0)  {
//                                $this->status = 2;
//                                $gamingRequestLogsModel->updateGamingLogsStatus($trans_id, $this->status, null, null);
//                                $message = 'Amount should be divisible by 100';
//                                Utilities::log($message);
//                                $this->_sendResponse(200, CommonController::startSessionResponse(2, 
//                                            $DateTime, $trackingID, $message, 47));
//
//                                Yii::app()->end();
//                        }
                    //checking if casino is terminal based
                    if ($usermode == 0) {
                        $login_acct = $terminalName;
                        $terminal_pwd = $terminals->getTerminalPassword($terminalid, $casinoID);

                        if ($terminal_pwd['ServicePassword'] == '') {
                            $this->status = 2;
                            $gamingRequestLogsModel->updateGamingLogsStatus($trans_id, $this->status, null, null, null);
                            $message = 'There are no mapped casino in this terminal';
                            $this->_sendResponse(200, CommonController::startSessionResponse(2, $DateTime, $trackingID, $message, 49));

                            Yii::app()->end();
                        }

                        $login_pwd = $terminal_pwd['ServicePassword'];

                        $sitebalance = $sitebalanceModel->getSiteBalance($siteid);
                        $sitebalance = Utilities::toMoney($sitebalance['Balance']);

                        if ($total['TotalAmount'] == $amount) {
                            $result = $terminalBasedTrans->start($terminalid, $siteid, 'D', $casinoID, Utilities::toInt($sitebalance), $amount, $this->acc_id, $card_number, $paymentType, $stackerbatchID, $casinoUsername, $casinoPassword, $casinoHashedPassword, $casinoServiceID, $mid, $usermode);
                        } else {
                            $message = "Error: Invalid Amount.";
                            $this->_sendResponse(200, CommonController::startSessionResponse(2, $DateTime, $trackingID, $message, 60));

                            Yii::app()->end();
                            ;
                        }
                    }


                    //checking if casino is user based
                    if ($usermode == 1) {
                        if (empty($casino))
                        {
                            $this->status = 2;
                            $gamingRequestLogsModel->updateGamingLogsStatus($trans_id, $this->status, null, null, null);
                            $message = 'Casino not found.';
                            $this->_sendResponse(200, CommonController::startSessionResponse(2, $DateTime, $trackingID, $message, 49));

                            Yii::app()->end();
                        }
                        $sitebalance = $sitebalanceModel->getSiteBalance($siteid);
                        $sitebalance = Utilities::toMoney($sitebalance['Balance']);

                        $login_acct = $casinoUsername;
                        $login_pwd = $casinoHashedPassword;

                        if ($login_pwd == '') {
                            $this->status = 2;
                            $gamingRequestLogsModel->updateGamingLogsStatus($trans_id, $this->status, null, null, null);
                            $message = 'There are no mapped casino in this terminal';
                            $this->_sendResponse(200, CommonController::startSessionResponse(2, $DateTime, $trackingID, $message, 49));

                            Yii::app()->end();
                        }

                        if ($total['TotalAmount'] == $amount) {
                            $result = $userBasedTrans->start($terminalid, $siteid, 'D', $casinoID, Utilities::toInt($sitebalance), $amount, $this->acc_id, $card_number, $paymentType, $stackerbatchID, $casinoUsername, $casinoPassword, $casinoHashedPassword, $casinoServiceID, $mid, $usermode);
                        } else {
                            $message = "Error: Invalid Amount.";
                            $this->_sendResponse(200, CommonController::startSessionResponse(2, $DateTime, $trackingID, $message, 60));

                            Yii::app()->end();
                            ;
                        }
                    }
                }

                $pos_account_no = $sitesModel->getPosAccountNo($siteid);

                //Is cashier transaction successful
                if (isset($result['trans_summary_id'])) {

                    $this->status = $result['transStatus'];

                    $gamingRequestLogsModel->updateGamingLogsStatus($trans_id, $this->status, $result['udate'], null, null);
                } else {
                    $this->status = 2;

                    $gamingRequestLogsModel->updateGamingLogsStatus($trans_id, $this->status, null, null, null);
                }

                /*                 * ********************** FOR LOYALTY ************************ */
                if (!isset($result["trans_details_id"])) {
                    $trans_details_id = null;
                } else {
                    $trans_details_id = $result["trans_details_id"];
                }

                if (!isset($result['terminal_name'])) {
                    $terminal_name = $terminalname;
                } else {
                    $terminal_name = $result['terminal_name'];
                }

                //Insert to loyaltyrequestlogs
                $loyaltyrequestlogsID = $loyaltyrequestlogs->insert($mid, 'D', $terminalid, $amount, $trans_details_id, 0, $isCreditable);
                $transdate = CasinoApi::udate('Y-m-d H:i:s.u');
                if ($is_loyalty) {
                    $isSuccessful = $loyalty->processPoints($cardNumber, $transdate, 1, 'D', $amount, $siteid, $trans_details_id, $terminal_name, $isCreditable, '', 7, 1);
                }

                //check if the loyaltydeposit is successful, if success insert to loyaltyrequestlogs and status = 1 else 2
                if ($isSuccessful) {
                    $loyaltyrequestlogs->updateLoyaltyRequestLogs($loyaltyrequestlogsID, 1);
                } else {
                    $loyaltyrequestlogs->updateLoyaltyRequestLogs($loyaltyrequestlogsID, 2);
                }
                //SUCCESS
                if (isset($result['transStatus'])) {
                    if ($result['transStatus'] == 1) {
                        //get cashier user
                        $user = $siteaccounts->getAIDByAccountTypeIDAndTerminalID(15, $terminalid);
                        //Update Stacker Summart Status
                        $updatestat = $stackerSummaryModel->updateStackerSummaryStatus($stackerbatchID, StackerSummaryModel::STATUS_DEPOSIT, $user);
                        $message = 'Transaction Successful';
                        $this->_sendResponse(200, CommonController::startSessionResponse(1, $result['TransactionDate'], $result['trans_details_id'], $message, 0));
                    } else {
                        $this->_sendResponse(200, CommonController::startSessionResponse(2, '', '', $result['TransMessage'], $result['ErrorCode']));
                    }
                } else {
                    $this->_sendResponse(200, CommonController::startSessionResponse(2, '', '', $result['TransMessage'], $result['ErrorCode']));
                }


//                    $spyder_enabled = $sitesModel->getSpyderStatus($siteid); 
//
//                    if(isset($spyder_enabled)){
//                        //if spyder call was enabled in cashier config, call SAPI
//                        if($spyder_enabled == 1){
//                            $commandId = 0; //unlock
//                            $spyder_req_id = $spyderReqLogsModel->insert($terminalName, $commandId);
//                            $terminal = substr($terminalName, strlen("ICSA-")); //removes the "icsa-
//                            $computerName = str_replace("VIP", '', $terminal);
//
//                            $params = array('TerminalName'=>$computerName,'CommandID'=>$commandId,
//                                            'UserName'=>$login_acct,'Password'=>$login_pwd,'Type'=> Yii::app()->params['SAPI_Type'],
//                                            'SpyderReqID'=>$spyder_req_id,'CasinoID'=>$casinoID);
//
//                            $asynchronousRequest->curl_request_async(Yii::app()->params['Asynchronous_URI'], $params);
//                        }
//                    }
            } else {
                $message = 'Tracking ID must be unique.';
                $this->_sendResponse(200, CommonController::startSessionResponse(2, $DateTime, $trackingID, $message, 40));
            }
        } else {

            $message = "Parameters are not set";
            $this->_sendResponse(200, CommonController::startSessionResponse(2, $DateTime, $trackingID, $message, 11));
        }
    }

    /**
     * Description: End a Session and Withdraw Transaction 
     * @author gvjagolino
     */
    public function actionRedeemSession() {
        Yii::import('application.components.TerminalBasedTrans');
        Yii::import('application.components.UserBasedTrans');
        Yii::import('application.components.LoyaltyAPIWrapper');
        Yii::import('application.components.AsynchronousRequest');
        Yii::import('application.components.VoucherManagement');
        Yii::import('application.components.VoucherTicketAPIWrapper');

        $voucherticket = new VoucherTicketAPIWrapper();
        $loyalty = new LoyaltyAPIWrapper();

        $terminals = new TerminalsModel();
        $refServices = new RefServicesModel();
        $sitebalanceModel = new SiteBalanceModel();
        $sitesModel = new SitesModel();
        $loyaltyrequestlogs = new LoyaltyRequestLogsModel();
        $terminalsessions = new TerminalSessionsModel();
        $gamingRequestLogsModel = new GamingRequestLogs();

        $terminalBasedTrans = new TerminalBasedTrans();
        $userBasedTrans = new UserBasedTrans();
        $spyderReqLogsModel = new SpyderRequestLogsModel();
        $asynchronousRequest = new AsynchronousRequest();
        $voucherManagement = new VoucherManagement();
        $siteaccounts = new SiteAccountsModel();
        $egmsessions = new GamingSessionsModel();
        $tickets = new TicketsModel();
        
        $request = $this->_readJsonRequest();
        $DateTime = '';
        $trackingID = '';
        $message = '';
        $errCode = '';

        $paymentType = 1;
        $isCreditable = 1;

        $terminalname = trim($request['TerminalName']);
        $trackingID = htmlentities($request['TrackingID']);
        $stackerBatchID = htmlentities($request['StackerBatchID']);
        $loyaltyCardNo = '';
        if (isset($terminalname) && $terminalname != '' && isset($trackingID) && $trackingID != '' && isset($stackerBatchID) && $stackerBatchID != '') {

            $terminalname = htmlentities($terminalname);
            $trackingID = htmlentities($trackingID);

            if ((!Utilities::validateInput($terminalname)) || (!Utilities::validateInput($trackingID))) {

                $message = 'Parameters contains invalid special characters';
                $this->_sendResponse(200, CommonController::redeemSessionResponse(2, '', '', '', '', '', '', '', $message, 0, 21));

                exit;
            }

            $terminalName = Yii::app()->params['SitePrefix'] . $terminalname;
            $terminalID = $terminals->getTerminalSiteID($terminalName);

            if ($terminalID == false) {
                $message = "Invalid Terminal Name";
                $this->_sendResponse(200, CommonController::redeemSessionResponse(2, '', '', '', '', '', '', '', $message, 0, 23));

                exit;
            }

            if (!is_numeric($stackerBatchID)) {
                $message = "Invalid Stacker Batch ID";
                $this->_sendResponse(200, CommonController::redeemSessionResponse(2, '', '', '', '', '', '', '', $message, 0, 51));

                exit;
            }

            $terminalidreg = $terminalID[0]['TerminalID'];
            $terminalidvip = '';
            if (isset($terminalID[1]['TerminalID'])) {
                $terminalidvip = $terminalID[1]['TerminalID'];
            }
            $siteid = $terminalID[0]['SiteID'];

            $this->acc_id = $siteaccounts->getVirtualCashier($siteid);

            $lastsessiondetails = $terminalsessions->getLastSessionDetails($terminalidreg, $terminalidvip);
            
            if (!empty($lastsessiondetails)) {
                $terminalid = $lastsessiondetails['TerminalID'];
                $casinoUsername = $lastsessiondetails['UBServiceLogin'];
                $casinoPassword = $lastsessiondetails['UBServicePassword'];
                $mid = $lastsessiondetails['MID'];
                $loyaltyCardNo = $lastsessiondetails['LoyaltyCardNumber'];
                $casinoUserMode = $lastsessiondetails['UserMode'];
                $casinoServiceID = $lastsessiondetails['ServiceID'];
                $hashedpw = $lastsessiondetails['UBHashedServicePassword'];
                $amount = $lastsessiondetails['LastBalance'];
            } else {
                $message = "Please start a session first";
                $this->_sendResponse(200, CommonController::redeemSessionResponse(2, '', '', '', '', '', '', '', $message, 0, 23));

                exit;
            }
            //Check if StackerBatchID exist
            $isExist = $egmsessions->chkIfStackerBatchIDExist($stackerBatchID);
            if ($isExist['Count'] == 0)
            {
                $message = "StackerBatchID does not have a session.";
                $this->_sendResponse(200, CommonController::redeemSessionResponse(2, '', '', '', '', '', '', '', $message, 0, 23));

                exit;
            }

            $isSiteActive = $sitesModel->checkIfActiveSite($siteid);

            //Check if site is deactivated
            if (!$isSiteActive) {
                $message = "Inactive Site.";
                return array("message" => $message, "ErrorCode" => 56);
            }
            //check if terminal and stacker session are matched
            $countMatchedID = $egmsessions->isTerminalAndBatchIDMatched($terminalid, $stackerBatchID);
            if ($countMatchedID == 0) {
                $message = "Terminal and StackerBatchID does not match in EGM session.";
                $this->_sendResponse(200, CommonController::redeemSessionResponse(2, '', '', '', '', '', '', '', $message, 0, 42));
            
                exit;
            }
            //Generate Ticket
            $voucherTicketBarcode = Helpers::generate_ticket();
            if(!isset($voucherTicketBarcode) || $voucherTicketBarcode == "") { 
                $message = "Failed to generate ticket.";
                $this->_sendResponse(200, CommonController::redeemSessionResponse(2, '', '', '', '', '', '', '', $message, 0, 62));
                
                exit;
            }
            
            $card_number = $loyaltyCardNo;
            //check if tracking ID is unique
            $trans_id = $gamingRequestLogsModel->insertGamingRequestLogs($trackingID, $amount, '0', 'W', $siteid, $terminalid, $casinoServiceID, $card_number, $mid);
            //check if tracking ID is unique in tickets table
            $isTrackIDExist = $tickets->checkIfTrackingIDExist($trackingID);
            
            if ($trans_id != false && $isTrackIDExist == false) {
                //checking if casino is terminal based
                if ($casinoUserMode == 0) {
                    $login_acct = $terminalName;
                    $terminal_pwd = $terminals->getTerminalPassword($terminalid, $casinoServiceID);

                    $login_pwd = $terminal_pwd['ServicePassword'];

                    $sitebalance = $sitebalanceModel->getSiteBalance($siteid);
                    $sitebalance = Utilities::toMoney($sitebalance['Balance']);

                    $result = $terminalBasedTrans->redeem($login_pwd, $terminalid, $login_acct, $stackerBatchID, $siteid, Utilities::toInt($sitebalance), $casinoServiceID, $amount, $paymentType, $this->acc_id, $loyaltyCardNo, $voucherTicketBarcode, $mid, $casinoUserMode);
                }

                //checking if casino is user based
                if ($casinoUserMode == 1) {
                    $login_acct = $casinoUsername;
                    $login_pwd = $hashedpw;

                    $sitebalance = $sitebalanceModel->getSiteBalance($siteid);
                    $sitebalance = Utilities::toMoney($sitebalance['Balance']);

                    $result = $userBasedTrans->redeem($login_pwd, $terminalid, $login_acct, $stackerBatchID, $siteid
                            , Utilities::toInt($sitebalance), $casinoServiceID
                            , $amount, $paymentType, $this->acc_id, $loyaltyCardNo, $mid, $casinoUserMode, $login_acct, $trackingID, $voucherTicketBarcode, $casinoPassword, $casinoServiceID);
                }

                $pos_account_no = $sitesModel->getPosAccountNo($siteid);

                $vcode = $sitesModel->getSiteCode($siteid);
                $sitecode = substr($vcode, strlen(Yii::app()->params['SitePrefix']));

                /** ********************** FOR LOYALTY ************************ */

                if (!isset($result["trans_details_id"])) {
                    $trans_details_id = null;
                } else {
                    $trans_details_id = $result["trans_details_id"];
                }

                if (!isset($result['terminal_name'])) {
                    $terminal_name = $terminalname;
                } else {
                    $terminal_name = $result['terminal_name'];
                }

                //Insert to loyaltyrequestlogs
                $loyaltyrequestlogsID = $loyaltyrequestlogs->insert($mid, 'W', $terminalid, $amount, $trans_details_id, 0, $isCreditable);
                $transdate = CasinoApi::udate('Y-m-d H:i:s.u');

                $isSuccessful = $loyalty->processPoints($loyaltyCardNo, $transdate, 1, 'W', $amount, $siteid, $trans_details_id, $terminal_name, $isCreditable, '', 7, 1);


                //check if the loyaltydeposit is successful, if success insert to loyaltyrequestlogs and status = 1 else 2
                if ($isSuccessful) {
                    $loyaltyrequestlogs->updateLoyaltyRequestLogs($loyaltyrequestlogsID, 1);
                } else {
                    $loyaltyrequestlogs->updateLoyaltyRequestLogs($loyaltyrequestlogsID, 2);
                }
                if (isset($result['transStatus']) && $result['transStatus'] == 1) {
                    //Success Transaction
                    if ($result['amount'] > 0) {
                        
                        $this->status = $result['transStatus'];

                        $gamingRequestLogsModel->updateGamingLogsStatus($trans_id, $this->status, $result['udate'], $result['VoucherTicketBarcode'], $result['ExpirationDate']);
                
                        if (isset($result['trans_summary_id'])) {
                            $this->status = $result['transStatus'];
                            
                            $gamingRequestLogsModel->updateGamingLogsStatus($trans_id, $this->status, $result['udate'], $result['VoucherTicketBarcode'], $result['ExpirationDate']);
                            $this->_sendResponse(200, CommonController::redeemSessionResponse(1, $result['amount'], $result['VoucherTicketBarcode'], $result['TransactionDate'], $result['ExpirationDate'], $sitecode, $loyaltyCardNo, $result['trans_details_id'], $result['TransMessage'], 1, 0));
                        } else {
                            $this->status = 2;
                            $gamingRequestLogsModel->updateGamingLogsStatus($trans_id, $this->status, null, null, null);
                            $this->_sendResponse(200, CommonController::redeemSessionResponse(1, $result['amount'], '', $result['TransactionDate'], '', $sitecode, $loyaltyCardNo, $result['trans_details_id'], $result['TransMessage'], 0, 0));
                        }
                    } else {
                        $this->status = 1;

                        $gamingRequestLogsModel->updateGamingLogsStatus($trans_id, $this->status, $result['udate'], null, null);
                        $this->_sendResponse(200, CommonController::redeemSessionResponse(1, $result['amount'], '', $result['TransactionDate'], '', $sitecode, $loyaltyCardNo, $result['trans_details_id'], $result['TransMessage'], 0, 0));
                    }
                } else {
                    $this->status = 2;
                    //If amount is not set
                    if (isset($result['amount'])){
                        $amount = $result['amount'];
                    }
                    else{ 
                        $amount = "";
                    }
                    $gamingRequestLogsModel->updateGamingLogsStatus($trans_id, $this->status, null, null, null);
                    $this->_sendResponse(200, CommonController::redeemSessionResponse(2, $amount,  '', '', '', $sitecode, '', '', $result['TransMessage'], 0, $result['ErrorCode']));
                }
            } else {
                $this->status = 2;

                $gamingRequestLogsModel->updateGamingLogsStatus($trans_id, $this->status, null, null, null);
                $message = 'Tracking ID must be unique.';
                $this->_sendResponse(200, CommonController::redeemSessionResponse(2, '', '', '', '', '', '', $trackingID, $message, 0, 40));
            }
        } else {
            $message = "Parameters are not set";
            $this->_sendResponse(200, CommonController::redeemSessionResponse(2, '', '', '', '', '', '', $trackingID, $message, 0, 11));
        }
    }

    /**
     * Description: Relaod a Terminal/Account and Deposit Transaction 
     * @author gvjagolino
     */
    public function actionReloadSession() {
        Yii::import('application.components.TerminalBasedTrans');
        Yii::import('application.components.UserBasedTrans');
        Yii::import('application.components.LoyaltyAPIWrapper');
        Yii::import('application.components.AsynchronousRequest');
        Yii::import('application.components.VoucherManagement');
        Yii::import('application.components.VoucherTicketAPIWrapper');

        $loyalty = new LoyaltyAPIWrapper();
        $voucherticket = new VoucherTicketAPIWrapper();

        $terminals = new TerminalsModel();
        $refServices = new RefServicesModel();
        $sitebalanceModel = new SiteBalanceModel();
        $sitesModel = new SitesModel();
        $loyaltyrequestlogs = new LoyaltyRequestLogsModel();
        $terminalsessions = new TerminalSessionsModel();
        $gamingRequestLogsModel = new GamingRequestLogs();

        $terminalBasedTrans = new TerminalBasedTrans();
        $userBasedTrans = new UserBasedTrans();
        $spyderReqLogsModel = new SpyderRequestLogsModel();
        $asynchronousRequest = new AsynchronousRequest();
        $voucherManagement = new VoucherManagement();
        $vmsrequestlogs = new VMSRequestLogsModel();
        $siteaccounts = new SiteAccountsModel();
        $stackerSummaryModel = new StackerSummaryModel();
        $stackerDetailsModel = new StackerDetailsModel();

        $request = $this->_readJsonRequest();
        $DateTime = '';
        $trackingID = '';
        $message = '';
        $errCode = '';
        $vouchercode = '';

        $paymentType = 1;
        $isCreditable = 1;

        $terminalname = trim($request['TerminalName']);
        $amount = trim($request['Amount']);
        $trackingID = trim($request['TrackingID']);
        $stackerbatchID = trim($request['StackerBatchID']);
        $loyaltyCardNo = '';
        if (isset($terminalname) && $terminalname != '' && isset($amount) && $amount != '' && isset($trackingID) && $trackingID != '' && isset($stackerbatchID) && $stackerbatchID != '') {

            $terminalname = htmlentities($request['TerminalName']);
            $amount = htmlentities($request['Amount']);
            $trackingID = htmlentities($request['TrackingID']);
            $stackerbatchID = htmlentities($request['StackerBatchID']);

            if ((!Utilities::validateInput($terminalname)) || (!Utilities::validateInput($amount)) || (!Utilities::validateInput($trackingID)) || (!Utilities::validateInput($stackerbatchID))) {

                $message = 'Parameters contains invalid special characters';
                $this->_sendResponse(200, CommonController::reloadSessionResponse(2, $DateTime, $trackingID, $message, 21));

                exit;
            }


            $terminalName = Yii::app()->params['SitePrefix'] . $terminalname;
            $terminalID = $terminals->getTerminalSiteID($terminalName);

            if ($terminalID == false) {
                $message = "Invalid Terminal Name";
                $this->_sendResponse(200, CommonController::reloadSessionResponse(2, $DateTime, $trackingID, $message, 23));

                exit;
            }


            if (!is_numeric($stackerbatchID)) {
                $message = "Invalid Stacker Batch ID";
                $this->_sendResponse(200, CommonController::reloadSessionResponse(2, $DateTime, $trackingID, $message, 51));

                exit;
            }

            $terminalidreg = $terminalID[0]['TerminalID'];
            $siteid = $terminalID[0]['SiteID'];
            $terminalidvip = '';
            if (isset($terminalID[1]['TerminalID'])) {
                $terminalidvip = $terminalID[1]['TerminalID'];
            }

            $this->acc_id = $siteaccounts->getVirtualCashier($siteid);

            $lastsessiondetails = $terminalsessions->getLastSessionDetails($terminalidreg, $terminalidvip);

            if (!empty($lastsessiondetails)) {
                $terminalid = $lastsessiondetails['TerminalID'];
                $casinoUsername = $lastsessiondetails['UBServiceLogin'];
                $casinoPassword = $lastsessiondetails['UBServicePassword'];
                $mid = $lastsessiondetails['MID'];
                $loyaltyCardNo = $lastsessiondetails['LoyaltyCardNumber'];
                $casinoUserMode = $lastsessiondetails['UserMode'];
                $casinoServiceID = $lastsessiondetails['ServiceID'];
                $hashedpw = $lastsessiondetails['UBHashedServicePassword'];
            } else {
                $message = "Please start a session first";
                $this->_sendResponse(200, CommonController::reloadSessionResponse(2, $DateTime, $trackingID, $message, 23));

                exit;
            }

            //Check Last reload transaction
            $reloadAmount = $stackerDetailsModel->getLastReloadTrans($stackerbatchID);

            //Check if entered amount is equal to amount in stacker details
            if ($amount != $reloadAmount['Amount']) {
                $message = 'Error: Invalid Amount.';
                $this->_sendResponse(200, CommonController::reloadSessionResponse(2, $DateTime, $trackingID, $message, 23));

                exit;
            }


            $isSiteActive = $sitesModel->checkIfActiveSite($siteid);

            //Check if site is deactivated
            if (!$isSiteActive) {
                $message = "Inactive Site.";
                return array("message" => $message, "ErrorCode" => 56);
            }
            $card_number = $loyaltyCardNo;
            $trans_id = $gamingRequestLogsModel->insertGamingRequestLogs($trackingID, $amount, '0', 'R', $siteid, $terminalid, $casinoServiceID, $card_number, $mid);
            //check if tracking ID is unique
            if ($trans_id != false) {
                //GET last reload
                $total = $stackerDetailsModel->getLastReloadTrans($stackerbatchID);
                if (!is_numeric($amount)) {

                    $bool = substr($amount, 0, 1) === 'T';
                    if (!$bool) {
                        $message = "Amount should be numeric or a Ticket";
                        $this->_sendResponse(200, CommonController::reloadSessionResponse(2, $DateTime, $trackingID, $message, 22));

                        exit;
                    }

                    $paymentType = 3;
                    $vouchercode = $amount;
                    $source = Yii::app()->params['voucher_source'];
                    $trackingId = '';
                    $verifyVoucherResult = $voucherticket->validateVoucher($terminalname, $vouchercode, $this->acc_id, $source, $loyaltyCardNo, $trackingId);

                    //verify if vms API has no error/reachable
                    if (is_string($verifyVoucherResult)) {
                        $this->status = 2;
                        $message = $verifyVoucherResult;
                        return array('TransStatus' => 2, 'TransAmount' => '',
                            'TransDate' => '', 'TrackingID' => $trackingID, 'TransID' => '',
                            'TransMessage' => $message, 'ErrorCode' => 46);
                        Yii::app()->end();
                    }

                    //check if voucher is not yet claimed
                    if (isset($verifyVoucherResult['VerifyTicket']['ErrorCode']) && $verifyVoucherResult['VerifyTicket']['ErrorCode'] == 0) {
                        //check if amount is not blank 
                        if (isset($verifyVoucherResult['VerifyTicket']['Amount']) && $verifyVoucherResult['VerifyTicket']['Amount'] != '') {
                            $amount = $verifyVoucherResult['VerifyTicket']['Amount'];

                            //checking if casino is terminal based
                            if ($casinoUserMode == 0) {
                                $login_acct = $terminalName;
                                $terminal_pwd = $terminals->getTerminalPassword($terminalid, $casinoServiceID);

                                if ($terminal_pwd['ServicePassword'] == '') {
                                    $this->status = 2;
                                    $gamingRequestLogsModel->updateGamingLogsStatus($trans_id, $this->status, null, null, null);
                                    $message = 'There are no mapped casino in this terminal';
                                    $this->_sendResponse(200, CommonController::startSessionResponse(2, $DateTime, $trackingID, $message, 49));

                                    Yii::app()->end();
                                }

                                $login_pwd = $terminal_pwd['ServicePassword'];

                                $sitebalance = $sitebalanceModel->getSiteBalance($siteid);
                                $sitebalance = Utilities::toMoney($sitebalance['Balance']);
                                if ($total['Amount'] == $amount) {
                                    $result = $terminalBasedTrans->reload(Utilities::toInt($sitebalance), $amount, $paymentType, $stackerbatchID, $terminalid, $siteid, $casinoServiceID, $this->acc_id, $loyaltyCardNo, $vouchercode, $trackingID, $mid, $casinoUserMode, $reloadAmount['StackerDetailID']);
                                } else {
                                    $message = "Error: Invalid Amount.";
                                    $this->_sendResponse(200, CommonController::startSessionResponse(2, $DateTime, $trackingID, $message, 60));

                                    Yii::app()->end();
                                }
                            }

                            //checking if casino is user based
                            if ($casinoUserMode == 1) {
                                $login_acct = $casinoUsername;

                                $login_pwd = $hashedpw;

                                if ($login_pwd == '') {
                                    $this->status = 2;
                                    $gamingRequestLogsModel->updateGamingLogsStatus($trans_id, $this->status, null, null, null);
                                    $message = 'There are no mapped casino in this terminal';
                                    $this->_sendResponse(200, CommonController::startSessionResponse(2, $DateTime, $trackingID, $message, 49));

                                    Yii::app()->end();
                                }

                                $sitebalance = $sitebalanceModel->getSiteBalance($siteid);
                                $sitebalance = Utilities::toMoney($sitebalance['Balance']);
                                if ($total['Amount'] == $amount) {
                                    $result = $userBasedTrans->reload(Utilities::toInt($sitebalance), $amount, $paymentType, $stackerbatchID, $terminalid, $siteid, $casinoServiceID, $this->acc_id, $loyaltyCardNo, $vouchercode, $trackingID, $mid, $casinoUserMode, $casinoUsername, $casinoPassword, $casinoServiceID, $reloadAmount['StackerDetailID']);
                                } else {
                                    $message = "Error: Invalid Amount.";
                                    $this->_sendResponse(200, CommonController::startSessionResponse(2, $DateTime, $trackingID, $message, 60));

                                    Yii::app()->end();
                                }
                            }

                            //Is cashier transaction successful
                            if (isset($result['trans_summary_id'])) {
                                //Insert to vmsrequestlogs
                                $vmsrequestlogsID = $vmsrequestlogs->insert($vouchercode, $this->acc_id, $terminalid, $trackingID);

                                //use voucher and check result
                                $useVoucherResult = $voucherticket->useVoucher($terminalname, $vouchercode, $this->acc_id, $source, $trackingID, $loyaltyCardNo);

                                if (isset($useVoucherResult['UseTicket']['ErrorCode']) && $useVoucherResult['UseTicket']['ErrorCode'] != 0) {
                                    $vmsrequestlogs->updateVMSRequestLogs($vmsrequestlogsID, 2);

                                    //verify tracking id, if tracking id is not found and voucher is unclaimed proceed to use voucher
                                    $verifyVoucherResult = $voucherticket->validateVoucher($terminalname, $vouchercode, $this->acc_id, $source, $loyaltyCardNo, $trackingId);

                                    //check if tracking result is not found that means transaction was not successful on the first try
                                    if (!isset($verifyVoucherResult['VerifyTicket']['ErrorCode']) && $verifyVoucherResult['VerifyTicket']['ErrorCode'] != 0) {

                                        //Insert to vmsrequestlogs
                                        $vmsrequestlogs->insert($vouchercode, $this->acc_id, $terminalid, $trackingID);

                                        $useVoucherResult = $voucherticket->useVoucher($terminalname, $vouchercode, $this->acc_id, $source, $trackingID, $loyaltyCardNo);

                                        if (isset($useVoucherResult['UseTicket']['ErrorCode']) && $useVoucherResult['UseTicket']['ErrorCode'] != 0) {
                                            $vmsrequestlogs->updateVMSRequestLogs($vmsrequestlogsID, 2);
                                            $message = 'VMS: ' . $useVoucherResult['UseTicket']['TransMsg'];
                                            $errorcodes = $useVoucherResult['UseTicket']['ErrorCode'];
                                            $result = array('TransMessage' => $message, 'ErrorCode' => $errorcodes);
                                        } else {
                                            //check if the useVoucher is successful, if success insert to vmsrequestlogs and status = 1 else 2
                                            $vmsrequestlogs->updateVMSRequestLogs($vmsrequestlogsID, 1);
                                        }
                                    }
                                } else {
                                    //check if the useVoucher is successful, if success insert to vmsrequestlogs and status = 1 else 2
                                    $vmsrequestlogs->updateVMSRequestLogs($vmsrequestlogsID, 1);
                                }
                            }
                        } else {
                            $message = 'Amount is not set';
                            $result = array('TransMessage' => $message, 'ErrorCode' => 30);
                        }
                    } else {
                        $message = 'VMS: ' . $verifyVoucherResult['VerifyTicket']['TransactionMessage'];
                        $errorcodes = $verifyVoucherResult['VerifyTicket']['ErrorCode'];
                        $result = array('TransMessage' => $message, 'ErrorCode' => $errorcodes);
                    }
                } else {

                    $paymentType = 1;
                    $isCreditable = 1;

                    //checking if casino is terminal based
                    if ($casinoUserMode == 0) {
                        $login_acct = $terminalName;
                        $terminal_pwd = $terminals->getTerminalPassword($terminalid, $casinoServiceID);

                        if ($terminal_pwd['ServicePassword'] == '') {
                            $this->status = 2;
                            $gamingRequestLogsModel->updateGamingLogsStatus($trans_id, $this->status, null, null, null);
                            $message = 'There are no mapped casino in this terminal';
                            $this->_sendResponse(200, CommonController::startSessionResponse(2, $DateTime, $trackingID, $message, 47));

                            Yii::app()->end();
                        }

                        $login_pwd = $terminal_pwd['ServicePassword'];

                        $sitebalance = $sitebalanceModel->getSiteBalance($siteid);
                        $sitebalance = Utilities::toMoney($sitebalance['Balance']);
                        //Check if Total Amount of stacker is equal to entered amount
                        if ($total['Amount'] == $amount) {
                            $result = $terminalBasedTrans->reload(Utilities::toInt($sitebalance), $amount, $paymentType, $stackerbatchID, $terminalid, $siteid, $casinoServiceID, $this->acc_id, $loyaltyCardNo, $vouchercode, $trackingID, $mid, $casinoUserMode, $reloadAmount['StackerDetailID']);
                        } else {
                            $message = "Error: Invalid Amount.";
                            $this->_sendResponse(200, CommonController::startSessionResponse(2, $DateTime, $trackingID, $message, 60));

                            Yii::app()->end();
                        }
                    }

                    //checking if casino is user based
                    if ($casinoUserMode == 1) {
                        $login_acct = $casinoUsername;

                        $login_pwd = $hashedpw;

                        if ($login_pwd == '') {
                            $this->status = 2;
                            $gamingRequestLogsModel->updateGamingLogsStatus($trans_id, $this->status, null, null, null);
                            $message = 'There are no mapped casino in this terminal';
                            $this->_sendResponse(200, CommonController::startSessionResponse(2, $DateTime, $trackingID, $message, 47));

                            Yii::app()->end();
                        }

                        $sitebalance = $sitebalanceModel->getSiteBalance($siteid);
                        $sitebalance = Utilities::toMoney($sitebalance['Balance']);
                        //Check total reload is equal to entered amount
                        if ($total['Amount'] == $amount) {
                            $result = $userBasedTrans->reload(Utilities::toInt($sitebalance), $amount, $paymentType, $stackerbatchID, $terminalid, $siteid, $casinoServiceID, $this->acc_id, $loyaltyCardNo, $vouchercode, $trackingID, $mid, $casinoUserMode, $casinoUsername, $casinoPassword, $casinoServiceID, $reloadAmount['StackerDetailID']);
                        } else {
                            $message = "Error: Invalid Amount.";
                            $this->_sendResponse(200, CommonController::startSessionResponse(2, $DateTime, $trackingID, $message, 60));

                            Yii::app()->end();
                        }
                    }
                }

                $pos_account_no = $sitesModel->getPosAccountNo($siteid);

                if (isset($result['trans_summary_id'])) {
                    $this->status = $result['transStatus'];
                    //get cashier user
                    $user = $siteaccounts->getAIDByAccountTypeIDAndTerminalID(15, $terminalid);
                    //Update Stacker Summary Status
                    $updatestat = $stackerSummaryModel->updateStackerSummaryStatus($stackerbatchID, StackerSummaryModel::STATUS_RELOAD, $user);

                    $gamingRequestLogsModel->updateGamingLogsStatus($trans_id, $this->status, $result['udate'], null, null);


                    $this->_sendResponse(200, CommonController::reloadSessionResponse(1, $result['TransactionDate'], $result['trans_details_id'], $result['TransMessage'], 0));
                } else {
                    $this->status = 2;

                    $gamingRequestLogsModel->updateGamingLogsStatus($trans_id, $this->status, null, null, null);

                    $this->_sendResponse(200, CommonController::reloadSessionResponse(2, '', '', $result['TransMessage'], $result['ErrorCode']));
                }

                /*                 * ********************** FOR LOYALTY ************************ */

                if (!isset($result["trans_details_id"])) {
                    $trans_details_id = null;
                } else {
                    $trans_details_id = $result["trans_details_id"];
                }

                if (!isset($result['terminal_name'])) {
                    $terminal_name = $terminalname;
                } else {
                    $terminal_name = $result['terminal_name'];
                }

                //Insert to loyaltyrequestlogs
                $loyaltyrequestlogsID = $loyaltyrequestlogs->insert($mid, 'R', $terminalid, $amount, $trans_details_id, 0, $isCreditable);
                $transdate = CasinoApi::udate('Y-m-d H:i:s.u');

                $isSuccessful = $loyalty->processPoints($loyaltyCardNo, $transdate, 1, 'R', $amount, $siteid, $trans_details_id, $terminal_name, $isCreditable, '', 7, 1);

                //check if the loyaltydeposit is successful, if success insert to loyaltyrequestlogs and status = 1 else 2
                if ($isSuccessful) {
                    $loyaltyrequestlogs->updateLoyaltyRequestLogs($loyaltyrequestlogsID, 1);
                } else {
                    $loyaltyrequestlogs->updateLoyaltyRequestLogs($loyaltyrequestlogsID, 2);
                }
            } else {
                $message = 'Tracking ID must be unique.';
                $this->_sendResponse(200, CommonController::reloadSessionResponse(2, $DateTime, $trackingID, $message, 40));
            }
        } else {

            $message = "Parameters are not set";
            $this->_sendResponse(200, CommonController::reloadSessionResponse(2, $DateTime, $trackingID, $message, 11));
        }
    }

    protected function getCardInfo($barCode) {
        Yii::import('application.components.LoyaltyAPIWrapper');

        $is_loyalty = false;
        $loyalty = new LoyaltyAPIWrapper();
        $card_number = '';


        if ($barCode != '') {

            $result = $loyalty->getCardInfo($barCode, 1);
            $obj_result = json_decode($result);

            if ($obj_result->CardInfo->CardNumber == null) {
                $message = "Invalid Membership Card Number";
                $this->_sendResponse(200, CommonController::startSessionResponse(2, '', '', $message, 24));

                return false;
            } else {
                $is_loyalty = true;
                $card_number = $obj_result->CardInfo->CardNumber;
            }

            if ($obj_result->CardInfo->StatusCode == 1) {

                $casinoarray_count = count($obj_result->CardInfo->CasinoArray);
                $casinos = array();
                if ($casinoarray_count != 0)
                    for ($ctr = 0; $ctr < $casinoarray_count; $ctr++) {
                        $casinos[$ctr] = array('ServiceUsername' => $obj_result->CardInfo->CasinoArray[$ctr]->ServiceUsername,
                            'ServicePassword' => $obj_result->CardInfo->CasinoArray[$ctr]->ServicePassword,
                            'HashedServicePassword' => $obj_result->CardInfo->CasinoArray[$ctr]->HashedServicePassword,
                            'ServiceID' => $obj_result->CardInfo->CasinoArray[$ctr]->ServiceID,
                            'UserMode' => $obj_result->CardInfo->CasinoArray[$ctr]->UserMode,
                            'isVIP' => $obj_result->CardInfo->CasinoArray[$ctr]->isVIP,
                            'Status' => $obj_result->CardInfo->CasinoArray[$ctr]->Status);
                    }

                $mid = $obj_result->CardInfo->MID;
                $isVIP = $obj_result->CardInfo->MemberClassification;

                return array($is_loyalty, $card_number, $loyalty, $casinos, $mid, $casinoarray_count, $isVIP);
            } else {

                $message = "Invalid Membership Card Number";
                $this->_sendResponse(200, CommonController::startSessionResponse(2, '', '', $message, 24));

                return false;
            }
        } else {
            $message = "Invalid Membership Card Number";
            $this->_sendResponse(200, CommonController::startSessionResponse(2, '', '', $message, 24));

            return false;
        }
    }
    /**
     * @author Gerardo V. Jagolino Jr.
     * @param $array, $index, $search
     * @return array 
     * Description: get Find a certain service in Casino Array
     */
      function loopAndFindCasinoService($array, $index, $search){
        $returnArray = array();
            foreach($array as $k=>$v){
                  if($v[$index] == $search){   
                       $returnArray[] = $v;
                  }
            }
      return $returnArray;
      }
    /**
     * Remove EGM Session API Controller
     * @author Mark Kenneth Esguerra
     * @date June 5, 2014
     */  
    public function actionRemoveegmsession()
    {
        $request = $this->_readJsonRequest();
        $message = "";
        $errCode = "";
        $APIName = "Remove EGM Session";
        
        $membershipcardnumber   = trim($request['MembershipCardNumber']);
        $terminalName           = trim($request['TerminalName']);
        $casinoID               = trim($request['CasinoID']);
        
        if (((isset($membershipcardnumber) && $membershipcardnumber) != '') 
              && ((isset($terminalName) && $terminalName) != '')  
              && ((isset($casinoID) && $casinoID) != '')) 
        {
            if (Utilities::validateInput($membershipcardnumber) && Utilities::validateInput($terminalName) 
                                                                && Utilities::validateInput($casinoID)) 
            {
                //Start of declaration of models to be used.
                $terminalsModel = new TerminalsModel();
                $gamingSessionsModel = new GamingSessionsModel();
                $terminalServicesModel = new TerminalServicesModel();
                $memberCardsModel = new MemberCardsModel();
                $memberServicesModel = new MemberServicesModel();
                $refServices = new RefServicesModel();
                $siteaccounts = new SiteAccountsModel();
                $membersModel = new MembersModel();
                $audittrail = new AuditTrailModel();
                $terminalSessions = new TerminalSessionsModel();
                
                //Check membership card
                $sc = Yii::app()->params['SitePrefix'] . $terminalName;
                $MID = $memberCardsModel->getMID($membershipcardnumber);
                //check if vip
                if (!empty($MID)) {
                    $isCardVip = $memberServicesModel->isVip($MID);
                    if ($isCardVip > 0) {
                        $sc = $sc . 'Vip';
                    } else {
                        $isCardNumberVip = $membersModel->isVip($MID);
                        if ($isCardNumberVip > 0) {
                            $sc = $sc . 'Vip';
                        } else {
                            $sc = $sc;
                        }
                    }
                } 
                else 
                {
                    $transMsg = "Invalid Membership Card Number";
                    $errCode = 24;
                    $this->_sendResponse(200, CommonController::creteEgmSessionResponse(0, '', $transMsg, $errCode));
                    exit;
                }
                $TerminalDetails = $terminalsModel->getTerminalSiteIDSolo($sc);
                //Check if Terminal is an EGM
                if (!empty($TerminalDetails)) {
                    $terminaltype = $terminalsModel->checkTerminalType($TerminalDetails['TerminalID']);
                } else {
                    $terminaltype = 0;
                }
                if ($terminaltype == 1) 
                {
                    //Check if Member Card is Active
                    $status = $memberCardsModel->checkCardStatus($membershipcardnumber);
                    //if $status is Active it will return numeric 1 else it will return the Error Message
                    if (is_numeric($status) && $status == 1) 
                    {
                        $MID = $memberCardsModel->getMID($membershipcardnumber);
                        if (!empty($MID)) 
                        {
                            //Check Terminal if found by TerminalID which is not empty. If it exists or not empty then,
                            if (!empty($TerminalDetails)) 
                            {
                                //check if has active terminal sessions
                                $countExist = $terminalSessions->isSessionActive($TerminalDetails['TerminalID']);
                                if ($countExist == 0 || $countExist == false)
                                {
                                    //Check Terminal Status
                                    if ($TerminalDetails['Status'] == 1) 
                                    {
                                        $cnt_mapped = $terminalServicesModel->checkHasMappedCasino($TerminalDetails['TerminalID'], $casinoID);
                                        if ($cnt_mapped['cnt'] > 0) 
                                        {
                                            $TerminalID = $TerminalDetails['TerminalID'];
                                            $siteid = $TerminalDetails['SiteID'];
                                            //get virtual cashier of the site
                                            $this->acc_id = $siteaccounts->getVirtualCashier($siteid);

                                            //check if casino id is a number
                                            if (!is_numeric($casinoID)) 
                                            {
                                                $message = "Invalid Casino ID";
                                                $errCode = 45;
                                                $this->_sendResponse(200, CommonController::creteEgmSessionResponse(0, '', $message, $errCode));

                                                exit;
                                            }

                                            //check if casino id is valid
                                            $ServiceName = $refServices->getServiceNameById($casinoID);
                                            if ($ServiceName == 'false' || $ServiceName == '') 
                                           {
                                                $message = "Invalid Casino ID";
                                                $errCode = 45;
                                                $this->_sendResponse(200, CommonController::creteEgmSessionResponse(0, '', $message, $errCode));

                                                exit;
                                            }
                                            //check if casino user mode is user-based
                                            $usermode = $refServices->getServiceUserMode($casinoID);
                                            if ($usermode != 1) 
                                            {
                                                $message = "Casino is not supported.";
                                                $errCode = 62;
                                                $this->_sendResponse(200, CommonController::creteEgmSessionResponse(0, '', $message, $errCode));

                                                exit;
                                            }
                                            //check if casino is mapped on the given terminal
                                            $match = $terminalServicesModel->getMatchedTerminalAndServiceID($TerminalID, $casinoID);
                                            if ($match > 0) //Start of removing of egm session
                                            {
                                               //check if there is an active egm session
                                               $hasActive = $gamingSessionsModel->checkEgmSessionBoth($TerminalID, $MID);
                                               if ($hasActive['Count'] > 0)
                                               {
                                                   //get stacker batch id
                                                   $egmsessionID = $hasActive['EGMSessionID'];
                                                   $stackerBatchID = $gamingSessionsModel->getStackerBatchID($egmsessionID);
                                                   //delete egm
                                                   $deleteegm = $gamingSessionsModel->deleteGamingSessions($TerminalID, $stackerBatchID);
                                                   if ($deleteegm)
                                                   {
                                                       //log to audit trail
                                                       $transdetails = "Manual Remove of EGM Session (KAPI) | MID: ".$MID." | TerminalID: ".$TerminalID;
                                                       $audittrail->logToAuditTrail($this->acc_id, $transdetails);

                                                       $message = 'EGM Session Successfully Removed.';
                                                       $errCode = 0;
                                                       $this->_sendResponse(200, CommonController::removeEgmSessionResponse($message, $errCode));
                                                   }
                                                   else
                                                   {
                                                       $message = "Failed to Remove EGM Session";
                                                       $errCode = 64;
                                                       $this->_sendResponse(200, CommonController::removeEgmSessionResponse($message, $errCode));
                                                   }
                                               }
                                               else
                                               {
                                                   $message = 'Terminal has no active EGM session.';
                                                   $errCode = 55;
                                                   $this->_sendResponse(200, CommonController::removeEgmSessionResponse($message, $errCode));
                                               }
                                            } 
                                            else 
                                            {
                                                $message = 'Terminal Name and Casino ID did not match.';
                                                $errCode = 15;
                                                $this->_sendResponse(200, CommonController::removeEgmSessionResponse($message, $errCode));
                                            }
                                        } 
                                        else 
                                        {
                                            $message = 'The casino is not mapped in this terminal.';
                                            $errCode = 49;
                                            $this->_sendResponse(200, CommonController::removeEgmSessionResponse($message, $errCode));
                                        }
                                    }//
                                    else 
                                    {
                                        $message = 'Terminal is Inactive.';
                                        $errCode = 48;
                                        $this->_sendResponse(200, CommonController::removeEgmSessionResponse($message, $errCode));
                                    }
                                }
                                else
                                {
                                    $message = "Failed to Remove EGM Session. There is an existing terminal session for this terminal.";
                                    $errCode = 65;
                                    $this->_sendResponse(200, CommonController::removeEgmSessionResponse($message, $errCode));
                                }
                            } 
                            else 
                            {
                                $message = "Cannot find Terminal.";
                                $errCode = 7;
                                $this->_sendResponse(200, CommonController::removeEgmSessionResponse($message, $errCode));
                            }
                        } 
                        else 
                        {
                            $message = "Cannot find Card Number.";
                            $errCode = 8;
                            $this->_sendResponse(200, CommonController::removeEgmSessionResponse($message, $errCode));
                        }
                    } 
                    else 
                    {
                        $message = $status;
                        $errCode = 24;
                        $this->_sendResponse(200, CommonController::removeEgmSessionResponse($message, $errCode));
                    }
                } 
                else 
                {
                    $message = "Terminal type is not EGM";
                    $errCode = 57;
                    $this->_sendResponse(200, CommonController::removeEgmSessionResponse($message, $errCode));
                }
            }
            //If membershipCardNumber is invalid. If invalid then
            else if (!Utilities::validateInput($membershipcardnumber) && Utilities::validateInput($membershipcardnumber)) {
                $message = "Membership Card Number may contain special characters.";
                $errCode = 5;
                $this->_sendResponse(200, CommonController::removeEgmSessionResponse($message, $errCode));
            }
            //If Terminal Name is invalid. If invalid then
            else if (!Utilities::validateInput($terminalName) && Utilities::validateInput($casinoID)) {
                $message = "Terminal Name may contain special characters.";
                $errCode = 4;
                $this->_sendResponse(200, CommonController::removeEgmSessionResponse($message, $errCode));
            }
            //If Tracking ID is invalid. If invalid then
            else if (!Utilities::validateInput($casinoID)) {
                $message = "Casino ID may contain special characters.";
                $errCode = 16;
                $this->_sendResponse(200, CommonController::removeEgmSessionResponse($message, $errCode));
            }
        }
        //If Terminal Name is blank. If blank then
        else if (((!isset($request['MembershipCardNumber']) && $request['MembershipCardNumber']) == '') && (isset($request['TerminalName']) && $request['TerminalName']) != '' && (isset($request['CasinoID']) && $request['CasinoID']) != '') {
            $message = "Membership Card Number is not set or blank.";
            $errCode = 2;
            $this->_sendResponse(200, CommonController::removeEgmSessionResponse($message, $errCode));
        }
        //If Terminal Name is blank. If blank then
        else if (((!isset($request['TerminalName']) && $request['TerminalName']) == '') && (isset($request['MembershipCardNumber']) && $request['MembershipCardNumber']) != '' && (isset($request['CasinoID']) && $request['CasinoID']) != '') {
            $message = "Terminal Name is not set or blank.";
            $errCode = 1;
            $this->_sendResponse(200, CommonController::removeEgmSessionResponse($message, $errCode));
        }
        //If Tracking ID is blank. If blank then
        else if (((isset($request['MembershipCardNumber']) && $request['MembershipCardNumber']) != '') && ((isset($request['TerminalName']) && $request['TerminalName']) != '') && (!isset($request['CasinoID']) && $request['CasinoID']) == '') {
            $message = "Casino ID is not set or blank.";
            $errCode = 18;
            $this->_sendResponse(200, CommonController::removeEgmSessionResponse($message, $errCode));
        } else if (((!isset($request['TerminalName']) && $request['TerminalName']) == '') && ((!isset($request['CasinoID']) && $request['CasinoID']) == '') && (isset($request['MembershipCardNumber']) && $request['MembershipCardNumber']) != '') {
            $message = "Terminal Name and Casino ID is not set or blank.";
            $errCode = 19;
            $this->_sendResponse(200, CommonController::removeEgmSessionResponse($message, $errCode));
        } else if (((!isset($request['TerminalName']) && $request['TerminalName']) == '') && ((!isset($request['MembershipCardNumber']) && $request['MembershipCardNumber']) == '') && (isset($request['CasinoID']) && $request['CasinoID']) != '') {
            $message = "Membership Card Number and Terminal Name is not set or blank.";
            $errCode = 54;
            $this->_sendResponse(200, CommonController::removeEgmSessionResponse($message, $errCode));
        } else if (((!isset($request['CasinoID']) && $request['CasinoID']) == '') && ((!isset($request['MembershipCardNumber']) && $request['MembershipCardNumber']) == '') && (isset($request['TerminalName']) && $request['TerminalName']) != '') {
            $message = "Membership Card Number and Casino ID is not set or blank.";
            $errCode = 53;
            $this->_sendResponse(200, CommonController::removeEgmSessionResponse($message, $errCode));
        } else if (($request['MembershipCardNumber'] == '') && ($request['TerminalName'] == '') && ($request['CasinoID'] == '')) {
            $message = "Membership Card Number, Terminal Name, and Casino ID is not set or blank.";
            $errCode = 52;
            $this->_sendResponse(200, CommonController::removeEgmSessionResponse($message, $errCode));
        }
    }
}

?>
