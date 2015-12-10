<?php

/**
 * Controller for Membership Portal API Naboo
 * @date 12-03-2015
 * @author taalcantara
 */
class MPapiController extends Controller
{

    private $_caching = FALSE;
    var $MID;

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

    public function actionRegisterMemberNaboo()
    {
        $request = $this->_readJsonRequest();
        $transMsg = '';
        $errorCode = '';
        $module = 'RegisterMemberNaboo';
        $appLogger = new AppLogger();
        $paramval = CJSON::encode($request);
        $message = "[" . $module . "] Input: " . $paramval;
        $appLogger->log($appLogger->logdate, "[request]", $message);
        $logger = new ErrorLogger();
        if (isset($request['MobileNo']) && isset($request['PlayerName']))
        {
            if (($request['MobileNo'] == '') || ($request['PlayerName'] == ''))
            {
                $transMsg = "One or more fields is not set or is blank.";
                $errorCode = 1;
                Utilities::log("ReturnMessage: " . $transMsg . " ErrorCode: " . $errorCode);
                $data = CommonController::retMsgRegisterMemberNaboo($module, $errorCode, $transMsg);
                $message = "[" . $module . "] Output: " . CJSON::encode($data);
                $appLogger->log($appLogger->logdate, "[response]", $message);
                $this->_sendResponse(200, CJSON::encode($data));
                $logMessage = 'One or more fields is not set or is blank.';
                $logger->log($logger->logdate, "[REGISTERMEMBERNABOO ERROR]: " . $request['MobileNo'] . " || " . $request['PlayerName'], $logMessage);
                exit;
            }
            else if ((substr($request['MobileNo'], 0, 2) == "09" && strlen($request['MobileNo']) != 11))
            {
                $transMsg = "Invalid mobile number.";
                $errorCode = 95;
                Utilities::log("ReturnMessage: " . $transMsg . " ErrorCode: " . $errorCode);
                $data = CommonController::retMsgRegisterMemberNaboo($module, $errorCode, $transMsg);
                $message = "[" . $module . "] Output: " . CJSON::encode($data);
                $appLogger->log($appLogger->logdate, "[response]", $message);
                $this->_sendResponse(200, CJSON::encode($data));
                $logMessage = "Invalid mobile number.";
                $logger->log($logger->logdate, "[REGISTERMEMBERNABOO ERROR]: " . $request['MobileNo'] . " || " . $request['PlayerName'], $logMessage);
                exit;
            }
            else if ((substr($request['MobileNo'], 0, 2) != '09'))
            {
                $transMsg = "Mobile number should begin with '09'.";
                $errorCode = 98;
                Utilities::log("ReturnMessage: " . $transMsg . " ErrorCode: " . $errorCode);
                $data = CommonController::retMsgRegisterMemberNaboo($module, $errorCode, $transMsg);
                $message = "[" . $module . "] Output: " . CJSON::encode($data);
                $appLogger->log($appLogger->logdate, "[response]", $message);
                $this->_sendResponse(200, CJSON::encode($data));
                $logMessage = "Mobile number should begin with '09'.";
                $logger->log($logger->logdate, "[REGISTERMEMBERNABOO ERROR]: " . $request['MobileNo'] . " || " . $request['PlayerName'], $logMessage);
                exit;
            }
            else if (!is_numeric($request['MobileNo']))
            {
                $transMsg = "Mobile number should consist of numbers only.";
                $errorCode = 16;
                Utilities::log("ReturnMessage: " . $transMsg . " ErrorCode: " . $errorCode);
                $data = CommonController::retMsgRegisterMemberNaboo($module, $errorCode, $transMsg);
                $message = "[" . $module . "] Output: " . CJSON::encode($data);
                $appLogger->log($appLogger->logdate, "[response]", $message);
                $this->_sendResponse(200, CJSON::encode($data));
                $logMessage = 'Mobile number should consist of numbers only.';
                $logger->log($logger->logdate, "[REGISTERMEMBERNABOO ERROR]: " . $request['MobileNo'] . " || " . $request['PlayerName'], $logMessage);
                exit;
            }
            else if (strlen($request['PlayerName']) < 2)
            {
                $transMsg = "Player name should not be less than 2 characters long.";
                $errorCode = 99;
                Utilities::log("ReturnMessage: " . $transMsg . " ErrorCode: " . $errorCode);
                $data = CommonController::retMsgRegisterMemberNaboo($module, $errorCode, $transMsg);
                $message = "[" . $module . "] Output: " . CJSON::encode($data);
                $appLogger->log($appLogger->logdate, "[response]", $message);
                $this->_sendResponse(200, CJSON::encode($data));
                $logMessage = 'Player name should not be less than 2 characters long.';
                $logger->log($logger->logdate, "[REGISTERMEMBERNABOO ERROR]: " . $request['MobileNo'] . " || " . $request['PlayerName'], $logMessage);
                exit;
            }
            else if (preg_match("/^[A-Za-z\s]+$/", trim($request['PlayerName'])) == 0)
            {
                $transMsg = "Player name should consist of letters and spaces only.";
                $errorCode = 100;
                Utilities::log("ReturnMessage: " . $transMsg . " ErrorCode: " . $errorCode);
                $data = CommonController::retMsgRegisterMemberNaboo($module, $errorCode, $transMsg);
                $message = "[" . $module . "] Output: " . CJSON::encode($data);
                $appLogger->log($appLogger->logdate, "[response]", $message);
                $this->_sendResponse(200, CJSON::encode($data));
                $logMessage = 'Player name should consist of letters and spaces only.';
                $logger->log($logger->logdate, "[REGISTERMEMBERNABOO ERROR]: " . $request['MobileNo'] . " || " . $request['PlayerName'], $logMessage);
                exit;
            }
            else
            {
                //start of declaration of models to be used
                $casinoServicesModel = new CasinoServicesModel();
                $memberServices = new MemberServicesModel();
                $membersModel = new MembersModel();

                $mobileNo = trim($request['MobileNo']);
                $playerName = trim($request['PlayerName']);

                $lastSpacePosition = strrpos($playerName, ' ');
                $split = explode(" ", $playerName);

                $firstName = substr($playerName, 0, $lastSpacePosition);
                $lastName = $split[count($split) - 1];

                //Create dummy info base on MID
                $email = $mobileNo . "@philweb.com.ph";
                $birthDate = "1970-01-01";
                $address = "NA";
                $city = "NA";
                $phone = '123-4567';
                $zip = 'NA';
                $countryCode = 'PH';
                $gender = 1;
                $vipLevel = 1;

                $arrMembers['UserName'] = $mobileNo;
                $arrMembers['Password'] = NULL;
                $rnpassword = '123456';
                $hashedpassword = 'ca0acccc44caa4f03b5318e92512';
                $arrMembers['DateCreated'] = 'NOW(6)';
                $arrMembers['Status'] = 1;

                $arrMemberInfo['FirstName'] = $firstName;
                $arrMemberInfo['MiddleName'] = 'NA';
                $arrMemberInfo['LastName'] = $lastName;
                $arrMemberInfo['NickName'] = 'NA';
                $arrMemberInfo['Address1'] = 'NA';
                $arrMemberInfo['Address2'] = 'NA';
                $arrMemberInfo['IdentificationNumber'] = 1;
                $arrMemberInfo['IdentificationID'] = 1;
                $arrMemberInfo['MobileNumber'] = $mobileNo;
                $arrMemberInfo['AlternateMobileNumber'] = $phone;
                $arrMemberInfo['Email'] = $email;
                $arrMemberInfo['AlternateEmail'] = '';
                $arrMemberInfo['Birthdate'] = $birthDate;
                $arrMemberInfo['NationalityID'] = 1;
                $arrMemberInfo['OccupationID'] = 1;
                $arrMemberInfo['Gender'] = 1;
                $arrMemberInfo['IsSmoker'] = 1;
                $arrMemberInfo['EmailSubscription'] = 1;
                $arrMemberInfo['SMSSubscription'] = 1;
                $arrMemberInfo['IsCompleteInfo'] = 1;
                $arrMemberInfo['DateCreated'] = 'NOW(6)';
                $arrMemberInfo['DateVerified'] = 'NOW(6)';
                $arrMemberInfo['ReferrerCode'] = null;

                $IsInsert = $membersModel->insertMembers($arrMembers, $arrMemberInfo);

                if ($IsInsert["MID"] > 0)
                {
                    $this->MID = $IsInsert["MID"];
                    $isupdated = $membersModel->updatePasswordUsingMID($rnpassword, $this->MID);

                    if ($isupdated)
                    {
                        $UBserviceID = 20; //Naboo
                        $casinoservices = $casinoServicesModel->getUserBasedCasinoDetails($UBserviceID);

                        $MID = $this->MID;

                        $serviceID = $casinoservices['ServiceID'];
                        $serviceName = $casinoservices['ServiceGroupName'];
                        if (strpos($serviceName, 'RTG2') !== false)
                        {
                            //START: Call Casino Create Account API Method');
                            $msresult = $memberServices->AddMemberServices($serviceID, $MID, $mobileNo, $rnpassword, $hashedpassword, 1, 'NOW(6)', 1, $vipLevel, null, 1, 5000);
                            if ($msresult == 1)
                            {

                                $apiResult = CasinoAPI::createAccount($serviceName, $serviceID, $mobileNo, $rnpassword, $firstName, $lastName, $birthDate, $gender, $email, $phone, $address, $city, $countryCode, $vipLevel);

                                if (!$apiResult)
                                {
                                    $transMsg = "An error has occurred when connecting to RTG. Please try again.";
                                    $errorCode = 101;
                                    Utilities::log("ReturnMessage: " . $transMsg . " ErrorCode: " . $errorCode);
                                    $data = CommonController::retMsgRegisterMemberNaboo($module, $errorCode, $transMsg);
                                    $message = "[" . $module . "] Output: " . CJSON::encode($data);
                                    $appLogger->log($appLogger->logdate, "[response]", $message);
                                    $this->_sendResponse(200, CJSON::encode($data));
                                    $logMessage = 'An error has occurred when connecting to RTG. Please try again.';
                                    $logger->log($logger->logdate, "[REGISTERMEMBERNABOO ERROR]: " . $request['MobileNo'] . " || " . $request['PlayerName'], $logMessage);
                                    exit;
                                }
                                else
                                {

                                    //Checking if casino reply is successful, then push array result
                                    if ($apiResult['IsSucceed'] == true && $apiResult['ErrorID'] == 1)
                                    {

                                        if ($vipLevel == 1)
                                        {
                                            CasinoController::Deposit($serviceID, $mobileNo, $rnpassword, 1, 5000, '', 'D', '', '', '');
                                            $transMsg = "Successfully registered.";
                                            $errorCode = 102;
                                            Utilities::log("ReturnMessage: " . $transMsg . " ErrorCode: " . $errorCode);
                                            $data = CommonController::retMsgRegisterMemberNaboo($module, $errorCode, $transMsg);
                                            $message = "[" . $module . "] Output: " . CJSON::encode($data);
                                            $appLogger->log($appLogger->logdate, "[response]", $message);
                                            $this->_sendResponse(200, CJSON::encode($data));
                                            $logMessage = 'Successfully registered.';
                                            $logger->log($logger->logdate, "[REGISTERMEMBERNABOO ERROR]: " . $request['MobileNo'] . " || " . $request['PlayerName'], $logMessage);
                                            exit;
                                        }
                                    }
                                    else
                                    {
                                        $transMsg = "An error has occurred when connecting to RTG. Please try again.";
                                        $errorCode = 101;
                                        Utilities::log("ReturnMessage: " . $transMsg . " ErrorCode: " . $errorCode);
                                        $data = CommonController::retMsgRegisterMemberNaboo($module, $errorCode, $transMsg);
                                        $message = "[" . $module . "] Output: " . CJSON::encode($data);
                                        $appLogger->log($appLogger->logdate, "[response]", $message);
                                        $this->_sendResponse(200, CJSON::encode($data));
                                        $logMessage = 'An error has occurred when connecting to RTG. Please try again.';
                                        $logger->log($logger->logdate, "[REGISTERMEMBERNABOO ERROR]: " . $request['MobileNo'] . " || " . $request['PlayerName'], $logMessage);
                                        exit;
                                    }
                                }
                            }
                            else
                            {
                                $transMsg = "An error has occurred when inserted to member services. Registration failed.";
                                $errorCode = 103;
                                Utilities::log("ReturnMessage: " . $transMsg . " ErrorCode: " . $errorCode);
                                $data = CommonController::retMsgRegisterMemberNaboo($module, $errorCode, $transMsg);
                                $message = "[" . $module . "] Output: " . CJSON::encode($data);
                                $appLogger->log($appLogger->logdate, "[response]", $message);
                                $this->_sendResponse(200, CJSON::encode($data));
                                $logMessage = 'An error has occurred when inserted to member services. Registration failed.';
                                $logger->log($logger->logdate, "[REGISTERMEMBERNABOO ERROR]: " . $request['MobileNo'] . " || " . $request['PlayerName'], $logMessage);
                                exit;
                            }
                        }
                        else
                        {
                            $transMsg = "An error has occurred when connecting to RTG. Please try again.";
                            $errorCode = 101;
                            Utilities::log("ReturnMessage: " . $transMsg . " ErrorCode: " . $errorCode);
                            $data = CommonController::retMsgRegisterMemberNaboo($module, $errorCode, $transMsg);
                            $message = "[" . $module . "] Output: " . CJSON::encode($data);
                            $appLogger->log($appLogger->logdate, "[response]", $message);
                            $this->_sendResponse(200, CJSON::encode($data));
                            $logMessage = 'An error has occurred when connecting to RTG. Please try again.';
                            $logger->log($logger->logdate, "[REGISTERMEMBERNABOO ERROR]: " . $request['MobileNo'] . " || " . $request['PlayerName'], $logMessage);
                            exit;
                        }
                    }
                    else
                    {
                        $transMsg = "Registration failed.";
                        $errorCode = 104;
                        Utilities::log("ReturnMessage: " . $transMsg . " ErrorCode: " . $errorCode);
                        $data = CommonController::retMsgRegisterMemberNaboo($module, $errorCode, $transMsg);
                        $message = "[" . $module . "] Output: " . CJSON::encode($data);
                        $appLogger->log($appLogger->logdate, "[response]", $message);
                        $this->_sendResponse(200, CJSON::encode($data));
                        $logMessage = 'Registration failed.';
                        $logger->log($logger->logdate, "[REGISTERMEMBERNABOO ERROR]: " . $request['MobileNo'] . " || " . $request['PlayerName'], $logMessage);
                        exit;
                    }
                }
                else
                {
                    $transMsg = "This mobile number was already registered.";
                    $errorCode = 105;
                    Utilities::log("ReturnMessage: " . $transMsg . " ErrorCode: " . $errorCode);
                    $data = CommonController::retMsgRegisterMemberNaboo($module, $errorCode, $transMsg);
                    $message = "[" . $module . "] Output: " . CJSON::encode($data);
                    $appLogger->log($appLogger->logdate, "[response]", $message);
                    $this->_sendResponse(200, CJSON::encode($data));
                    $logMessage = 'Exisitng mobile number.';
                    $logger->log($logger->logdate, "[REGISTERMEMBERNABOO ERROR]: " . $request['MobileNo'] . " || " . $request['PlayerName'], $logMessage);
                    exit;
                }
            }
        }
    }

    public function actionReloadNaboo()
    {
        $request = $this->_readJsonRequest();
        $transMsg = '';
        $errorCode = '';
        $module = 'ReloadNaboo';
        $apiMethod = 3;
        $appLogger = new AppLogger();
        $paramval = CJSON::encode($request);
        $message = "[" . $module . "] Input: " . $paramval;
        $appLogger->log($appLogger->logdate, "[request]", $message);
        $logger = new ErrorLogger();
//        $apiLogsModel = new APILogsModel();
        if (isset($request['Password']) && isset($request['MobileNo']) && isset($request['Amount']))
        {
            if (($request['Password'] == '') || ($request['MobileNo'] == '') || ($request['Amount'] == ''))
            {
                $transMsg = "One or more fields is not set or is blank.";
                $errorCode = 1;
                Utilities::log("ReturnMessage: " . $transMsg . " ErrorCode: " . $errorCode);
                $data = CommonController::retMsgReloadNaboo($module, $errorCode, $transMsg);
                $message = "[" . $module . "] Output: " . CJSON::encode($data);
                $appLogger->log($appLogger->logdate, "[response]", $message);
                $this->_sendResponse(200, CJSON::encode($data));
                $logMessage = 'One or more fields is not set or is blank.';
                $logger->log($logger->logdate, "[RELOADNABOO ERROR]: " . sha1($request['Password']) . " || " . $request['MobileNo'] . " || " . $request['Amount'], $logMessage);
//                $apiDetails = 'RELOADNABOO-Failed: Invalid input parameters. ';
//                $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, '', $apiDetails, '', 2);
//                if ($isInserted == 0)
//                {
//                    $logMessage = "Failed to insert to APILogs.";
//                    $logger->log($logger->logdate, "[RELOADNABOO ERROR]: " . sha1($request['Password']) . " || " . $request['MobileNo'] . " || " . $request['Amount'], $logMessage);
//                }
                exit;
            }
            else if (preg_match("/[^a-z_\-0-9]/i", trim($request['Password'])) == 0)
            {
                $transMsg = "Password should consist of letters and numbers only.";
                $errorCode = 18;
                Utilities::log("ReturnMessage: " . $transMsg . " ErrorCode: " . $errorCode);
                $data = CommonController::retMsgReloadNaboo($module, $errorCode, $transMsg);
                $message = "[" . $module . "] Output: " . CJSON::encode($data);
                $appLogger->log($appLogger->logdate, "[response]", $message);
                $this->_sendResponse(200, CJSON::encode($data));
                $logMessage = 'Password should consist of letters and numbers only.';
                $logger->log($logger->logdate, "[RELOAD ERROR]: " . $request['Password'] . " || " . $request['MobileNo'] . " || " . $request['Amount'], $logMessage);
//                $apiDetails = 'RELOAD-Failed: Invalid input parameters. ';
//                $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, '', $apiDetails, '', 2);
//                if ($isInserted == 0)
//                {
//                    $logMessage = "Failed to insert to APILogs.";
//                    $logger->log($logger->logdate, "[RELOAD ERROR]: " . $request['Password'] . " || " . $request['MobileNo'] . " || " . $request['Amount'], $logMessage);
//                }
                exit;
            }
            else if (strlen($request['Password']) < 8 && strlen($request['Password']) > 12)
            {
                $transMsg = "Password should be 8 to 12 characters long.";
                $errorCode = 14;
                Utilities::log("ReturnMessage: " . $transMsg . " ErrorCode: " . $errorCode);
                $data = CommonController::retMsgReloadNaboo($module, $errorCode, $transMsg);
                $message = "[" . $module . "] Output: " . CJSON::encode($data);
                $appLogger->log($appLogger->logdate, "[response]", $message);
                $this->_sendResponse(200, CJSON::encode($data));
                $logMessage = 'Password should be 8 to 12 characters long.';
                $logger->log($logger->logdate, "[RELOADNABOO ERROR]: " . sha1($request['Password']) . " || " . $request['MobileNo'] . " || " . $request['Amount'], $logMessage);
//                $apiDetails = 'RELOADNABOO-Failed: Invalid input parameters. ';
//                $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, '', $apiDetails, '', 2);
//                if ($isInserted == 0)
//                {
//                    $logMessage = "Failed to insert to APILogs.";
//                    $logger->log($logger->logdate, "[RELOADNABOO ERROR]: " . sha1($request['Password']) . " || " . $request['MobileNo'] . " || " . $request['Amount'], $logMessage);
//                }
                exit;
            }
            else if ((substr($request['MobileNo'], 0, 2) != '09'))
            {
                $transMsg = "Mobile number should begin with '09'.";
                $errorCode = 69;
                Utilities::log("ReturnMessage: " . $transMsg . " ErrorCode: " . $errorCode);
                $data = CommonController::retMsgReloadNaboo($module, $errorCode, $transMsg);
                $message = "[" . $module . "] Output: " . CJSON::encode($data);
                $appLogger->log($appLogger->logdate, "[response]", $message);
                $this->_sendResponse(200, CJSON::encode($data));
                $logMessage = "Mobile number should begin with '09'.";
                $logger->log($logger->logdate, "[RELOADNABOO ERROR]: " . sha1($request['Password']) . " || " . $request['MobileNo'] . " || " . $request['Amount'], $logMessage);
//                $apiDetails = 'RELOADNABOO-Failed: Invalid input parameters. ';
//                $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, '', $apiDetails, '', 2);
//                if ($isInserted == 0)
//                {
//                    $logMessage = "Failed to insert to APILogs.";
//                    $logger->log($logger->logdate, "[RELOADNABOO ERROR]: " . $request['Mobile'] . " || " . $request['PlayerName'], $logMessage);
//                }
                exit;
            }
            else if ((substr($request['MobileNo'], 0, 2) == "09" && strlen($request['MobileNo']) != 11))
            {
                $transMsg = "Invalid mobile number.";
                $errorCode = 97;
                Utilities::log("ReturnMessage: " . $transMsg . " ErrorCode: " . $errorCode);
                $data = CommonController::retMsgReloadNaboo($module, $errorCode, $transMsg);
                $message = "[" . $module . "] Output: " . CJSON::encode($data);
                $appLogger->log($appLogger->logdate, "[response]", $message);
                $this->_sendResponse(200, CJSON::encode($data));
                $logMessage = 'Invalid mobile number.';
                $logger->log($logger->logdate, "[RELOAD ERROR]: " . $request['Password'] . " || " . $request['MobileNo'] . " || " . $request['Amount'], $logMessage);
//                $apiDetails = 'RELOAD-Failed: Invalid input parameters. ';
//                $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, '', $apiDetails, '', 2);
//                if ($isInserted == 0)
//                {
//                    $logMessage = "Failed to insert to APILogs.";
//                    $logger->log($logger->logdate, "[RELOAD ERROR]: " . $request['Password'] . " || " . $request['MobileNo'] . " || " . $request['Amount'], $logMessage);
//                }
                exit;
            }
            else if (!is_numeric($request['Amount']))
            {
                $transMsg = "Amount should consist of numbers only.";
                $errorCode = 16;
                Utilities::log("ReturnMessage: " . $transMsg . " ErrorCode: " . $errorCode);
                $data = CommonController::retMsgReloadNaboo($module, $errorCode, $transMsg);
                $message = "[" . $module . "] Output: " . CJSON::encode($data);
                $appLogger->log($appLogger->logdate, "[response]", $message);
                $this->_sendResponse(200, CJSON::encode($data));
                $logMessage = 'Amount should consist of numbers only.';
                $logger->log($logger->logdate, "[RELOAD ERROR]: " . $request['Password'] . " || " . $request['MobileNo'] . " || " . $request['Amount'], $logMessage);
//                $apiDetails = 'RELOAD-Failed: Invalid input parameters. ';
//                $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, '', $apiDetails, '', 2);
//                if ($isInserted == 0)
//                {
//                    $logMessage = "Failed to insert to APILogs.";
//                    $logger->log($logger->logdate, "[RELOAD ERROR]: " . $request['Password'] . " || " . $request['MobileNo'] . " || " . $request['Amount'], $logMessage);
//                }
                exit;
            }
            else if ((int) $request['Amount'] % 100 != 0)
            {
                $transMsg = "Amount should be divisible by 100.";
                $errorCode = 16;
                Utilities::log("ReturnMessage: " . $transMsg . " ErrorCode: " . $errorCode);
                $data = CommonController::retMsgReloadNaboo($module, $errorCode, $transMsg);
                $message = "[" . $module . "] Output: " . CJSON::encode($data);
                $appLogger->log($appLogger->logdate, "[response]", $message);
                $this->_sendResponse(200, CJSON::encode($data));
                $logMessage = 'Amount should be divisible by 100.';
                $logger->log($logger->logdate, "[RELOAD ERROR]: " . $request['Password'] . " || " . $request['MobileNo'] . " || " . $request['Amount'], $logMessage);
//                $apiDetails = 'RELOAD-Failed: Invalid input parameters. ';
//                $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, '', $apiDetails, '', 2);
//                if ($isInserted == 0)
//                {
//                    $logMessage = "Failed to insert to APILogs.";
//                    $logger->log($logger->logdate, "[RELOAD ERROR]: " . $request['Password'] . " || " . $request['MobileNo'] . " || " . $request['Amount'], $logMessage);
//                }
                exit;
            }
            else if ((int) $request['Amount'] > 100000)
            {
                $transMsg = "Amount should be lesser than 100,000.";
                $errorCode = 16;
                Utilities::log("ReturnMessage: " . $transMsg . " ErrorCode: " . $errorCode);
                $data = CommonController::retMsgReloadNaboo($module, $errorCode, $transMsg);
                $message = "[" . $module . "] Output: " . CJSON::encode($data);
                $appLogger->log($appLogger->logdate, "[response]", $message);
                $this->_sendResponse(200, CJSON::encode($data));
                $logMessage = 'Amount should be lesser than by 100,000.';
                $logger->log($logger->logdate, "[RELOAD ERROR]: " . $request['Password'] . " || " . $request['MobileNo'] . " || " . $request['Amount'], $logMessage);
//                $apiDetails = 'RELOAD-Failed: Invalid input parameters. ';
//                $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, '', $apiDetails, '', 2);
//                if ($isInserted == 0)
//                {
//                    $logMessage = "Failed to insert to APILogs.";
//                    $logger->log($logger->logdate, "[RELOAD ERROR]: " . $request['Password'] . " || " . $request['MobileNo'] . " || " . $request['Amount'], $logMessage);
//                }
                exit;
            }
            else if ((int) $request['Amount'] < 5000)
            {
                $transMsg = "Amount should be greater than 5,000.";
                $errorCode = 16;
                Utilities::log("ReturnMessage: " . $transMsg . " ErrorCode: " . $errorCode);
                $data = CommonController::retMsgReloadNaboo($module, $errorCode, $transMsg);
                $message = "[" . $module . "] Output: " . CJSON::encode($data);
                $appLogger->log($appLogger->logdate, "[response]", $message);
                $this->_sendResponse(200, CJSON::encode($data));
                $logMessage = 'Amount should be greater than 5,000.';
                $logger->log($logger->logdate, "[RELOADNABOO ERROR]: " . sha1($request['Password']) . " || " . $request['MobileNo'] . " || " . $request['Amount'], $logMessage);
                $apiDetails = 'RELOADNABOO-Failed: Invalid input parameters. ';
                $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, '', $apiDetails, '', 2);
                if ($isInserted == 0)
                {
                    $logMessage = "Failed to insert to APILogs.";
                    $logger->log($logger->logdate, "[RELOADNABOO ERROR]: " . sha1($request['Password']) . " || " . $request['MobileNo'] . " || " . $request['Amount'], $logMessage);
                }
                exit;
            }
            else if (($request['Amount']) < 500 && ($request['Amount']) > 100000)
            {
                $transMsg = "Amount should be between 500 to 100,000 only.";
                $errorCode = 16;
                Utilities::log("ReturnMessage: " . $transMsg . " ErrorCode: " . $errorCode);
                $data = CommonController::retMsgReloadNaboo($module, $errorCode, $transMsg);
                $message = "[" . $module . "] Output: " . CJSON::encode($data);
                $appLogger->log($appLogger->logdate, "[response]", $message);
                $this->_sendResponse(200, CJSON::encode($data));
                $logMessage = 'Amount should be between 500 to 100,000 only.';
                $logger->log($logger->logdate, "[RELOADNABOO ERROR]: " . sha1($request['Password']) . " || " . $request['MobileNo'] . " || " . $request['Amount'], $logMessage);
//                $apiDetails = 'RELOADNABOO-Failed: Invalid input parameters. ';
//                $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, '', $apiDetails, '', 2);
//                if ($isInserted == 0)
//                {
//                    $logMessage = "Failed to insert to APILogs.";
//                    $logger->log($logger->logdate, "[RELOADNABOO ERROR]: " . sha1($request['Password']) . " || " . $request['MobileNo'] . " || " . $request['Amount'], $logMessage);
//                }
                exit;
            }
            else
            {
                //process naboo reload
            }
        }
    }

    private function formatMobileNo($cpNumber)
    {
        return str_replace("09", "639", substr($cpNumber, 0, 2)) . substr($cpNumber, 2, strlen($cpNumber));
    }

    private function _readJsonRequest()
    {
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
    private function _sendResponse($status = 200, $body = '', $content_type = 'text/html')
    {
        // set the status
        $status_header = 'HTTP/1.1 ' . $status . ' ' . $this->_getStatusCodeMessage($status);
        header($status_header);
        // and the content type
        header('Content-type: ' . $content_type);
        // pages with body are easy
        if ($body != '')
        {
            // send the body
            echo $body;
        }
        // we need to create the body if none is passed
        else
        {
            // create some body messages
            $message = '';
            // this is purely optional, but makes the pages a little nicer to read
            // for your users.  Since you won't likely send a lot of different status codes,
            // this also shouldn't be too ponderous to maintain
            switch ($status)
            {
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
    }

    /**
     * HTTP Status Code Message
     * @param string $status
     * @return bool
     */
    private function _getStatusCodeMessage($status)
    {
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

    private function _validateDate($date)
    {
        $d = DateTime::createFromFormat('Y-m-d', $date);
        return $d && $d->format('Y-m-d') == $date;
    }

    private function SubmitData($uri, $postdata)
    {
        $curl = curl_init($uri);
        curl_setopt($curl, CURLOPT_FRESH_CONNECT, $this->_caching);
        curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, $this->_connectionTimeout);
        curl_setopt($curl, CURLOPT_TIMEOUT, $this->_timeout);
        curl_setopt($curl, CURLOPT_USERAGENT, $this->_userAgent);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($curl, CURLOPT_POST, TRUE);
        curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE);
        // Data+Files to be posted
        curl_setopt($curl, CURLOPT_POSTFIELDS, $postdata);
        $response = curl_exec($curl);
        $http_status = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);
        return array($http_status, $response);
    }

}

?>
