<?php

/*
 * Created By : John Aaron Vida
 * javida@philweb.com.ph
 * Mini-Zeus API Controller
 */

class MzapiController extends Controller {

    /**
     * Set default action
     * @var string
     */
    public $defaultAction = 'overview';

    /**
     * Set default layout
     * @var string
     */
    public $layout = 'main';
    public $pageTitle;

    /**
     * Set caching of connection
     * @var boolean
     */
    private $_caching = FALSE;

    /**
     * User agent
     * @var string
     */
    private $_userAgent = 'PEGS Station Manager';

    /**
     * Maximum number of seconds to wait while trying to connect
     * @var integer
     */
    private $_connectionTimeout = 10;

    /**
     * Maximum number of seconds before a call timeouts
     * @var integer
     */
    private $_timeout = 500;

    //@purpose AuthenticateSession
    public function actionIndex() {
        //echo "This is index page!";
    }

    public function actionTransferWallet() {

        Yii::import('application.components.CasinoController');

        $this->pageTitle = 'MZAPI - TransferWallet';
        $request = $this->_readJsonRequest();
        $module = 'TransferWallet';
        $rand = $this->random_string();
        $appLogger = new AppLogger();

        $message = trim(htmlentities($request['TerminalCode'])) . " | " . trim(htmlentities($request['ServiceID'])) . " | " . trim(htmlentities($request['Usermode']));
        $appLogger->log($appLogger->logdate, "[request]", $message);

        $RTGApiWrapper = new RealtimeGamingAPIWrapper();
        $terminalSessionsModel = new TerminalSessionsModel();
        $mzTransactionTransferModel = new MzTransactionTransferModel();
        $membersModel = new MembersModel();
        $refServicesModel = new RefServicesModel();
        $terminalServices = new TerminalServices();
        $memberServicesModel = new MemberServicesModel();
        $terminalsModel = new TerminalsModel();
        $LPErrorLogsModel = new LpErrorLogsModel();

        $TerminalCode = trim(htmlentities($request['TerminalCode']));
        $NewServiceID = trim(htmlentities($request['ServiceID']));
        $NewUsermode = trim(htmlentities($request['Usermode']));

        if (isset($TerminalCode) && $TerminalCode !== '' && isset($NewServiceID) && $NewServiceID !== '' && isset($NewUsermode) && $NewUsermode !== '') {

            //Check if has session
            $checkActiveSession = $terminalSessionsModel->checkActiveSession($TerminalCode);
            $CheckSession = $checkActiveSession['Cnt'];


            if ($CheckSession > 0) {
                //Get Active Wallet
                $checkActiveWallet = $terminalSessionsModel->checkActiveWallet($TerminalCode);
                $ActiveServiceID = $checkActiveWallet['serviceid'];

                if ($NewUsermode <> 3) {
                    $errCode = 54;
                    $transMsg = '[LP #54] Transferring of wallet is only applicable to user-based casino.';

                    $LPErrorLogsModel->insertLPlogs(Yii::app()->params['systemNode'], null, null, null, $transMsg, null, null);
                    $appLogger->log($appLogger->logdate, "[response]", $transMsg);

                    $data = CommonController::retMsgTransferWallet($transMsg, $errCode);
                    $this->_sendResponse(200, $data);
                    exit;
                }

                if ($NewServiceID <> $ActiveServiceID) {

                    //Get Terminal Session Details 
                    $getTerminalSessionDetails = $terminalSessionsModel->getTerminalSessionDetails($TerminalCode);

                    if (!empty($getTerminalSessionDetails)) {

                        $TerminalID = $getTerminalSessionDetails['TerminalID'];
                        $MID = $getTerminalSessionDetails['MID'];
                        $CardNumber = $getTerminalSessionDetails['LoyaltyCardNumber'];
                        $UBServiceLogin = $getTerminalSessionDetails['UBServiceLogin'];
                        $UBServicePassword = $getTerminalSessionDetails['UBServicePassword'];
                        $UBHashedPassword = $getTerminalSessionDetails['UBHashedServicePassword'];
                        $LastBalance = $getTerminalSessionDetails['LastBalance'];
                        $TransactionSummaryID = $getTerminalSessionDetails['TransactionSummaryID'];

                        $getSiteID = $terminalsModel->getSiteID($TerminalID);

                        if (!empty($getSiteID)) {
                            $SiteID = $getSiteID['SiteID'];
                        } else {
                            $errCode = 49;
                            $transMsg = '[LP #49] Failed to get Site Details';

                            $appLogger->log($appLogger->logdate, "[response]", $transMsg);

                            $LPErrorLogsModel->insertLPlogs(Yii::app()->params['systemNode'], $TerminalID, $MID, $CardNumber, $transMsg, null, null);
                            $data = CommonController::retMsgTransferWallet($transMsg, $errCode);
                            $this->_sendResponse(200, $data);
                            exit;
                        }

                        if ($NewUsermode == 3) {
                            $getMemberServicesDetails = $memberServicesModel->getCasinoCredentials($MID, $NewServiceID);
                            if (!empty($getMemberServicesDetails)) {
                                $NewUBServiceLogin = $getMemberServicesDetails['ServiceUsername'];
                                $NewUBServicePassword = $getMemberServicesDetails['ServicePassword'];
                                $NewUBHashedPassword = $getMemberServicesDetails['HashedServicePassword'];
                            } else {
                                $errCode = 47;
                                $transMsg = '[LP #47] Failed to get memberservices details';

                                $LPErrorLogsModel->insertLPlogs(Yii::app()->params['systemNode'], $TerminalID, $MID, $CardNumber, $transMsg, null, null);
                                $appLogger->log($appLogger->logdate, "[response]", $transMsg);

                                $data = CommonController::retMsgTransferWallet($transMsg, $errCode);
                                $this->_sendResponse(200, $data);
                                exit;
                            }
                        }

                        //Check Service Group of New Provider
                        $getServiceGrpNameByIdNewProvider = $refServicesModel->getServiceGrpNameById($NewServiceID);

                        if (!empty($getServiceGrpNameByIdNewProvider)) {
                            $ServiceGroupNewProvider = $getServiceGrpNameByIdNewProvider['ServiceGroupName'];
                        } else {
                            $errCode = 46;
                            $transMsg = '[LP #46] Failed to get casino details';

                            $LPErrorLogsModel->insertLPlogs(Yii::app()->params['systemNode'], $TerminalID, $MID, $CardNumber, $transMsg, null, null);
                            $appLogger->log($appLogger->logdate, "[response]", $transMsg);

                            $data = CommonController::retMsgTransferWallet($transMsg, $errCode);
                            $this->_sendResponse(200, $data);
                            exit;
                        }

                        $checkingMaximumDeposit = Yii::app()->params['isMaximumDepositOn'];

                        if ($checkingMaximumDeposit == 1) {
                            //Check Service Group
                            $getServiceGrpNameByIdCurrentProvider = $refServicesModel->getServiceGrpNameById($ActiveServiceID);

                            if (!empty($getServiceGrpNameByIdCurrentProvider)) {

                                $ServiceGroupCurrentProvider = $getServiceGrpNameByIdCurrentProvider['ServiceGroupName'];

                                //Get Balance to Current Provider
                                $CurrentProviderBalance = $this->_getBalance($ActiveServiceID, $UBServiceLogin, $UBServicePassword, $ServiceGroupCurrentProvider, $MID, $CardNumber, $TerminalID);
                                $maxDepositAmount = Yii::app()->params['maxDepositAmount'];

                                if (is_numeric($CurrentProviderBalance)) {

                                    if ($CurrentProviderBalance > $maxDepositAmount) {
                                        $errCode = 45;
                                        $transMsg = '[LP #45] Transfer amount has exceeded the maximum deposit amount.';

                                        $LPErrorLogsModel->insertLPlogs(Yii::app()->params['systemNode'], $TerminalID, $MID, $CardNumber, $transMsg, null, null);
                                        $appLogger->log($appLogger->logdate, "[response]", $transMsg);

                                        $data = CommonController::retMsgTransferWallet($transMsg, $errCode);
                                        $this->_sendResponse(200, $data);
                                        exit;
                                    }
                                } else {
                                    $errCode = 44;
                                    $transMsg = '[LP #44] Failed to get balance from current provider';

                                    $LPErrorLogsModel->insertLPlogs(Yii::app()->params['systemNode'], $TerminalID, $MID, $CardNumber, $transMsg, null, null);
                                    $appLogger->log($appLogger->logdate, "[response]", $transMsg);

                                    $data = CommonController::retMsgTransferWallet($transMsg, $errCode);
                                    $this->_sendResponse(200, $data);
                                    exit;
                                }
                            } else {
                                $errCode = 43;
                                $transMsg = '[LP #43] Failed to get service group name';

                                $LPErrorLogsModel->insertLPlogs(Yii::app()->params['systemNode'], $TerminalID, $MID, $CardNumber, $transMsg, null, null);
                                $appLogger->log($appLogger->logdate, "[response]", $transMsg);

                                $data = CommonController::retMsgTransferWallet($transMsg, $errCode);
                                $this->_sendResponse(200, $data);
                                exit;
                            }
                        }

                        //Get Current Balance
                        $NewProviderBalance = $this->_getBalance($NewServiceID, $NewUBServiceLogin, $NewUBServicePassword, $ServiceGroupNewProvider, $MID, $CardNumber, $TerminalID);

                        if (is_numeric($NewProviderBalance)) {

                            if ($NewProviderBalance == 0) {

                                //Check Service Group
                                $getServiceGrpNameByIdCurrentProvider = $refServicesModel->getServiceGrpNameById($ActiveServiceID);

                                if (!empty($getServiceGrpNameByIdCurrentProvider)) {

                                    $ServiceGroupCurrentProvider = $getServiceGrpNameByIdCurrentProvider['ServiceGroupName'];

                                    //Get Pending Games to Current Provider
                                    $PendingGame = '';

                                    if ($ServiceGroupCurrentProvider == "RTG" || $ServiceGroupCurrentProvider == "RTG2") {
                                        $GetPendingGames = $RTGApiWrapper->getPendingGamesRTG($UBServiceLogin, $ActiveServiceID);
                                        $PendingGame = $GetPendingGames;

                                        $requestBody = array("UBServiceLogin" => $NewUBServiceLogin, "ServiceID" => $ActiveServiceID);
                                        $request = json_encode($requestBody);
                                        $response = json_encode($GetPendingGames);
                                    }

                                    if ($ServiceGroupCurrentProvider == "HAB") {
                                        $HabaneroApiWrapper = new HabaneroAPIWrapper(Yii::app()->params->gameapi[$ActiveServiceID - 1], Yii::app()->params['HB_APIkey'], Yii::app()->params['HB_BrandID']);
                                        $GetPendingGames = $HabaneroApiWrapper->GetPendingGamesHabanero($UBServiceLogin, $UBServicePassword);

                                        if ($GetPendingGames['ErrorCode'] == 0) {
                                            if ($GetPendingGames['TransactionInfo'][0]['GameName'] != null || $GetPendingGames['TransactionInfo'][0]['GameName'] != '') {
                                                $PendingGame['IsSucceed'] = true;
                                                $PendingGame['PendingGames']['GetPendingGamesByPIDResult']['Gamename'] = $pendingGames['TransactionInfo'][0]['GameName'];
                                            }
                                        }

                                        $requestBody = array("ActiveServiceID" => $ActiveServiceID, "UBServiceLogin" => $UBServiceLogin, "UBServicePassword" => $UBServicePassword);
                                        $request = json_encode($requestBody);
                                        $response = json_encode($GetPendingGames);
                                    }

                                    //Check if has Pending Games
                                    if (!empty($PendingGame) && is_array($PendingGame)) {
                                        $errCode = 42;
                                        $transMsg = '[LP #42] There was a pending game bet.';

                                        $LPErrorLogsModel->insertLPlogs(Yii::app()->params['systemNode'], $TerminalID, $MID, $CardNumber, $transMsg, $request, $response);
                                        $appLogger->log($appLogger->logdate, "[response]", $transMsg);
                                        $data = CommonController::retMsgTransferWallet($transMsg, $errCode);
                                        $this->_sendResponse(200, $data);
                                        exit;
                                    }

                                    //check terminal sessions activeservicestatus
                                    $checkActiveServiceStatus = $terminalSessionsModel->checkActiveServiceStatus($TerminalCode);
                                    $ActiveServiceStatus = $checkActiveServiceStatus['ActiveServiceStatus'];

                                    //if activeservicestatus == 9
                                    if ($ActiveServiceStatus == 9) {
                                        $errCode = 41;
                                        $transMsg = '[LP #41.2] There is already a pending Launchpad transfer transaction for this account. Please contact customer service.';

                                        $LPErrorLogsModel->insertLPlogs(Yii::app()->params['systemNode'], $TerminalID, $MID, $CardNumber, $transMsg, null, null);
                                        $appLogger->log($appLogger->logdate, "[response]", $transMsg);
                                        $data = CommonController::retMsgTransferWallet($transMsg, $errCode);
                                        $this->_sendResponse(200, $data);
                                        exit;
                                    }


                                    //Get Balance to Current Provider
                                    $CurrentProviderBalance = $this->_getBalance($ActiveServiceID, $UBServiceLogin, $UBServicePassword, $ServiceGroupCurrentProvider, $MID, $CardNumber, $TerminalID);

                                    $hundredKchecking = Yii::app()->params['hundredKchecking'];
                                    $maxRTGdepositAmount = Yii::app()->params['maxRTGdepositAmount'];

                                    if ($hundredKchecking == 1) {
                                        if ($ServiceGroupNewProvider == 'RTG' || $ServiceGroupNewProvider == 'RTG2' && $CurrentProviderBalance > $maxRTGdepositAmount) {
                                            $errCode = 40;
                                            $transMsg = '[LP #40] You are not allowed to transfer an amount greater than ' . number_format($maxRTGdepositAmount) . ' to this casino.';

                                            $LPErrorLogsModel->insertLPlogs(Yii::app()->params['systemNode'], $TerminalID, $MID, $CardNumber, $transMsg, null, null);
                                            $appLogger->log($appLogger->logdate, "[response]", $transMsg);
                                            $data = CommonController::retMsgTransferWallet($transMsg, $errCode);
                                            $this->_sendResponse(200, $data);
                                            exit;
                                        }

                                        if ($ServiceGroupCurrentProvider == 'RTG' || $ServiceGroupCurrentProvider == 'RTG2' && $CurrentProviderBalance >= 999999) {
                                            $errCode = 40;
                                            $transMsg = '[LP #40.1] You are not allowed to transfer an amount greater than or equal to 1 Million from this casino';

                                            $LPErrorLogsModel->insertLPlogs(Yii::app()->params['systemNode'], $TerminalID, $MID, $CardNumber, $transMsg, null, null);
                                            $appLogger->log($appLogger->logdate, "[response]", $transMsg);
                                            $data = CommonController::retMsgTransferWallet($transMsg, $errCode);
                                            $this->_sendResponse(200, $data);
                                            exit;
                                        }
                                    }

                                    if (is_numeric($CurrentProviderBalance)) {

                                        if ($ActiveServiceStatus == 1) {

                                            //update activeservicestatus to 9
                                            $ActiveServiceStatus = 9;

                                            $UpdateToNine = $terminalSessionsModel->updateActiveServiceStatus($TerminalID, $CardNumber, $ActiveServiceStatus);

                                            if ($UpdateToNine) {

                                                if ($CurrentProviderBalance == 0) {

                                                    $FromTransactionType = 'W';
                                                    $FromAmount = 0;
                                                    $ToAmount = 0;
                                                    $FromServiceID = $ActiveServiceID;
                                                    $FromStatus = 1;
                                                    $ToStatus = 1;
                                                    $ToTransactionType = 'D';
                                                    $ToServiceID = $NewServiceID;
                                                    $TransferStatus = 9;
                                                    $identifier = 0;

                                                    //Insert mztransactiontransfer
                                                    $insertMzTransactionTransfer = $mzTransactionTransferModel->insertZeroBalance($TransactionSummaryID, $SiteID, $TerminalID, $MID, $CardNumber, $FromTransactionType, $FromAmount, $ToAmount, $FromServiceID, $FromStatus, $ToStatus, $ToTransactionType, $ToServiceID, $TransferStatus);

                                                    if ($insertMzTransactionTransfer > 0) {

                                                        $ActiveServiceStatus = 1;
                                                        $LastBalance = 0;

                                                        //Update terminalsessions    
                                                        $updateTerminalSession = $terminalSessionsModel->updateTerminalSession($NewServiceID, $TerminalID, $CardNumber, $NewUBServiceLogin, $NewUBServicePassword, $NewUBHashedPassword, $ActiveServiceStatus, $LastBalance);

                                                        if ($updateTerminalSession) {
                                                            //Update members
                                                            $updateMember = $membersModel->updateMembers($NewServiceID, $MID);

                                                            $errCode = 0;
                                                            $transMsg = 'Successful Transfer of Zero Balance';

                                                            $appLogger->log($appLogger->logdate, "[response]", $transMsg);
                                                            $data = CommonController::retMsgTransferWallet($transMsg, $errCode);
                                                            $this->_sendResponse(200, $data);
                                                            exit;
                                                        } else {
                                                            $errCode = 39;
                                                            $transMsg = '[LP #39] Failed to update session details';

                                                            $LPErrorLogsModel->insertLPlogs(Yii::app()->params['systemNode'], $TerminalID, $MID, $CardNumber, $transMsg, null, null);
                                                            $appLogger->log($appLogger->logdate, "[response]", $transMsg);
                                                            $data = CommonController::retMsgTransferWallet($transMsg, $errCode);
                                                            $this->_sendResponse(200, $data);
                                                            exit;
                                                        }
                                                    } else {
                                                        $errCode = 38;
                                                        $transMsg = '[LP #038] Failed to insert transaction transfer details';

                                                        $LPErrorLogsModel->insertLPlogs(Yii::app()->params['systemNode'], $TerminalID, $MID, $CardNumber, $transMsg, null, null);
                                                        $appLogger->log($appLogger->logdate, "[response]", $transMsg);
                                                        $data = CommonController::retMsgTransferWallet($transMsg, $errCode);
                                                        $this->_sendResponse(200, $data);
                                                        exit;
                                                    }
                                                } else {
                                                    $FromTransactionType = 'W';
                                                    $FromAmount = $CurrentProviderBalance;
                                                    $ToAmount = null;
                                                    $FromServiceID = $ActiveServiceID;
                                                    $FromStatus = 0;
                                                    $ToStatus = null;
                                                    $ToTransactionType = null;
                                                    $ToServiceID = $NewServiceID;
                                                    $TransferStatus = 0;
                                                    $identifier = 0;

                                                    //Insert mztransactiontransfer
                                                    $insertMzTransactionTransfer = $mzTransactionTransferModel->insert($TransactionSummaryID, $SiteID, $TerminalID, $MID, $CardNumber, $FromTransactionType, $FromAmount, $ToAmount, $FromServiceID, $FromStatus, $ToStatus, $ToTransactionType, $ToServiceID, $TransferStatus, null, $identifier);


                                                    if ($insertMzTransactionTransfer > 0) {

                                                        //Assign Variable
                                                        $MzTransactionTransferID = $insertMzTransactionTransfer;

                                                        $transtype = 'MZW';

                                                        //CALL WITHDRAW API TO CURRENT PROVIDER
                                                        $WithdrawToCurrentProvider = $this->_withdraw($MzTransactionTransferID, $ActiveServiceID, $UBServiceLogin, $UBServicePassword, $CurrentProviderBalance, $TerminalID, $TerminalCode, $ServiceGroupCurrentProvider, $transtype, $MID, $CardNumber);


                                                        if (!empty($WithdrawToCurrentProvider) && $WithdrawToCurrentProvider['IsSuccess'] == true) {

                                                            $getMaxTransferID = $mzTransactionTransferModel->getMaxTransferID($TransactionSummaryID);

                                                            if (!empty($getMaxTransferID)) {

                                                                $TransferID = $getMaxTransferID['MaxTransferID'];
                                                                $FromServiceTransID = $WithdrawToCurrentProvider['TransactionReferenceID'];
                                                                $FromServiceStatus = $WithdrawToCurrentProvider['Status'];
                                                                $FromStatus = 1;
                                                                $TransferStatus = 1;

                                                                //update mztransactiontransfer
                                                                $updateMzTransactionTransfer = $mzTransactionTransferModel->updateFromMzTransactionTransfer($FromServiceTransID, $FromServiceStatus, $FromStatus, $TransferStatus, $TransferID, $TransactionSummaryID);


                                                                if ($updateMzTransactionTransfer) {

                                                                    //get balance to current provider
                                                                    $BalancetoCurrentProvider = $this->_getBalance($ActiveServiceID, $UBServiceLogin, $UBServicePassword, $ServiceGroupCurrentProvider, $MID, $CardNumber, $TerminalID);

                                                                    //if balance is == 0
                                                                    if (is_numeric($BalancetoCurrentProvider)) {
                                                                        if ($BalancetoCurrentProvider == 0) {


                                                                            $ToTransactionType = 'D';
                                                                            $ToAmount = $WithdrawToCurrentProvider['WithdrawnAmount'];
                                                                            $ToStatus = 0;
                                                                            $TransferStatus = 3;
                                                                            $identifier = 0;

                                                                            //update mztransactiontransfer 
                                                                            $updateMzTransactionTransfer = $mzTransactionTransferModel->updateToMzTransactionTransfer(null, $ToAmount, $ToTransactionType, null, $ToStatus, $TransferStatus, $TransferID, $TransactionSummaryID, $identifier);

                                                                            if ($updateMzTransactionTransfer) {

                                                                                $transtype = 'MZD';

                                                                                //DEPOSIT TO NEW PROVIDER
                                                                                $DepositToNewProvider = $this->_deposit($MzTransactionTransferID, $NewServiceID, $NewUBServiceLogin, $NewUBServicePassword, $CurrentProviderBalance, $TerminalID, $TerminalCode, $ServiceGroupNewProvider, $transtype, $MID, $CardNumber);



                                                                                if (!empty($DepositToNewProvider) && $DepositToNewProvider['IsSuccess'] == true) {

                                                                                    $ToServiceTransID = $DepositToNewProvider['TransactionReferenceID'];
                                                                                    $ToServiceStatus = $DepositToNewProvider['Status'];
                                                                                    $ToStatus = 1;
                                                                                    $TransferStatus = 4;
                                                                                    $identifier = 1;
                                                                                    $ToAmount = $DepositToNewProvider['DepositedAmount'];

                                                                                    //update mztransactiontransfer 
                                                                                    $updateMzTransactionTransfer = $mzTransactionTransferModel->updateToMzTransactionTransfer($ToServiceTransID, $ToAmount, null, $ToServiceStatus, $ToStatus, $TransferStatus, $TransferID, $TransactionSummaryID, $identifier);

                                                                                    if ($updateMzTransactionTransfer) {

                                                                                        //Get Balance to new provider
                                                                                        $BalancetoNewProvider = $this->_getBalance($NewServiceID, $NewUBServiceLogin, $NewUBServicePassword, $ServiceGroupNewProvider, $MID, $CardNumber, $TerminalID);


                                                                                        if (is_numeric($BalancetoNewProvider)) {

                                                                                            if ($BalancetoNewProvider == $WithdrawToCurrentProvider['WithdrawnAmount']) {
                                                                                                $ActiveServiceStatus = 1;
                                                                                                $LastBalance = $BalancetoNewProvider;

                                                                                                //update terminalsessions
                                                                                                $updateTerminalSession = $terminalSessionsModel->updateTerminalSession($NewServiceID, $TerminalID, $CardNumber, $NewUBServiceLogin, $NewUBServicePassword, $NewUBHashedPassword, $ActiveServiceStatus, $LastBalance);


                                                                                                if ($updateTerminalSession) {
                                                                                                    //update members
                                                                                                    $updateMember = $membersModel->updateMembers($NewServiceID, $MID);

                                                                                                    $errCode = 0;
                                                                                                    $transMsg = 'Successful Wallet Transfer';

                                                                                                    $appLogger->log($appLogger->logdate, "[response]", $transMsg);
                                                                                                    $data = CommonController::retMsgTransferWallet($transMsg, $errCode);
                                                                                                    $this->_sendResponse(200, $data);
                                                                                                    exit;
                                                                                                } else {
                                                                                                    $errCode = 37;
                                                                                                    $transMsg = '[LP #37] Failed to update session details';

                                                                                                    $LPErrorLogsModel->insertLPlogs(Yii::app()->params['systemNode'], $TerminalID, $MID, $CardNumber, $transMsg, null, null);
                                                                                                    $appLogger->log($appLogger->logdate, "[response]", $transMsg);
                                                                                                    $data = CommonController::retMsgTransferWallet($transMsg, $errCode);
                                                                                                    $this->_sendResponse(200, $data);
                                                                                                    exit;
                                                                                                }
                                                                                            } else {
                                                                                                $TransferStatus = 91;

                                                                                                //update mztransactiontransfer 
                                                                                                $updateMzTransactionTransfer = $mzTransactionTransferModel->updateStatusMzTransactionTransfer($TransferStatus, $TransferID, $TransactionSummaryID);

                                                                                                if ($updateMzTransactionTransfer) {
                                                                                                    $errCode = 36;
                                                                                                    $transMsg = '[LP #36] Transferred balance does not match the previous balance.';

                                                                                                    $appLogger->log($appLogger->logdate, "[response]", $transMsg);
                                                                                                    $data = CommonController::retMsgTransferWallet($transMsg, $errCode);
                                                                                                    $this->_sendResponse(200, $data);
                                                                                                    exit;
                                                                                                } else {
                                                                                                    $errCode = 35;
                                                                                                    $transMsg = '[LP #35] Failed to update transaction transfer details';

                                                                                                    $LPErrorLogsModel->insertLPlogs(Yii::app()->params['systemNode'], $TerminalID, $MID, $CardNumber, $transMsg, null, null);
                                                                                                    $appLogger->log($appLogger->logdate, "[response]", $transMsg);
                                                                                                    $data = CommonController::retMsgTransferWallet($transMsg, $errCode);
                                                                                                    $this->_sendResponse(200, $data);
                                                                                                    exit;
                                                                                                }
                                                                                            }
                                                                                        } else {

                                                                                            //Get Balance to new provider
                                                                                            $BalancetoNewProvider = $this->_getBalance($NewServiceID, $NewUBServiceLogin, $NewUBServicePassword, $ServiceGroupNewProvider, $MID, $CardNumber, $TerminalID);


                                                                                            if (is_numeric($BalancetoNewProvider)) {

                                                                                                if ($BalancetoNewProvider == $WithdrawToCurrentProvider['WithdrawnAmount']) {
                                                                                                    $ActiveServiceStatus = 1;
                                                                                                    $LastBalance = $BalancetoNewProvider;

                                                                                                    //update terminalsessions
                                                                                                    $updateTerminalSession = $terminalSessionsModel->updateTerminalSession($NewServiceID, $TerminalID, $CardNumber, $NewUBServiceLogin, $NewUBServicePassword, $NewUBHashedPassword, $ActiveServiceStatus, $LastBalance);


                                                                                                    if ($updateTerminalSession) {
                                                                                                        //update members
                                                                                                        $updateMember = $membersModel->updateMembers($NewServiceID, $MID);

                                                                                                        $errCode = 0;
                                                                                                        $transMsg = 'Successful Wallet Transfer';

                                                                                                        $appLogger->log($appLogger->logdate, "[response]", $transMsg);
                                                                                                        $data = CommonController::retMsgTransferWallet($transMsg, $errCode);
                                                                                                        $this->_sendResponse(200, $data);
                                                                                                        exit;
                                                                                                    } else {
                                                                                                        $errCode = 37;
                                                                                                        $transMsg = '[LP #37] Failed to update session details';

                                                                                                        $LPErrorLogsModel->insertLPlogs(Yii::app()->params['systemNode'], $TerminalID, $MID, $CardNumber, $transMsg, null, null);
                                                                                                        $appLogger->log($appLogger->logdate, "[response]", $transMsg);
                                                                                                        $data = CommonController::retMsgTransferWallet($transMsg, $errCode);
                                                                                                        $this->_sendResponse(200, $data);
                                                                                                        exit;
                                                                                                    }
                                                                                                } else {
                                                                                                    $TransferStatus = 91;

                                                                                                    //update mztransactiontransfer 
                                                                                                    $updateMzTransactionTransfer = $mzTransactionTransferModel->updateStatusMzTransactionTransfer($TransferStatus, $TransferID, $TransactionSummaryID);

                                                                                                    if ($updateMzTransactionTransfer) {
                                                                                                        $errCode = 36;
                                                                                                        $transMsg = '[LP #36] Transferred balance does not match the previous balance.';

                                                                                                        $LPErrorLogsModel->insertLPlogs(Yii::app()->params['systemNode'], $TerminalID, $MID, $CardNumber, $transMsg, null, null);
                                                                                                        $appLogger->log($appLogger->logdate, "[response]", $transMsg);
                                                                                                        $data = CommonController::retMsgTransferWallet($transMsg, $errCode);
                                                                                                        $this->_sendResponse(200, $data);
                                                                                                        exit;
                                                                                                    } else {
                                                                                                        $errCode = 35;
                                                                                                        $transMsg = '[LP #35] Failed to update transaction transfer details';

                                                                                                        $LPErrorLogsModel->insertLPlogs(Yii::app()->params['systemNode'], $TerminalID, $MID, $CardNumber, $transMsg, null, null);
                                                                                                        $appLogger->log($appLogger->logdate, "[response]", $transMsg);
                                                                                                        $data = CommonController::retMsgTransferWallet($transMsg, $errCode);
                                                                                                        $this->_sendResponse(200, $data);
                                                                                                        exit;
                                                                                                    }
                                                                                                }
                                                                                            } else {
                                                                                                $errCode = 34;
                                                                                                $transMsg = '[LP #34] Failed to get balance from new provider';

                                                                                                $LPErrorLogsModel->insertLPlogs(Yii::app()->params['systemNode'], $TerminalID, $MID, $CardNumber, $transMsg, null, null);
                                                                                                $appLogger->log($appLogger->logdate, "[response]", $transMsg);
                                                                                                $data = CommonController::retMsgTransferWallet($transMsg, $errCode);
                                                                                                $this->_sendResponse(200, $data);
                                                                                                exit;
                                                                                            }
                                                                                        }
                                                                                    } else {
                                                                                        $errCode = 33;
                                                                                        $transMsg = '[LP #33] Failed to update transaction transfer details';

                                                                                        $LPErrorLogsModel->insertLPlogs(Yii::app()->params['systemNode'], $TerminalID, $MID, $CardNumber, $transMsg, null, null);
                                                                                        $appLogger->log($appLogger->logdate, "[response]", $transMsg);
                                                                                        $data = CommonController::retMsgTransferWallet($transMsg, $errCode);
                                                                                        $this->_sendResponse(200, $data);
                                                                                        exit;
                                                                                    }
                                                                                } else {

                                                                                    $errCode = 20;
                                                                                    $transMsg = '[LP #20] Failed to deposit to new provider';
                                                                                    $LPErrorLogsModel->insertLPlogs(Yii::app()->params['systemNode'], $TerminalID, $MID, $CardNumber, $transMsg, null, null);
                                                                                    $appLogger->log($appLogger->logdate, "[response]", $transMsg);

                                                                                    $ToServiceTransID = $DepositToNewProvider['TransactionReferenceID'];
                                                                                    $ToServiceStatus = $DepositToNewProvider['Status'];
                                                                                    $ToStatus = 2;
                                                                                    $TransferStatus = 5;
                                                                                    $identifier = 1;

                                                                                    //update mztransactiontransfer 
                                                                                    $updateMzTransactionTransfer = $mzTransactionTransferModel->updateToMzTransactionTransfer($ToServiceTransID, null, null, $ToServiceStatus, $ToStatus, $TransferStatus, $TransferID, $TransactionSummaryID, $identifier);

                                                                                    if ($updateMzTransactionTransfer) {

                                                                                        $FromTransactionType = 'W';
                                                                                        $FromAmount = $WithdrawToCurrentProvider['WithdrawnAmount'];
                                                                                        $FromServiceStatus = $WithdrawToCurrentProvider['Status'];
                                                                                        $FromServiceID = 1;
                                                                                        $FromStatus = 1;
                                                                                        $ToStatus = 0;
                                                                                        $ToTransactionType = 'RD';
                                                                                        $ToServiceID = $ActiveServiceID;
                                                                                        $TransferStatus = 6;
                                                                                        $ToAmount = 0;
                                                                                        $identifier = 1;

                                                                                        //Insert mztransactiontransfer
                                                                                        $insertMzTransactionTransfer = $mzTransactionTransferModel->insert($TransactionSummaryID, $SiteID, $TerminalID, $MID, $CardNumber, $FromTransactionType, $FromAmount, $ToAmount, $FromServiceID, $FromStatus, $ToStatus, $ToTransactionType, $ToServiceID, $TransferStatus, $FromServiceStatus, $identifier);

                                                                                        if ($insertMzTransactionTransfer > 0) {
                                                                                            $MzTransactionTransferID = $insertMzTransactionTransfer;
                                                                                            $TransferID = $MzTransactionTransferID;

                                                                                            //get balance to current provider
                                                                                            $BalancetoCurrentProvider = $this->_getBalance($ActiveServiceID, $UBServiceLogin, $UBServicePassword, $ServiceGroupCurrentProvider, $MID, $CardNumber, $TerminalID);


                                                                                            if (is_numeric($BalancetoCurrentProvider)) {
                                                                                                if ($BalancetoCurrentProvider == 0) {

                                                                                                    $transtype = 'MZRD';

                                                                                                    //CALL API RE-DEPOSIT
                                                                                                    $DepositToNewProvider = $this->_deposit($MzTransactionTransferID, $ActiveServiceID, $UBServiceLogin, $UBServicePassword, $WithdrawToCurrentProvider['WithdrawnAmount'], $TerminalID, $TerminalCode, $ServiceGroupCurrentProvider, $transtype, $MID, $CardNumber);

                                                                                                    if (!empty($DepositToNewProvider) && $DepositToNewProvider['IsSuccess'] == true) {

                                                                                                        $ToServiceTransID = $DepositToNewProvider['TransactionReferenceID'];
                                                                                                        $ToServiceStatus = $DepositToNewProvider['Status'];
                                                                                                        $ToAmount = $DepositToNewProvider['DepositedAmount'];
                                                                                                        $ToStatus = 1;
                                                                                                        $TransferStatus = 7;
                                                                                                        $identifier = 1;


                                                                                                        //update mztransactiontransfer 
                                                                                                        $updateMzTransactionTransfer = $mzTransactionTransferModel->updateToMzTransactionTransfer($ToServiceTransID, $ToAmount, null, $ToServiceStatus, $ToStatus, $TransferStatus, $TransferID, $TransactionSummaryID, $identifier);



                                                                                                        if ($updateMzTransactionTransfer) {

                                                                                                            //get balance to current provider
                                                                                                            $BalancetoCurrentProvider = $this->_getBalance($ActiveServiceID, $UBServiceLogin, $UBServicePassword, $ServiceGroupCurrentProvider, $MID, $CardNumber, $TerminalID);

                                                                                                            if (is_numeric($BalancetoCurrentProvider)) {

                                                                                                                if ($BalancetoCurrentProvider == $WithdrawToCurrentProvider['WithdrawnAmount']) {
                                                                                                                    $ActiveServiceStatus = 1;
                                                                                                                    $LastBalance = $DepositToNewProvider['DepositedAmount'];

                                                                                                                    //Update terminalsessions    
                                                                                                                    $updateTerminalSession = $terminalSessionsModel->updateTerminalSession($ActiveServiceID, $TerminalID, $CardNumber, $UBServiceLogin, $UBServicePassword, $UBHashedPassword, $ActiveServiceStatus, $LastBalance);

                                                                                                                    if ($updateTerminalSession) {
                                                                                                                        //update members
                                                                                                                        $updateMember = $membersModel->updateMembers($ActiveServiceID, $MID);
                                                                                                                        $errCode = 1000;
                                                                                                                        $transMsg = 'Successful Re-Deposit';

                                                                                                                        $appLogger->log($appLogger->logdate, "[response]", $transMsg);
                                                                                                                        $data = CommonController::retMsgTransferWallet($transMsg, $errCode);
                                                                                                                        $this->_sendResponse(200, $data);
                                                                                                                        exit;
                                                                                                                    } else {
                                                                                                                        $errCode = 32;
                                                                                                                        $transMsg = '[LP #32] Failed to update session details';

                                                                                                                        $LPErrorLogsModel->insertLPlogs(Yii::app()->params['systemNode'], $TerminalID, $MID, $CardNumber, $transMsg, null, null);
                                                                                                                        $appLogger->log($appLogger->logdate, "[response]", $transMsg);
                                                                                                                        $data = CommonController::retMsgTransferWallet($transMsg, $errCode);
                                                                                                                        $this->_sendResponse(200, $data);
                                                                                                                        exit;
                                                                                                                    }
                                                                                                                } else {

                                                                                                                    $TransferStatus = 92;

                                                                                                                    //update mztransactiontransfer 
                                                                                                                    $updateMzTransactionTransfer = $mzTransactionTransferModel->updateStatusMzTransactionTransfer($TransferStatus, $TransferID, $TransactionSummaryID);

                                                                                                                    if ($updateMzTransactionTransfer) {
                                                                                                                        $errCode = 31;
                                                                                                                        $transMsg = '[LP #31] Re-Deposit amount is not equal to withdrawn amount';

                                                                                                                        $LPErrorLogsModel->insertLPlogs(Yii::app()->params['systemNode'], $TerminalID, $MID, $CardNumber, $transMsg, null, null);
                                                                                                                        $appLogger->log($appLogger->logdate, "[response]", $transMsg);
                                                                                                                        $data = CommonController::retMsgTransferWallet($transMsg, $errCode);
                                                                                                                        $this->_sendResponse(200, $data);
                                                                                                                        exit;
                                                                                                                    } else {
                                                                                                                        $errCode = 30;
                                                                                                                        $transMsg = '[LP #30] Failed to update transaction transfer details';
                                                                                                                        $LPErrorLogsModel->insertLPlogs(Yii::app()->params['systemNode'], $TerminalID, $MID, $CardNumber, $transMsg, null, null);
                                                                                                                        $appLogger->log($appLogger->logdate, "[response]", $transMsg);
                                                                                                                        $data = CommonController::retMsgTransferWallet($transMsg, $errCode);
                                                                                                                        $this->_sendResponse(200, $data);
                                                                                                                        exit;
                                                                                                                    }
                                                                                                                }
                                                                                                            } else {
                                                                                                                $errCode = 29;
                                                                                                                $transMsg = '[LP #29] Failed to get balance from current provide';

                                                                                                                $LPErrorLogsModel->insertLPlogs(Yii::app()->params['systemNode'], $TerminalID, $MID, $CardNumber, $transMsg, null, null);
                                                                                                                $appLogger->log($appLogger->logdate, "[response]", $transMsg);
                                                                                                                $data = CommonController::retMsgTransferWallet($transMsg, $errCode);
                                                                                                                $this->_sendResponse(200, $data);
                                                                                                                exit;
                                                                                                            }
                                                                                                        } else {
                                                                                                            $errCode = 28;
                                                                                                            $transMsg = '[LP #28] Failed to update transaction transfer details';

                                                                                                            $LPErrorLogsModel->insertLPlogs(Yii::app()->params['systemNode'], $TerminalID, $MID, $CardNumber, $transMsg, null, null);
                                                                                                            $appLogger->log($appLogger->logdate, "[response]", $transMsg);
                                                                                                            $data = CommonController::retMsgTransferWallet($transMsg, $errCode);
                                                                                                            $this->_sendResponse(200, $data);
                                                                                                            exit;
                                                                                                        }
                                                                                                    } else {

                                                                                                        $ToServiceTransID = $DepositToNewProvider['TransactionReferenceID'];
                                                                                                        $ToServiceStatus = $DepositToNewProvider['Status'];
                                                                                                        $ToStatus = 2;
                                                                                                        $TransferStatus = 8;
                                                                                                        $identifier = 1;
                                                                                                        $TransferID = $MzTransactionTransferID;

                                                                                                        //update mztransactiontransfer 
                                                                                                        $updateMzTransactionTransfer = $mzTransactionTransferModel->updateToMzTransactionTransfer($ToServiceTransID, null, null, $ToServiceStatus, $ToStatus, $TransferStatus, $TransferID, $TransactionSummaryID, $identifier);


                                                                                                        if ($updateMzTransactionTransfer) {
                                                                                                            $errCode = 27;
                                                                                                            $transMsg = '[LP #27] Failed Re-Deposit';

                                                                                                            $LPErrorLogsModel->insertLPlogs(Yii::app()->params['systemNode'], $TerminalID, $MID, $CardNumber, $transMsg, null, null);
                                                                                                            $appLogger->log($appLogger->logdate, "[response]", $transMsg);
                                                                                                            $data = CommonController::retMsgTransferWallet($transMsg, $errCode);
                                                                                                            $this->_sendResponse(200, $data);
                                                                                                            exit;
                                                                                                        } else {
                                                                                                            $errCode = 26;
                                                                                                            $transMsg = '[LP #26] Failed to update transaction transfer details';

                                                                                                            $LPErrorLogsModel->insertLPlogs(Yii::app()->params['systemNode'], $TerminalID, $MID, $CardNumber, $transMsg, null, null);
                                                                                                            $appLogger->log($appLogger->logdate, "[response]", $transMsg);
                                                                                                            $data = CommonController::retMsgTransferWallet($transMsg, $errCode);
                                                                                                            $this->_sendResponse(200, $data);
                                                                                                            exit;
                                                                                                        }
                                                                                                    }
                                                                                                } else {

                                                                                                    $TransferStatus = 93;

                                                                                                    //update mztransactiontransfer 
                                                                                                    $updateMzTransactionTransfer = $mzTransactionTransferModel->updateStatusMzTransactionTransfer($TransferStatus, $TransferID, $TransactionSummaryID);


                                                                                                    if ($updateMzTransactionTransfer) {
                                                                                                        $errCode = 25;
                                                                                                        $transMsg = '[LP #25] Unable to perform re-deposit transaction. Balance of a casino is not zero.';

                                                                                                        $LPErrorLogsModel->insertLPlogs(Yii::app()->params['systemNode'], $TerminalID, $MID, $CardNumber, $transMsg, null, null);
                                                                                                        $appLogger->log($appLogger->logdate, "[response]", $transMsg);
                                                                                                        $data = CommonController::retMsgTransferWallet($transMsg, $errCode);
                                                                                                        $this->_sendResponse(200, $data);
                                                                                                        exit;
                                                                                                    } else {
                                                                                                        $errCode = 24;
                                                                                                        $transMsg = '[LP #24] Failed to update transaction transfer details';

                                                                                                        $LPErrorLogsModel->insertLPlogs(Yii::app()->params['systemNode'], $TerminalID, $MID, $CardNumber, $transMsg, null, null);
                                                                                                        $appLogger->log($appLogger->logdate, "[response]", $transMsg);
                                                                                                        $data = CommonController::retMsgTransferWallet($transMsg, $errCode);
                                                                                                        $this->_sendResponse(200, $data);
                                                                                                        exit;
                                                                                                    }
                                                                                                }
                                                                                            } else {
                                                                                                $errCode = 23;
                                                                                                $transMsg = '[LP #23] Failed to get balance from current provider';
                                                                                                $LPErrorLogsModel->insertLPlogs(Yii::app()->params['systemNode'], $TerminalID, $MID, $CardNumber, $transMsg, null, null);
                                                                                                $appLogger->log($appLogger->logdate, "[response]", $transMsg);
                                                                                                $data = CommonController::retMsgTransferWallet($transMsg, $errCode);
                                                                                                $this->_sendResponse(200, $data);
                                                                                                exit;
                                                                                            }
                                                                                        } else {
                                                                                            $errCode = 22;
                                                                                            $transMsg = '[LP #22] Failed to insert transaction transfer details';

                                                                                            $LPErrorLogsModel->insertLPlogs(Yii::app()->params['systemNode'], $TerminalID, $MID, $CardNumber, $transMsg, null, null);
                                                                                            $appLogger->log($appLogger->logdate, "[response]", $transMsg);
                                                                                            $data = CommonController::retMsgTransferWallet($transMsg, $errCode);
                                                                                            $this->_sendResponse(200, $data);
                                                                                            exit;
                                                                                        }
                                                                                    } else {
                                                                                        $errCode = 21;
                                                                                        $transMsg = '[LP #21] Failed to update transaction transfer details';

                                                                                        $LPErrorLogsModel->insertLPlogs(Yii::app()->params['systemNode'], $TerminalID, $MID, $CardNumber, $transMsg, null, null);
                                                                                        $appLogger->log($appLogger->logdate, "[response]", $transMsg);
                                                                                        $data = CommonController::retMsgTransferWallet($transMsg, $errCode);
                                                                                        $this->_sendResponse(200, $data);
                                                                                        exit;
                                                                                    }
                                                                                }
                                                                            } else {
                                                                                $errCode = 19;
                                                                                $transMsg = '[LP #19] Failed to update transaction transfer details';

                                                                                $LPErrorLogsModel->insertLPlogs(Yii::app()->params['systemNode'], $TerminalID, $MID, $CardNumber, $transMsg, null, null);
                                                                                $appLogger->log($appLogger->logdate, "[response]", $transMsg);
                                                                                $data = CommonController::retMsgTransferWallet($transMsg, $errCode);
                                                                                $this->_sendResponse(200, $data);
                                                                                exit;
                                                                            }
                                                                        } else {
                                                                            $TransferStatus = 90;

                                                                            //update mztransactiontransfer 
                                                                            $updateMzTransactionTransfer = $mzTransactionTransferModel->updateStatusMzTransactionTransfer($TransferStatus, $TransferID, $TransactionSummaryID);


                                                                            if ($updateMzTransactionTransfer) {
                                                                                $errCode = 18;
                                                                                $transMsg = '[LP #18] Previous provider balance is not zero';

                                                                                $LPErrorLogsModel->insertLPlogs(Yii::app()->params['systemNode'], $TerminalID, $MID, $CardNumber, $transMsg, null, null);
                                                                                $appLogger->log($appLogger->logdate, "[response]", $transMsg);
                                                                                $data = CommonController::retMsgTransferWallet($transMsg, $errCode);
                                                                                $this->_sendResponse(200, $data);
                                                                                exit;
                                                                            } else {
                                                                                $errCode = 17;
                                                                                $transMsg = '[LP #17] Failed to update transaction transfer details';

                                                                                $LPErrorLogsModel->insertLPlogs(Yii::app()->params['systemNode'], $TerminalID, $MID, $CardNumber, $transMsg, null, null);
                                                                                $appLogger->log($appLogger->logdate, "[response]", $transMsg);
                                                                                $data = CommonController::retMsgTransferWallet($transMsg, $errCode);
                                                                                $this->_sendResponse(200, $data);
                                                                                exit;
                                                                            }
                                                                        }
                                                                    } else {
                                                                        $errCode = 16;
                                                                        $transMsg = '[LP #16] Failed to get balance from current provider';

                                                                        $LPErrorLogsModel->insertLPlogs(Yii::app()->params['systemNode'], $TerminalID, $MID, $CardNumber, $transMsg, null, null);
                                                                        $appLogger->log($appLogger->logdate, "[response]", $transMsg);
                                                                        $data = CommonController::retMsgTransferWallet($transMsg, $errCode);
                                                                        $this->_sendResponse(200, $data);
                                                                        exit;
                                                                    }
                                                                } else {
                                                                    $errCode = 15;
                                                                    $transMsg = '[LP #15] Failed to update transaction transfer details';

                                                                    $LPErrorLogsModel->insertLPlogs(Yii::app()->params['systemNode'], $TerminalID, $MID, $CardNumber, $transMsg, null, null);
                                                                    $appLogger->log($appLogger->logdate, "[response]", $transMsg);
                                                                    $data = CommonController::retMsgTransferWallet($transMsg, $errCode);
                                                                    $this->_sendResponse(200, $data);
                                                                    exit;
                                                                }
                                                            } else {
                                                                $errCode = 14;
                                                                $transMsg = '[LP #14] Failed to get transaction transfer details';

                                                                $LPErrorLogsModel->insertLPlogs(Yii::app()->params['systemNode'], $TerminalID, $MID, $CardNumber, $transMsg, null, null);
                                                                $appLogger->log($appLogger->logdate, "[response]", $transMsg);
                                                                $data = CommonController::retMsgTransferWallet($transMsg, $errCode);
                                                                $this->_sendResponse(200, $data);
                                                                exit;
                                                            }
                                                        } else {
                                                            $FromServiceTransID = $WithdrawToCurrentProvider['TransactionReferenceID'];
                                                            $FromServiceStatus = $WithdrawToCurrentProvider['Status'];
                                                            $FromStatus = 2;
                                                            $TransferStatus = 2;

                                                            //update mztransactiontransfer
                                                            $updateMzTransactionTransfer = $mzTransactionTransferModel->updateFromMzTransactionTransfer($FromServiceTransID, $FromServiceStatus, $FromStatus, $TransferStatus, $MzTransactionTransferID, $TransactionSummaryID);


                                                            if ($updateMzTransactionTransfer) {

                                                                $ActiveServiceStatus = 1;

                                                                //update terminalsessions
                                                                $updateTerminalSession = $terminalSessionsModel->updateActiveServiceStatus($TerminalID, $CardNumber, $ActiveServiceStatus);

                                                                if ($updateTerminalSession) {
                                                                    //FAILED
                                                                    $errCode = 13;
                                                                    $transMsg = '[LP #13] Failed to withdraw from current provider';

                                                                    $appLogger->log($appLogger->logdate, "[response]", $transMsg);
                                                                    $data = CommonController::retMsgTransferWallet($transMsg, $errCode);
                                                                    $this->_sendResponse(200, $data);
                                                                    exit;
                                                                } else {
                                                                    $errCode = 12;
                                                                    $transMsg = '[LP #12] Failed to update session details';

                                                                    $LPErrorLogsModel->insertLPlogs(Yii::app()->params['systemNode'], $TerminalID, $MID, $CardNumber, $transMsg, null, null);
                                                                    $appLogger->log($appLogger->logdate, "[response]", $transMsg);
                                                                    $data = CommonController::retMsgTransferWallet($transMsg, $errCode);
                                                                    $this->_sendResponse(200, $data);
                                                                    exit;
                                                                }
                                                            } else {
                                                                $errCode = 11;
                                                                $transMsg = '[LP #11] Failed to update transaction transfer details';

                                                                $LPErrorLogsModel->insertLPlogs(Yii::app()->params['systemNode'], $TerminalID, $MID, $CardNumber, $transMsg, null, null);
                                                                $appLogger->log($appLogger->logdate, "[response]", $transMsg);
                                                                $data = CommonController::retMsgTransferWallet($transMsg, $errCode);
                                                                $this->_sendResponse(200, $data);
                                                                exit;
                                                            }
                                                        }
                                                    } else {
                                                        $errCode = 10;
                                                        $transMsg = '[LP #10] Failed to insert transaction transfer details';

                                                        $LPErrorLogsModel->insertLPlogs(Yii::app()->params['systemNode'], $TerminalID, $MID, $CardNumber, $transMsg, null, null);
                                                        $appLogger->log($appLogger->logdate, "[response]", $transMsg);
                                                        $data = CommonController::retMsgTransferWallet($transMsg, $errCode);
                                                        $this->_sendResponse(200, $data);
                                                        exit;
                                                    }
                                                }
                                            } else {
                                                $errCode = '09';
                                                $transMsg = '[LP #09] Failed to update transaction transfer details';

                                                $LPErrorLogsModel->insertLPlogs(Yii::app()->params['systemNode'], $TerminalID, $MID, $CardNumber, $transMsg, null, null);
                                                $appLogger->log($appLogger->logdate, "[response]", $transMsg);
                                                $data = CommonController::retMsgTransferWallet($transMsg, $errCode);
                                                $this->_sendResponse(200, $data);
                                                exit;
                                            }
                                        } else {
                                            $errCode = '08';
                                            $transMsg = '[LP #08.2] Transferring between casinos is currently unavailable. Please try again later';

                                            $LPErrorLogsModel->insertLPlogs(Yii::app()->params['systemNode'], $TerminalID, $MID, $CardNumber, $transMsg, null, null);
                                            $appLogger->log($appLogger->logdate, "[response]", $transMsg);
                                            $data = CommonController::retMsgTransferWallet($transMsg, $errCode);
                                            $this->_sendResponse(200, $data);
                                            exit;
                                        }
                                    } else {
                                        $errCode = '07';
                                        $transMsg = '[LP #07] Failed to get balance from current provider';

                                        $LPErrorLogsModel->insertLPlogs(Yii::app()->params['systemNode'], $TerminalID, $MID, $CardNumber, $transMsg, null, null);
                                        $appLogger->log($appLogger->logdate, "[response]", $transMsg);
                                        $data = CommonController::retMsgTransferWallet($transMsg, $errCode);
                                        $this->_sendResponse(200, $data);
                                        exit;
                                    }
                                } else {
                                    $errCode = '06';
                                    $transMsg = '[LP #06] Failed to get service group name';

                                    $LPErrorLogsModel->insertLPlogs(Yii::app()->params['systemNode'], $TerminalID, $MID, $CardNumber, $transMsg, null, null);
                                    $appLogger->log($appLogger->logdate, "[response]", $transMsg);
                                    $data = CommonController::retMsgTransferWallet($transMsg, $errCode);
                                    $this->_sendResponse(200, $data);
                                    exit;
                                }
                            } else {
                                $errCode = '05';
                                $transMsg = '[LP #05] New casino balance is not equal to zero';

                                $LPErrorLogsModel->insertLPlogs(Yii::app()->params['systemNode'], $TerminalID, $MID, $CardNumber, $transMsg, null, null);
                                $appLogger->log($appLogger->logdate, "[response]", $transMsg);
                                $data = CommonController::retMsgTransferWallet($transMsg, $errCode);
                                $this->_sendResponse(200, $data);
                                exit;
                            }
                        } else {
                            $errCode = '04';
                            $transMsg = '[LP #04] Failed to get balance from new provider';

                            $LPErrorLogsModel->insertLPlogs(Yii::app()->params['systemNode'], $TerminalID, $MID, $CardNumber, $transMsg, null, null);
                            $appLogger->log($appLogger->logdate, "[response]", $transMsg);
                            $data = CommonController::retMsgTransferWallet($transMsg, $errCode);
                            $this->_sendResponse(200, $data);
                            exit;
                        }
                    } else {
                        $errCode = '03';
                        $transMsg = '[LP #03] Failed to get session details';

                        $LPErrorLogsModel->insertLPlogs(Yii::app()->params['systemNode'], null, null, null, $transMsg, null, null);
                        $appLogger->log($appLogger->logdate, "[response]", $transMsg);
                        $data = CommonController::retMsgTransferWallet($transMsg, $errCode);
                        $this->_sendResponse(200, $data);
                        exit;
                    }
                } else {

                    //check terminal sessions activeservicestatus
                    $checkActiveServiceStatus = $terminalSessionsModel->checkActiveServiceStatus($TerminalCode);
                    $ActiveServiceStatus = $checkActiveServiceStatus['ActiveServiceStatus'];

                    if ($ActiveServiceStatus == 9) {
                        $errCode = 41;
                        $transMsg = '[LP #41.1] There is already a pending Launchpad transfer transaction for this account. Please contact customer service.';

                        $LPErrorLogsModel->insertLPlogs(Yii::app()->params['systemNode'], null, null, null, $transMsg, null, null);
                        $appLogger->log($appLogger->logdate, "[response]", $transMsg);
                        $data = CommonController::retMsgTransferWallet($transMsg, $errCode);
                        $this->_sendResponse(200, $data);
                        exit;
                    } elseif ($ActiveServiceStatus <> 1) {
                        $errCode = '08';
                        $transMsg = '[LP #08.1] Transferring between casinos is currently unavailable. Please try again later';

                        $LPErrorLogsModel->insertLPlogs(Yii::app()->params['systemNode'], null, null, null, $transMsg, null, null);
                        $appLogger->log($appLogger->logdate, "[response]", $transMsg);
                        $data = CommonController::retMsgTransferWallet($transMsg, $errCode);
                        $this->_sendResponse(200, $data);
                        exit;
                    } else {
                        //NO TRANSFER RETURN OKAY
                        $errCode = 0;
                        $transMsg = 'Successful No Wallet Transfer';

                        $appLogger->log($appLogger->logdate, "[response]", $transMsg);
                        $data = CommonController::retMsgTransferWallet($transMsg, $errCode);
                        $this->_sendResponse(200, $data);
                        exit;
                    }
                }
            } else {
                //NO SESSION
                $errCode = '02';
                $transMsg = '[LP #02] The player has no existing session.';

                $LPErrorLogsModel->insertLPlogs(Yii::app()->params['systemNode'], null, null, null, $transMsg, null, null);
                $appLogger->log($appLogger->logdate, "[response]", $transMsg);
                $data = CommonController::retMsgTransferWallet($transMsg, $errCode);
                $this->_sendResponse(200, $data);
                exit;
            }
        } else {
            $errCode = '01';
            if (empty($NewUsermode)) {
                $transMsg = '[LP #01] Usermode must not be blank';
            }
            if (empty($NewServiceID)) {
                $transMsg = '[LP #01] ServiceID must not be blank';
            }
            if (empty($TerminalCode)) {
                $transMsg = '[LP #01] TerminalCode must not be blank';
            }

            $LPErrorLogsModel->insertLPlogs(Yii::app()->params['systemNode'], null, null, null, $transMsg, null, null);
            $appLogger->log($appLogger->logdate, "[response]", $transMsg);
            $data = CommonController::retMsgTransferWallet($transMsg, $errCode);
            $this->_sendResponse(200, $data);
            exit;
        }
        $message = "[TransferWallet] Output: " . $data;
        CLoggerModified::log($message, CLoggerModified::RESPONSE);
    }

    private function _getBalance($ServiceID, $UBServiceLogin, $UBServicePassword, $ServiceGroup, $MID, $CardNumber, $TerminalID) {

        $appLogger = new AppLogger();
        $LPErrorLogsModel = new LpErrorLogsModel();
        Yii::import('application.components.CasinoController');

        if ($ServiceGroup == "RTG" || $ServiceGroup == "RTG2") {
            try {
                $RTGApiWrapper = new RealtimeGamingAPIWrapper();

                $GetBalance = $RTGApiWrapper->GetBalance($ServiceID, $UBServiceLogin);

                $requestBody = array("ServiceID" => $ServiceID, "UBServiceLogin" => $UBServiceLogin);
                $request = json_encode($requestBody);
                $response = json_encode($GetBalance);

                if (!empty($GetBalance) && $GetBalance <> 'Cant connect to casino') {
                    $Balance = $GetBalance['balance'];
                } else {
                    $errCode = 50;
                    $transMsg = '[LP #50] Can\'t get balance RTG.';

                    $LPErrorLogsModel->insertLPlogs(Yii::app()->params['systemNode'], $TerminalID, $MID, $CardNumber, $transMsg, $request, $response);
                    $appLogger->log($appLogger->logdate, "[response]", $transMsg);
                    $data = CommonController::retMsgTransferWallet($transMsg, $errCode);
                    $this->_sendResponse(200, $data);
                    exit;
                }

                return $Balance;
            } catch (Exception $ex) {
                $errCode = 50;
                $transMsg = '[LP #50] Can\'t get balance RTG.';

                $LPErrorLogsModel->insertLPlogs(Yii::app()->params['systemNode'], $TerminalID, $MID, $CardNumber, $transMsg, $request, $response);
                $appLogger->log($appLogger->logdate, "[response]", $transMsg);
                $data = CommonController::retMsgTransferWallet($transMsg, $errCode);
                $this->_sendResponse(200, $data);
                exit;
            }
        }

        if ($ServiceGroup == "HAB") {
            try {
                $HabaneroApiWrapper = new HabaneroAPIWrapper(Yii::app()->params->cashierapi[$ServiceID - 1], Yii::app()->params['HB_APIkey'], Yii::app()->params['HB_BrandID']);
                $GetBalance = $HabaneroApiWrapper->GetBalance($UBServiceLogin, $UBServicePassword);

                $requestBody = array("ServiceID" => $ServiceID, "UBServiceLogin" => $UBServiceLogin, "UBServicePassword" => $UBServicePassword);
                $request = json_encode($requestBody);
                $response = json_encode($GetBalance);


                if (!empty($GetBalance)) {
                    if ($GetBalance['IsSucceed'] == false) {
                        $errCode = 51;
                        $transMsg = '[LP #51] Can\'t get balance Habanero.';

                        $LPErrorLogsModel->insertLPlogs(Yii::app()->params['systemNode'], $TerminalID, $MID, $CardNumber, $transMsg, $request, $response);

                        $appLogger->log($appLogger->logdate, "[response]", $transMsg);
                        $data = CommonController::retMsgTransferWallet($transMsg, $errCode);
                        $this->_sendResponse(200, $data);
                        exit;
                    } else {
                        $Balance = $GetBalance['TransactionInfo']['RealBalance'];
                    }
                } else {
                    $errCode = 51;
                    $transMsg = '[LP #51] Can\'t get balance Habanero.';

                    $LPErrorLogsModel->insertLPlogs(Yii::app()->params['systemNode'], $TerminalID, $MID, $CardNumber, $transMsg, $request, $response);
                    $appLogger->log($appLogger->logdate, "[response]", $transMsg);
                    $data = CommonController::retMsgTransferWallet($transMsg, $errCode);
                    $this->_sendResponse(200, $data);
                    exit;
                }

                return $Balance;
            } catch (Exception $ex) {
                $errCode = 51;
                $transMsg = '[LP #051] Can\'t get balance Habanero.';

                $LPErrorLogsModel->insertLPlogs(Yii::app()->params['systemNode'], $TerminalID, $MID, $CardNumber, $transMsg, $request, $response);
                $appLogger->log($appLogger->logdate, "[response]", $transMsg);
                $data = CommonController::retMsgTransferWallet($transMsg, $errCode);
                $this->_sendResponse(200, $data);
                exit;
            }
        }
    }

    private function _deposit($MzTransactionTransferID, $ActiveServiceID, $UBServiceLogin, $UBServicePassword, $CurrentProviderBalance, $TerminalID, $TerminalCode, $ServiceGroup, $transtype, $MID, $CardNumber) {

        $appLogger = new AppLogger();
        $LPErrorLogsModel = new LpErrorLogsModel();
        Yii::import('application.components.CasinoController');
        $RTGApiWrapper = new RealtimeGamingAPIWrapper();
        $HabaneroApiWrapper = new HabaneroAPIWrapper(Yii::app()->params->cashierapi[$ActiveServiceID - 1], Yii::app()->params['HB_APIkey'], Yii::app()->params['HB_BrandID']);

        $tracking1 = $MzTransactionTransferID . $transtype;
        $tracking2 = $transtype;
        $tracking3 = $TerminalID;
        $tracking4 = str_replace("ICSA-", "", str_replace("VIP", "", $TerminalCode));

        if ($ServiceGroup == 'RTG' || $ServiceGroup == 'RTG2') {
            $DepositToNewProvider = $RTGApiWrapper->Deposit($ActiveServiceID, $UBServiceLogin, $UBServicePassword, 1, $CurrentProviderBalance, $tracking1, $tracking2, $tracking3, $tracking4, null);

            $requestBody = array("ActiveServiceID" => $ActiveServiceID, "UBServiceLogin" => $UBServiceLogin, "UBServicePassword" => $UBServicePassword, "CasinoID" => 1, "CurrentProviderBalance" => $CurrentProviderBalance, 'Tracking1' => $tracking1, 'Tracking2' => $tracking2, 'Tracking3' => $tracking3, 'Tracking4' => $tracking4, "LocatorName" => null);
            $request = json_encode($requestBody);
            $response = json_encode($DepositToNewProvider);
        }

        if ($ServiceGroup == 'HAB') {
            $DepositToNewProvider = $HabaneroApiWrapper->Deposit($UBServiceLogin, $UBServicePassword, $CurrentProviderBalance, $tracking1);

            $requestBody = array("ActiveServiceID" => $ActiveServiceID, "UBServiceLogin" => $UBServiceLogin, "UBServicePassword" => $UBServicePassword, "CurrentProviderBalance" => $CurrentProviderBalance, 'Tracking1' => $tracking1);
            $request = json_encode($requestBody);
            $response = json_encode($DepositToNewProvider);
        }

        if (is_null($DepositToNewProvider)) {

            if ($ServiceGroup == 'RTG' || $ServiceGroup == 'RTG2') {
                $transSearchInfo = $RTGApiWrapper->TransactionSerachInfo($ActiveServiceID, $UBServiceLogin, $tracking1, $tracking2, $tracking3, $tracking4);
            }

            if ($ServiceGroup == 'HAB') {
                $transSearchInfo = $HabaneroApiWrapper->TransactionSearchInfo($tracking1);
            }


            if (isset($transSearchInfo['TransactionInfo'])) {
                //RTG / Magic Macau
                if (isset($transSearchInfo['TransactionInfo']['TrackingInfoTransactionSearchResult'])) {
                    $amount = abs($transSearchInfo['TransactionInfo']['TrackingInfoTransactionSearchResult']['amount']);
                    $apiresult = $transSearchInfo['TransactionInfo']['TrackingInfoTransactionSearchResult']['transactionStatus'];
                    $transrefid = $transSearchInfo['TransactionInfo']['TrackingInfoTransactionSearchResult']['transactionID'];
                }

                //Habanero
                if (isset($transSearchInfo['TransactionInfo']['querytransmethodResult'])) {
                    $amount = abs($transSearchInfo['TransactionInfo']['querytransmethodResult']['Amount']);
                    $transrefid = $transSearchInfo['TransactionInfo']['querytransmethodResult']['TransactionId'];
                    $apiresult = $transSearchInfo['TransactionInfo']['querytransmethodResult']['Success'];
                }
            }
        } else {
            if (isset($DepositToNewProvider['TransactionInfo'])) {
                //RTG / Magic Macau
                if (isset($DepositToNewProvider['TransactionInfo']['DepositGenericResult'])) {
                    $transrefid = $DepositToNewProvider['TransactionInfo']['DepositGenericResult']['transactionID'];
                    $apiresult = $DepositToNewProvider['TransactionInfo']['DepositGenericResult']['transactionStatus'];
                    $amount = abs($DepositToNewProvider['TransactionInfo']['DepositGenericResult']['amount']);
                }

                //Habanero
                if (isset($DepositToNewProvider['TransactionInfo']['depositmethodResult'])) {
                    $amount = abs($DepositToNewProvider['TransactionInfo']['depositmethodResult']['Amount']); //returns 0 value
                    $transrefid = $DepositToNewProvider['TransactionInfo']['depositmethodResult']['TransactionId'];
                    $apiresult = $DepositToNewProvider['TransactionInfo']['depositmethodResult']['Message'];
                }
            }
        }
        if (empty($apiresult) || $apiresult == 'Cant connect to casino') {
            $errCode = 52;
            $transMsg = '[LP #52] Error in Deposit';

            $LPErrorLogsModel->insertLPlogs(Yii::app()->params['systemNode'], $TerminalID, $MID, $CardNumber, $transMsg, $request, $response);
            $appLogger->log($appLogger->logdate, "[response]", $transMsg);
            $data = CommonController::retMsgTransferWallet($transMsg, $errCode);
            $this->_sendResponse(200, $data);
            exit;
        } else {

            if ($apiresult == 'TRANSACTIONSTATUS_APPROVED' || $apiresult == 'true' || $apiresult == 'approved' || $apiresult == "Deposit Success") {
                $array['DepositedAmount'] = $amount;
                $array['TransactionReferenceID'] = $transrefid;
                $array['Status'] = $apiresult;
                $array['IsSuccess'] = true;

                $result = $array;
            } else {
                $array['DepositedAmount'] = $amount;
                $array['TransactionReferenceID'] = $transrefid;
                $array['Status'] = $apiresult;
                $array['IsSuccess'] = false;

                $LPErrorLogsModel->insertLPlogs(Yii::app()->params['systemNode'], $TerminalID, $MID, $CardNumber, $transMsg, $request, $response);

                $result = $array;
            }
        }
        return $result;
    }

    private function _withdraw($MzTransactionTransferID, $ActiveServiceID, $UBServiceLogin, $UBServicePassword, $CurrentProviderBalance, $TerminalID, $TerminalCode, $ServiceGroup, $transtype, $MID, $CardNumber) {

        $appLogger = new AppLogger();
        $LPErrorLogsModel = new LpErrorLogsModel();
        Yii::import('application.components.CasinoController');
        $RTGApiWrapper = new RealtimeGamingAPIWrapper();
        $HabaneroApiWrapper = new HabaneroAPIWrapper(Yii::app()->params->cashierapi[$ActiveServiceID - 1], Yii::app()->params['HB_APIkey'], Yii::app()->params['HB_BrandID']);

        $tracking1 = $MzTransactionTransferID . $transtype;
        $tracking2 = $transtype;
        $tracking3 = $TerminalID;
        $tracking4 = str_replace("ICSA-", "", str_replace("VIP", "", $TerminalCode));

        if ($ServiceGroup == 'RTG' || $ServiceGroup == 'RTG2') {
            $WithdrawToCurrentProvider = $RTGApiWrapper->Withdraw($ActiveServiceID, $UBServiceLogin, $UBServicePassword, 1, $CurrentProviderBalance, $tracking1, $tracking2, $tracking3, $tracking4);

            $requestBody = array("ActiveServiceID" => $ActiveServiceID, "UBServiceLogin" => $UBServiceLogin, "UBServicePassword" => $UBServicePassword, "CurrentProviderBalance" => $CurrentProviderBalance, 'Tracking1' => $tracking1, 'Tracking2' => $tracking2, 'Tracking3' => $tracking3, 'Tracking4' => $tracking4);
            $request = json_encode($requestBody);
            $response = json_encode($WithdrawToCurrentProvider);
        }

        if ($ServiceGroup == 'HAB') {
            $WithdrawToCurrentProvider = $HabaneroApiWrapper->Withdraw($UBServiceLogin, $UBServicePassword, $CurrentProviderBalance, $tracking1);

            $requestBody = array("ActiveServiceID" => $ActiveServiceID, "UBServiceLogin" => $UBServiceLogin, "UBServicePassword" => $UBServicePassword, "CurrentProviderBalance" => $CurrentProviderBalance, 'Tracking1' => $tracking1);
            $request = json_encode($requestBody);
            $response = json_encode($WithdrawToCurrentProvider);
        }

        if (is_null($WithdrawToCurrentProvider)) {

            if ($ServiceGroup == 'RTG' || $ServiceGroup == 'RTG2') {
                $transSearchInfo = $RTGApiWrapper->TransactionSerachInfo($ActiveServiceID, $UBServiceLogin, $tracking1, 1, $tracking2, $tracking3, $tracking4);
            }

            if ($ServiceGroup == 'HAB') {
                $transSearchInfo = $HabaneroApiWrapper->TransactionSearchInfo($tracking1);
            }

            if (isset($transSearchInfo['TransactionInfo'])) {
                //RTG / Magic Macau
                if (isset($transSearchInfo['TransactionInfo']['TrackingInfoTransactionSearchResult'])) {
                    $amount = abs($transSearchInfo['TransactionInfo']['TrackingInfoTransactionSearchResult']['amount']);
                    $apiresult = $transSearchInfo['TransactionInfo']['TrackingInfoTransactionSearchResult']['transactionStatus'];
                    $transrefid = $transSearchInfo['TransactionInfo']['TrackingInfoTransactionSearchResult']['transactionID'];
                }
                //Habanero
                if (isset($transSearchInfo['TransactionInfo']['querytransmethodResult'])) {
                    $amount = abs($transSearchInfo['TransactionInfo']['querytransmethodResult']['Amount']); //returns 0 value
                    $transrefid = $transSearchInfo['TransactionInfo']['querytransmethodResult']['TransactionId'];
                    $apiresult = $transSearchInfo['TransactionInfo']['querytransmethodResult']['Success'];
                }
            }
        } else {
            //check Withdraw API Result
            if (isset($WithdrawToCurrentProvider['TransactionInfo'])) {
                //RTG / Magic Macau
                if (isset($WithdrawToCurrentProvider['TransactionInfo']['WithdrawGenericResult'])) {
                    $amount = abs($WithdrawToCurrentProvider['TransactionInfo']['WithdrawGenericResult']['amount']);
                    $transrefid = $WithdrawToCurrentProvider['TransactionInfo']['WithdrawGenericResult']['transactionID'];
                    $apiresult = $WithdrawToCurrentProvider['TransactionInfo']['WithdrawGenericResult']['transactionStatus'];
                }

                //Habanero
                if (isset($WithdrawToCurrentProvider['TransactionInfo']['withdrawmethodResult'])) {
                    $amount = abs($WithdrawToCurrentProvider['TransactionInfo']['withdrawmethodResult']['Amount']);
                    $transrefid = $WithdrawToCurrentProvider['TransactionInfo']['withdrawmethodResult']['TransactionId'];
                    $apiresult = $WithdrawToCurrentProvider['TransactionInfo']['withdrawmethodResult']['Message'];
                }
            }
        }
        if (empty($apiresult) || $apiresult == 'Cant connect to casino') {
            $errCode = 53;
            $transMsg = '[LP #53] Error in Withdrawal';

            $LPErrorLogsModel->insertLPlogs(Yii::app()->params['systemNode'], $TerminalID, $MID, $CardNumber, $transMsg, $request, $response);
            $appLogger->log($appLogger->logdate, "[response]", $transMsg);
            $data = CommonController::retMsgTransferWallet($transMsg, $errCode);
            $this->_sendResponse(200, $data);
            exit;
        } else {
            if ($apiresult == 'TRANSACTIONSTATUS_APPROVED' || $apiresult == 'true' || $apiresult == 'approved' || $apiresult == "Withdrawal Success") {
                $array['WithdrawnAmount'] = $amount;
                $array['TransactionReferenceID'] = $transrefid;
                $array['Status'] = $apiresult;
                $array['IsSuccess'] = true;

                $result = $array;
            } else {
                $array['WithdrawnAmount'] = $amount;
                $array['TransactionReferenceID'] = $transrefid;
                $array['Status'] = $apiresult;
                $array['IsSuccess'] = false;

                $LPErrorLogsModel->insertLPlogs(Yii::app()->params['systemNode'], $TerminalID, $MID, $CardNumber, $transMsg, $request, $response);

                $result = $array;
            }
        }
        return $result;
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

//This function invokes necessary method in displaying error messages based on '$errorMessage' php variable declared in this class.
    private function _displayReturnMessage($errorCode, $module, $logErrorMessage, $randchars, $ApiLogsModel = '', $RewardID = '') {
        $appLogger = new AppLogger();
        $transMsg = $logErrorMessage;

        $errCode = floor($errorCode);
        $data = CommonController::retMsg($module, $errCode, $transMsg);
        $message = "[" . $module . "] " . $randchars . " Output: " . CJSON::encode($data);
        $appLogger->log($appLogger->logdate, "[response]", $message);
        $this->_sendResponse(200, $data);
        Utilities::log("ReturnMessage: " . $transMsg . " ErrorCode: " . $errorCode);
    }

//validation functions

    private function validateRequiredFields($request, $module, $fields, $randchars) {
        $validateSuccess = false;
        $fieldValue = "";
        foreach ($fields as $key => $value) {
            if (isset($request[$key]) && $request[$key] != null) {
                $fields[$key] = true;
            } else {
                $fieldValue = $fieldValue . "[" . $key . "] ";
            }
        }
        if ($fieldValue != "") {
            $ErrorCode = 75;
            $this->_displayReturnMessage($ErrorCode, $module, 'One or more fields is not set or is blank. ' . $fieldValue, $randchars);
            $ErrorCode = 1;
            return false;
        }
        $validateSuccess = $this->validateAllFields($fields);
        return $validateSuccess;
    }

    private function validateAllFields($fields) {
        $validateSuccess = false;
        foreach ($fields as $value) {
            if ($value == true) {
                $validateSuccess = true;
                return true;
            } else {
                $validateSuccess = false;
                return false;
            }
        }
        return $validateSuccess;
    }

    private function random_string() {
        $character_set_array = array();
        $character_set_array[] = array('count' => 4, 'characters' => 'ABCDEFGHIJKLMNOPQRSTUVWXYZ');
        $character_set_array[] = array('count' => 4, 'characters' => '0123456789');
        $temp_array = array();
        foreach ($character_set_array as $character_set) {
            for ($i = 0; $i < $character_set['count']; $i++) {
                $temp_array[] = $character_set['characters'][rand(0, strlen($character_set['characters']) - 1)];
            }
        }
        return implode('', $temp_array);
    }

    private function validateReferrer($SERVER) {
        if (isset($SERVER)) {
            if (Yii::app()->params['Referrer'] == $SERVER) {
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

}
