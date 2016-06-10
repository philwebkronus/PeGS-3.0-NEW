<?php
/**
 * Description of WsKapiController
 *
 * @author elperez
 */
class WsKapiController extends Controller {

    public $acc_id = 282;
    public $status = 0;
 
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
    public function actionGetsitebalance()
    {
        $request    = $this->_readJsonRequest();
        $message    = "";
        $errorcode  = "";
        $BCF        = "";
        $APIName    = "Get Site Balance";

        $sitecode = trim($request['SiteCode']);
        if (isset($sitecode))
        {
            //instantiate models
            $sites          = new SitesModel();
            $sitebalance    = new SiteBalanceModel();
            //check if sitecode is blank
            if ($sitecode != "")
            {
                //add icsa
                $sitecode = "ICSA-".$sitecode;
                //check if site code do exist
                $isExist = $sites->isSiteExist($sitecode);
                if ($isExist)
                {
                    $siteID = $sites->getSiteIDBySiteCode($sitecode);
                    //get site balance
                    $getbalance = $sitebalance->getSiteBalance($siteID);
                    $BCF = $getbalance['Balance'];
                    $errorcode = 0;
                    $message = "Transaction Successful.";
                }
                else
                {
                    $errorcode  = 69;
                    $message    = "Site code doesn't exist.";
                }
            }
            else
            {
                $errorcode  = 68;
                $message    = "Site code is not set or blank.";
            }
        }
        else
        {
            $errorcode  = 68;
            $message    = "Site code is not set or blank.";
        }
        $this->_sendResponse(200, CommonController::getSiteBalanceResponse($BCF, $message, $errorcode));
    }
    /*
     *  @description Get current (casino)balance of player in wallet for MSW
     *  @author ralph sison
     *  @dateadded 12-22-2015
     */
    public function actionGetbalancemsw()
    {
        //import APIs that will be used
        Yii::import('application.components.CasinoController');

        //instantiate classes that will be used
        $casinoController = new CasinoController();
        $refServices = new RefServicesModel();
        $members = new MembersModel();
        $memberServices = new MemberServicesModel();

        $request = $this->_readJsonRequest();

        $mid = htmlentities($request['MID']);
        $serviceID = htmlentities($request['ServiceID']);

        //check if all required fields are filled
        if(isset($mid) && $mid != '' && isset($serviceID) && $serviceID != '')
        {
            //check if entered fields are of valid data type
            if(is_numeric($mid) && is_numeric($serviceID))
            {
                //check if service id entered is valid (found in npos.ref_services table)
                $serviceInfo = $refServices->getServiceInfo($serviceID);
                if($serviceInfo != '')
                {
                    //check if mid is valid (existing in membership.members table)
                    $isMIDExisting = $members->checkMIDIfExisting($mid);
                    if($isMIDExisting != '')
                    {
                        //check if player is active
                        $isActive = $members->checkIfActive($mid);
                        if($isActive != '')
                        {
                            //check if player exists in the specified casino
                            $msResult = $memberServices->getDetailsByMIDAndCasinoID($mid, $serviceID);
                            if($msResult)
                            {
                                $serviceUsername = $msResult['ServiceUsername'];
                                // Call GetBalance to get the amount in Casino
                                $getBalance = $casinoController->GetBalance($serviceID, $serviceUsername);
                                if(is_array($getBalance))
                                {
                                    $balance = $getBalance['balance'];
                                    $balance = Utilities::toMoney($balance);
                                    $message = "Transaction Successful";
                                    $this->_sendResponse(200, CommonController::getBalanceMSW($balance, $message, 0));
                                }
                                else
                                {
                                    $message = $getBalance;
                                    $this->_sendResponse(200, CommonController::getBalanceMSW('', $message, 42));//Casino: Can't get balance
                                }
                            }
                            else
                            {
                                $message = "Player does not exist in the specified casino";
                                $this->_sendResponse(200, CommonController::getBalanceMSW('', $message, 83));
                            }
                        }
                        else
                        {
                            $message = "Player is inactive";
                            $this->_sendResponse(200, CommonController::getBalanceMSW('', $message, 77));
                        }
                    }
                    else
                    {
                        $message = "Invalid MID";
                        $this->_sendResponse(200, CommonController::getBalanceMSW('', $message, 76));
                    }
                }
                else
                {
                    $message = "Invalid Service ID";
                    $this->_sendResponse(200, CommonController::getBalanceMSW('', $message, 35));
                }
            }
            else
            {
                $message = "MID and ServiceID should be numeric";
                $this->_sendResponse(200, CommonController::getBalanceMSW('', $message, 75));
            }
        }
        else
        {
            $message = "Parameters are not set";
            $this->_sendResponse(200, CommonController::getBalanceMSW('',$message, 11));
        }
    }

    public function actionDepositMSW()
    {
        //import APIs that will be used
        Yii::import('application.components.CasinoController');

        //instantiate classes that will be used
        $casinoController = new CasinoController();
        $refServices = new RefServicesModel();
        $members = new MembersModel();
        $memberServices = new MemberServicesModel();
        $terminalSessions = new TerminalSessionsModel();
        $terminals = new TerminalsModel();
        $sites = new SitesModel();

        $request = $this->_readJsonRequest();

        $mid = htmlentities($request['MID']);
        $serviceID = htmlentities($request['ServiceID']);
        $amount = htmlentities($request['Amount']);
        $method = htmlentities($request['Method']);
        $tracking = htmlentities($request['Tracking']);
        $betSlip = htmlentities($request['BetSlipID']);
        $betRef = htmlentities($request['BetRefID']);
        //check if all required fields are filled
        if(isset($mid) && $mid != '' && isset($serviceID) && $serviceID != '' && isset($amount) && $amount != '' && isset($method) && $method != ''&& isset($tracking) && $tracking != ''&& isset($betSlip) && $betSlip != ''&& isset($betRef) && $betRef != '')
        {
            //check if entered fields are of valid data type
            if(is_numeric($mid) && is_numeric($serviceID))
            {
                //check if entered amount is valid
                if(Utilities::validateInput($amount))
                {
                    //check if service id entered is valid (found in npos.ref_services table)
                    $serviceInfo = $refServices->getServiceInfo($serviceID);
                    if($serviceInfo != '')
                    {
                        //check if mid is valid (existing in membership.members table)
                        $isMIDExisting = $members->checkMIDIfExisting($mid);
                        if($isMIDExisting != '')
                        {
                            //check if player is active
                            $isActive = $members->checkIfActive($mid);
                            if($isActive != '')
                            {
                                //check if card is ewallet/e-SAFE
                                $isEwallet = $members->checkIfEwallet($mid);
                                if($isEwallet['IsEwallet'] == 1)
                                {
                                    //check if amount is greater than 0
                                    if($amount > 0)
                                    {
                                        $msResult = $memberServices->getDetailsByMIDAndCasinoID($mid, $serviceID);
                                        if($msResult)
                                        {
                                            //get Terminal ID if MID has Session
                                            $hasTerminalSession = $terminalSessions->checkIfHasTerminalSession2($mid, $serviceID);
                                            if($hasTerminalSession)
                                            {
                                                $terminalCode = $hasTerminalSession['TerminalCode'];
                                                 $terminal= str_replace('ICSA-', '', $terminalCode);
                                                 $terminal= str_replace('VIP', '', $terminal);
                                                //$siteID = $terminals->getSiteIDByTerminalID($terminalID);
                                            }
                                            else
                                            {
                                                $terminal = ''; 
                                            }
                                            $serviceUsername = $msResult['ServiceUsername'];
                                            $servicePassword = $msResult['ServicePassword'];

                                            $tracking1 = $tracking;
                                            $tracking2 = $betSlip; // BetSlipID
                                            $tracking3 = $betRef; // BetRerference ID
                                            $tracking4 = $terminal; // Terminal Code

                                            if ($serviceID == 20) { //RTG V15
                                                $locatorname = Yii::app()->params['skinNameNonPlatinum'];
                                            } else {
                                                $locatorname = '';
                                            }
                                            $transSearchInfo = $casinoController->TransactionSearchInfo($serviceID, $serviceUsername, $tracking1, $tracking2, $tracking3, '');

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
                                                if ($apiresult == 'TRANSACTIONSTATUS_APPROVED' || $apiresult == 'true' || $apiresult == 'approved') {
                                                $transSearchStatus = '1';
                                                } else {
                                                $transSearchStatus = '2';
                                                }
                                                if($transSearchStatus==2)
                                                    {
                                            // Call Deposit API in RTG
                                            $resultDeposit = $casinoController->Deposit($serviceID, $serviceUsername, $servicePassword, $amount, $tracking1, $tracking2, $tracking3, $tracking4, $locatorname);
                                            if (is_null($resultDeposit)) {
                                                $transSearchInfo = $casinoController->TransactionSearchInfo($serviceID, $serviceUsername, $tracking1, $tracking2, $tracking3, '');

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
                                                if (isset($resultDeposit['TransactionInfo'])) {
                                                    //RTG / Magic Macau
                                                    if (isset($resultDeposit['TransactionInfo']['DepositGenericResult'])) {
                                                        $transrefid = $resultDeposit['TransactionInfo']['DepositGenericResult']['transactionID'];
                                                        $apiresult = $resultDeposit['TransactionInfo']['DepositGenericResult']['transactionStatus'];
                                                        $apierrmsg = $resultDeposit['TransactionInfo']['DepositGenericResult']['ErrorMessage'];
                                                    }
                                                    //MG / Vibrant Vegas
                                                    else if (isset($resultDeposit['TransactionInfo']['MG'])) {
                                                        $transrefid = $resultDeposit['TransactionInfo']['MG']['TransactionId'];
                                                        $apiresult = $resultDeposit['TransactionInfo']['MG']['TransactionStatus'];
                                                        $apierrmsg = $resultDeposit['ErrorMessage'];
                                                    }
                                                    //Rockin Reno
                                                    else if (isset($resultDeposit['TransactionInfo']['PT'])) {
                                                        $transrefid = $resultDeposit['TransactionInfo']['PT']['TransactionId'];
                                                        $apiresult = $resultDeposit['TransactionInfo']['PT']['TransactionStatus'];
                                                        $apierrmsg = $resultDeposit['TransactionInfo']['PT']['TransactionStatus'];
                                                    }
                                                } else 
                                                    {
                                                        if($resultDeposit['ErrorCode']==52)
                                                        {
                                                        $apiresult= 'INVALID_LOGIN';
                                                        }
                                                            
                                                    }
                                            }

                                            if ($apiresult == 'TRANSACTIONSTATUS_APPROVED' || $apiresult == 'true' || $apiresult == 'approved') {
                                                $transstatus = '1';
                                            } else {
                                                $transstatus = '2';
                                            }
                                            if ($apiresult == "true" || $apiresult == 'TRANSACTIONSTATUS_APPROVED' || $apiresult == 'approved') {
                                                if ($transstatus == 1) {
                                                    $message = 'MSW Deposit Transaction Successful';
                                                    $this->_sendResponse(200, CommonController::depositMSW($transrefid, $transstatus, $message, 0));
                                                } else {
                                                    $message = 'MSW Deposit Transaction Failed';
                                                    $this->_sendResponse(200, CommonController::depositMSW('', $transstatus, $message, 81));
                                                }
                                            } else {
                                                if ($resultDeposit['ErrorCode']==52)
                                                {
                                                 $message = 'Invalid Player Login Please try Again';
                                                $this->_sendResponse(200, CommonController::withdrawMSW('', $transstatus, $message, 52));    
                                                }
                                                else
                                                {
                                                    $message = 'MSW Deposit Transaction Failed';
                                                    $this->_sendResponse(200, CommonController::depositMSW('', $transstatus, $message, 81));
                                                }
                                            }
                                        }
                                        else
                                        {
                                            $message = 'MSW Deposit Transaction Successful';
                                            $this->_sendResponse(200, CommonController::depositMSW($transrefid, $transSearchStatus, $message, 0));
                                        }
                                        }
                                        else
                                        {
                                            $message = "Player does not exist in the specified casino";
                                            $this->_sendResponse(200, CommonController::depositMSW('', '', $message, 83));
                                        }
                                    }
                                    else
                                    {
                                        $message = "Amount should be greater than 0";
                                        $this->_sendResponse(200, CommonController::depositMSW('', '', $message, 72));
                                    }
                                }
                                else
                                {
                                    $message = "Non-eSAFE player is not allowed";
                                    $this->_sendResponse(200, CommonController::depositMSW('', '', $message, 78));
                                }
                            }
                            else
                            {
                                $message = "Player is inactive";
                                $this->_sendResponse(200, CommonController::depositMSW('', '', $message, 77));
                            }
                        }
                        else
                        {
                            $message = "Invalid MID";
                            $this->_sendResponse(200, CommonController::depositMSW('', '', $message, 76));
                        }
                    }
                    else
                    {
                        $message = "Invalid Service ID";
                        $this->_sendResponse(200, CommonController::depositMSW('', '', $message, 35));
                    }
                }
                else
                {
                    $message = "Invalid Amount";
                    $this->_sendResponse(200, CommonController::depositMSW('', '', $message, 34));
                }
            }
            else
            {
                $message = "MID and ServiceID should be numeric";
                $this->_sendResponse(200, CommonController::depositMSW('', '', $message, 75));
            }
        }
        else
        {
            $message = "Parameters are not set";
            $this->_sendResponse(200, CommonController::depositMSW('', '', $message, 11));
        }
    }

    public function actionWithdrawMSW()
    {
        //import APIs that will be used
        Yii::import('application.components.CasinoController');

        //instantiate classes that will be used
        $casinoController = new CasinoController();
        $refServices = new RefServicesModel();
        $members = new MembersModel();
        $memberServices = new MemberServicesModel();
        $terminalSessions = new TerminalSessionsModel();
        $terminals = new TerminalsModel();
        $sites = new SitesModel();

        $request = $this->_readJsonRequest();

        $mid = htmlentities($request['MID']);
        $serviceID = htmlentities($request['ServiceID']);
        $amount = htmlentities($request['Amount']);
        $method = htmlentities($request['Method']);
        $tracking = htmlentities($request['Tracking']);
        $betSlip = htmlentities($request['BetSlipID']);
        $betRef = htmlentities($request['BetRefID']);

        //check if all required fields are filled
        if(isset($mid) && $mid != '' && isset($serviceID) && $serviceID != '' && isset($amount) && $amount != '' && isset($method) && $method != '' && isset($tracking) && $tracking != '' && isset($betSlip) && $betSlip != ''&& isset($betRef) && $betRef != '')
        {
            //check if entered fields are of valid data type
            if(is_numeric($mid) && is_numeric($serviceID))
            {
                //check if entered amount is valid
                if(Utilities::validateInput($amount))
                {
                    //check if service id entered is valid (found in npos.ref_services table)
                    $serviceInfo = $refServices->getServiceInfo($serviceID);
                    if($serviceInfo != '')
                    {
                        //check if mid is valid (existing in membership.members table)
                        $isMIDExisting = $members->checkMIDIfExisting($mid);
                        if($isMIDExisting != '')
                        {
                            //check if player is active
                            $isActive = $members->checkIfActive($mid);
                            if($isActive != '')
                            {
                                //check if card is ewallet/e-SAFE
                                $isEwallet = $members->checkIfEwallet($mid);
                                if($isEwallet['IsEwallet'] == 1)
                                {
                                    //check if amount is greater than 0
                                    if($amount > 0)
                                    {
                                        $msResult = $memberServices->getDetailsByMIDAndCasinoID($mid, $serviceID);
                                        if($msResult)
                                        {
                                            $serviceUsername = $msResult['ServiceUsername'];
                                            $servicePassword = $msResult['ServicePassword'];

                                            // Call GetBalance to get the amount in Casino
                                            $getBalance = $casinoController->GetBalance($serviceID, $serviceUsername);
                                            if(is_array($getBalance))
                                            {
                                                $balance = $getBalance['balance'];

                                                //check if RTG balance is greater than or equal to the inputted amount
                                                if($balance < $amount)
                                                {
                                                    $message = 'Amount should be less than or equal to the withdrawable balance';
                                                    $this->_sendResponse(200, CommonController::withdrawMSW('', '', $message, 79));
                                                }
                                                else
                                                {
                                                    $hasTerminalSession = $terminalSessions->checkIfHasTerminalSession2($mid, $serviceID);
                                                    if($hasTerminalSession)
                                                    {
                                                        $terminalCode = $hasTerminalSession['TerminalCode'];
                                                        $terminal= str_replace('ICSA-', '', $terminalCode);
                                                        $terminal= str_replace('VIP', '', $terminal);
                                                        //$siteID = $terminals->getSiteIDByTerminalID($terminalID);
                                                    }
                                                    else
                                                    {
                                                        $terminal = '';
                                                    }
//
                                                        $tracking1 = $tracking;
                                                        $tracking2 = $betSlip; // BetSlipID
                                                        $tracking3 = $betRef; // BetRerference ID
                                                        $tracking4 = $terminal; // Terminal Code
                                                        $locatorname = '';
//                                                        $siteClassification = $sites->getSiteClassfication($siteID);
//                                                        if ($serviceID == 20) { //RTG V15
//                                                            if ($siteClassification['SiteClassificationID'] == 1) { //1 - Non Platinum, 2 - Platinum
//                                                                $locatorname = Yii::app()->params['skinNameNonPlatinum'];
//                                                            } else {
//                                                                $locatorname = Yii::app()->params['skinNamePlatinum'];
//                                                            }
//                                                        } else {
//                                                            $locatorname = '';
//                                                        }

                                                        // Call Withdraw API in RTG
                                                $transSearchInfo = $casinoController->TransactionSearchInfo($serviceID, $serviceUsername, $tracking1, $tracking2, $tracking3, '');

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
                                                    if ($apiresult == 'TRANSACTIONSTATUS_APPROVED' || $apiresult == 'true' || $apiresult == 'approved') {
                                                    $transSearchStatus = '1';
                                                    } else {
                                                    $transSearchStatus = '2';
                                                    }
                                                    if($transSearchStatus==2)
                                                        {
                                                        $resultWithdraw = $casinoController->Withdraw($serviceID, $serviceUsername, $servicePassword, $amount, $tracking1, $tracking2, $tracking3, $tracking4, $locatorname);
                                                        if (is_null($resultWithdraw)) {
                                                            $transSearchInfo = $casinoController->TransactionSearchInfo($serviceID, $serviceUsername, $tracking1, $tracking2, $tracking3, '');

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
                                                            if (isset($resultWithdraw['TransactionInfo'])) {
                                                                //RTG / Magic Macau
                                                                if (isset($resultWithdraw['TransactionInfo']['WithdrawGenericResult'])) {
                                                                    $transrefid = $resultWithdraw['TransactionInfo']['WithdrawGenericResult']['transactionID'];
                                                                    $apiresult = $resultWithdraw['TransactionInfo']['WithdrawGenericResult']['transactionStatus'];
                                                                    $apierrmsg = $resultDeposit['TransactionInfo']['WithdrawGenericResult']['ErrorMessage'];
                                                                }
                                                                //MG / Vibrant Vegas
                                                                if (isset($resultWithdraw['TransactionInfo']['MG'])) {
                                                                    $transrefid = $resultWithdraw['TransactionInfo']['MG']['TransactionId'];
                                                                    $apiresult = $resultWithdraw['TransactionInfo']['MG']['TransactionStatus'];
                                                                }
                                                                //PT / Rocking Reno
                                                                if (isset($resultWithdraw['TransactionInfo']['PT'])) {
                                                                    $transrefid = $resultWithdraw['TransactionInfo']['PT']['TransactionId'];
                                                                    $apiresult = $resultWithdraw['TransactionInfo']['PT']['TransactionStatus'];
                                                                }
                                                            }
                                                            else
                                                            {
                                                                if($resultWithdraw['ErrorCode']==62)
                                                                    {
                                                                    $apiresult= 'INVALID_LOGIN';
                                                                    }
                                                            }
                                                        }

                                                        if ($apiresult == 'TRANSACTIONSTATUS_APPROVED' || $apiresult == 'true' || $apiresult == 'approved') {
                                                            $transstatus = '1';
                                                        } else {
                                                            $transstatus = '2';
                                                        }
                                                
                                                        if ($apiresult == "true" || $apiresult == 'TRANSACTIONSTATUS_APPROVED' || $apiresult == 'approved') {
                                                            if ($transstatus == 1) {
                                                                $message = 'MSW Withdraw Transaction Successful';
                                                                $this->_sendResponse(200, CommonController::withdrawMSW($transrefid, $transstatus, $message, 0));
                                                            } else {
                                                                $message = 'MSW Withdraw Transaction Failed';
                                                                $this->_sendResponse(200, CommonController::withdrawMSW('', $transstatus, $message, 82));
                                                            }
                                                        } else {
                                                            if ($resultWithdraw['ErrorCode']==62)
                                                            {
                                                             $message = 'Invalid Player Login Please try Again';
                                                            $this->_sendResponse(200, CommonController::withdrawMSW('', $transstatus, $message, 62));    
                                                            }
                                                            else
                                                            {
                                                            $message = 'MSW Withdraw Transaction Failed';
                                                            $this->_sendResponse(200, CommonController::withdrawMSW('', $transstatus, $message, 82));
                                                            }
                                                        }
//                                                    }
//                                                    else
//                                                    {
//                                                        $message = "Terminal has no session for that account";
//                                                        $this->_sendResponse(200, CommonController::withdrawMSW('', '', $message, 80));
//                                                    }
                                                }else{
                                                  $message = 'MSW Withdraw Transaction Successful';
                                                  $this->_sendResponse(200, CommonController::depositMSW($transrefid, $transSearchStatus, $message, 0));
                                                }
                                                }
                                            }
                                            else
                                            {
                                                $message = $getBalance;
                                                $this->_sendResponse(200, CommonController::withdrawMSW('', '', $message, 42));//Casino: Can't get balance
                                            }
                                        }
                                        else
                                        {
                                            $message = "Player does not exist in the specified casino";
                                            $this->_sendResponse(200, CommonController::withdrawMSW('', $message, 83));
                                        }
                                    }
                                    else
                                    {
                                        $message = "Amount should be greater than 0";
                                        $this->_sendResponse(200, CommonController::withdrawMSW('', '', $message, 72));
                                    }
                                }
                                else
                                {
                                    $message = "Non-eSAFE player is not allowed";
                                    $this->_sendResponse(200, CommonController::withdrawMSW('', '', $message, 78));
                                }
                            }
                            else
                            {
                                $message = "Player is inactive";
                                $this->_sendResponse(200, CommonController::withdrawMSW('', '', $message, 77));
                            }
                        }
                        else
                        {
                            $message = "Invalid MID";
                            $this->_sendResponse(200, CommonController::withdrawMSW('', '', $message, 76));
                        }
                    }
                    else
                    {
                        $message = "Invalid Service ID";
                        $this->_sendResponse(200, CommonController::withdrawMSW('', '', $message, 35));
                    }
                }
                else
                {
                    $message = "Invalid Amount";
                    $this->_sendResponse(200, CommonController::withdrawMSW('', '', $message, 34));
                }
            }
            else
            {
                $message = "MID and ServiceID should be numeric";
                $this->_sendResponse(200, CommonController::withdrawMSW('', '', $message, 75));
            }
        }
        else
        {
            $message = "Parameters are not set";
            $this->_sendResponse(200, CommonController::withdrawMSW('', '', $message, 11));
        }
    }
}