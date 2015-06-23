<?php

/**
 * Controller for Membership Portal API v1
 * @date 6-13-2014
 * @author fdlsison
 */
class MPapiController extends Controller {

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

    //@date 07-10-2014
    //@purpose Login authentication
    private function _authenticate($username, $password) {
        $module = 'Login';
        $apiMethod = 1;

        $appLogger = new AppLogger();

        $membersModel = new MembersModel();
        $memberCardsModel = new MemberCardsModel();
        $membershipTempModel = new MembershipTempModel();
        $logger = new ErrorLogger();
        $apiLogsModel = new APILogsModel();

        //check if username is in members and is already verified
        if (Utilities::validateEmail($username)) {
            $result = $membersModel->getMembersDetails($username);
            $refID = $username;
        } else {
            $cardInfo = $memberCardsModel->getMIDUsingCard($username);
            if ($cardInfo > 0) {
                if ($cardInfo['Status'] == 1 || $cardInfo['Status'] == 5) {
                    $MID = $cardInfo['MID'];
                    $result = $membersModel->getMemberDetailsByMID($MID);
                } else if ($cardInfo['Status'] == 9) {
                    $result = "Card is banned.";
                } else {
                    $result = 0;
                }
            } else {
                $result = array();
            }

            $refID = $username;
        }

        $retVal = '';
        $strPass = md5($password);

        if (is_array($result) && count($result) > 0) {
            $mid = $result['MID'];

            switch ($result['Status']) {
                case 1:
                    if ($result['Password'] != $strPass) {

                        $logMessage = 'Password inputted is incorrect.';
                        $logger->log($logger->logdate, "[LOGIN ERROR]: " . $username . " || ", $logMessage);
                        $apiDetails = 'LOGIN-Authenticate-Failed: Incorrect password.';
                        $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, $refID, $apiDetails, '', 2);
                        if ($isInserted == 0) {
                            $logMessage = "Failed to insert to APILogs.";
                            $logger->log($logger->logdate, "[LOGIN ERROR]: " . $username . " || ", $logMessage);
                        }

                        $retVal = false;
                    }
                    else
                        $retVal = $result;
                    break;

                case 0:
                    $logMessage = 'Account is Inactive';

                    $logger->log($logger->logdate, "[LOGIN ERROR]: " . $username . " || ", $logMessage);
                    $apiDetails = 'LOGIN-Authenticate-Failed: Member account is inactive.';
                    $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, $refID, $apiDetails, '', 2);
                    if ($isInserted == 0) {
                        $logMessage = "Failed to insert to APILogs.";
                        $logger->log($logger->logdate, "[LOGIN ERROR]: " . $username . " || ", $logMessage);
                    }

                    $retVal = false;
                    break;

                case 2:
                    $logMessage = 'Account is Suspended.';

                    $logger->log($logger->logdate, "[LOGIN ERROR]: " . $username . " || ", $logMessage);
                    $apiDetails = 'LOGIN-Authenticate-Failed: Member account is suspended.';
                    $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, $refID, $apiDetails, '', 2);
                    if ($isInserted == 0) {
                        $logMessage = "Failed to insert to APILogs.";
                        $logger->log($logger->logdate, "[LOGIN ERROR]: " . $username . " || ", $logMessage);
                    }

                    $retVal = false;
                    break;

                case 3:
                    $logMessage = 'Account is Locked (Login Attempts).';

                    $logger->log($logger->logdate, "[LOGIN ERROR]: " . $username . " || ", $logMessage);
                    $apiDetails = 'LOGIN-Authenticate-Failed: Member account is locked (login attempts).';
                    $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, $refID, $apiDetails, '', 2);
                    if ($isInserted == 0) {
                        $logMessage = "Failed to insert to APILogs.";
                        $logger->log($logger->logdate, "[LOGIN ERROR]: " . $username . " || ", $logMessage);
                    }

                    $retVal = false;
                    break;

                case 4:
                    $logMessage = 'Account is Locked (By Admin).';

                    $logger->log($logger->logdate, "[LOGIN ERROR]: " . $username . " || ", $logMessage);
                    $apiDetails = 'LOGIN-Authenticate-Failed: Member account is locked (admin).';
                    $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, $refID, $apiDetails, '', 2);
                    if ($isInserted == 0) {
                        $logMessage = "Failed to insert to APILogs.";
                        $logger->log($logger->logdate, "[LOGIN ERROR]: " . $username . " || ", $logMessage);
                    }

                    $retVal = false;
                    break;

                case 5:
                    $logMessage = 'Account is Locked (By Admin).';

                    $logger->log($logger->logdate, "[LOGIN ERROR]: " . $username . " || ", $logMessage);
                    $apiDetails = 'LOGIN-Authenticate-Failed: Member account is locked (admin).';
                    $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, $refID, $apiDetails, '', 2);
                    if ($isInserted == 0) {
                        $logMessage = "Failed to insert to APILogs.";
                        $logger->log($logger->logdate, "[LOGIN ERROR]: " . $username . " || ", $logMessage);
                    }

                    $retVal = false;
                    break;

                case 6:
                    $logMessage = 'Account is Terminated.';

                    $logger->log($logger->logdate, "[LOGIN ERROR]: " . $username . " || ", $logMessage);
                    $apiDetails = 'LOGIN-Authenticate-Failed: Member account is terminated.';
                    $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, $refID, $apiDetails, '', 2);
                    if ($isInserted == 0) {
                        $logMessage = "Failed to insert to APILogs.";
                        $logger->log($logger->logdate, "[LOGIN ERROR]: " . $username . " || ", $logMessage);
                    }

                    $retVal = false;
                    break;

                default:
                    $logMessage = 'Account is Invalid.';

                    $logger->log($logger->logdate, "[LOGIN ERROR]: " . $username . " || ", $logMessage);
                    $apiDetails = 'LOGIN-Authenticate-Failed: Member account is invalid.';
                    $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, $refID, $apiDetails, '', 2);
                    if ($isInserted == 0) {
                        $logMessage = "Failed to insert to APILogs.";
                        $logger->log($logger->logdate, "[LOGIN ERROR]: " . $username . " || ", $logMessage);
                    }

                    $retVal = false;
                    break;
            }
        } else if (is_string($result)) {
            $transMsg = "Card is Banned.";
            $errorCode = 9;
            Utilities::log("ReturnMessage: " . $transMsg . " ErrorCode: " . $errorCode);
            $data = CommonController::retMsgLogin($module, '', '', '', '', $errorCode, $transMsg);
            $message = "[Login] Output: " . CJSON::encode($data);
            $appLogger->log($appLogger->logdate, "[response]", $message);
            $this->_sendResponse(200, CJSON::encode($data));
            $logMessage = 'Card is Banned.';
            $logger->log($logger->logdate, "[LOGIN ERROR]: " . $username . " || ", $logMessage);
            $apiDetails = 'LOGIN-Authenticate-Failed: Member card is banned.';
            $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, $refID, $apiDetails, '', 2);
            if ($isInserted == 0) {
                $logMessage = "Failed to insert to APILogs.";
                $logger->log($logger->logdate, "[LOGIN ERROR]: " . $username . " || ", $logMessage);
            }

            $retVal = false;
            exit;
        } else if ($result == 0) {
            $transMsg = "Account is Invalid.";
            $errorCode = 38;
            Utilities::log("ReturnMessage: " . $transMsg . " ErrorCode: " . $errorCode);
            $data = CommonController::retMsgLogin($module, '', '', '', '', $errorCode, $transMsg);
            $message = "[Login] Output: " . CJSON::encode($data);
            $appLogger->log($appLogger->logdate, "[response]", $message);
            $this->_sendResponse(200, CJSON::encode($data));
            $logMessage = 'Account is Invalid.';
            $logger->log($logger->logdate, "[LOGIN ERROR]: " . $username . " || ", $logMessage);
            $apiDetails = 'LOGIN-Authenticate-Failed: Member account is invalid.';
            $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, $refID, $apiDetails, '', 2);
            if ($isInserted == 0) {
                $logMessage = "Failed to insert to APILogs.";
                $logger->log($logger->logdate, "[LOGIN ERROR]: " . $username . " || ", $logMessage);
            }

            $retVal = false;
            exit;
        } else {

            $isTempAcctExist = $membershipTempModel->checkTempUser($username);

            //check if account has no transactions yet in kronus cashier
            if ($isTempAcctExist > 0) {
                $transMsg = "You need to transact at least one transaction before you can login.";
                $errorCode = 39;
                Utilities::log("ReturnMessage: " . $transMsg . " ErrorCode: " . $errorCode);
                $data = CommonController::retMsgLogin($module, '', '', '', '', $errorCode, $transMsg);
                $message = "[Login] Output: " . CJSON::encode($data);
                $appLogger->log($appLogger->logdate, "[response]", $message);
                $this->_sendResponse(200, CJSON::encode($data));
                $logMessage = 'You need to transact at least one transaction before you can login.';
                $logger->log($logger->logdate, "[LOGIN ERROR]: " . $username . " || ", $logMessage);
                $apiDetails = 'LOGIN-Authenticate-Failed: You need to transact at least one transaction before you can login.';
                $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, $refID, $apiDetails, '', 2);
                if ($isInserted == 0) {
                    $logMessage = "Failed to insert to APILogs.";
                    $logger->log($logger->logdate, "[LOGIN ERROR]: " . $username . " || ", $logMessage);
                }

                $retVal = false;
                exit;
            } else {
                $transMsg = "Account is Invalid.";
                $errorCode = 38;
                Utilities::log("ReturnMessage: " . $transMsg . " ErrorCode: " . $errorCode);
                $data = CommonController::retMsgLogin($module, '', '', '', '', $errorCode, $transMsg);
                $message = "[Login] Output: " . CJSON::encode($data);
                $appLogger->log($appLogger->logdate, "[response]", $message);
                $this->_sendResponse(200, CJSON::encode($data));
                $logMessage = 'Account is Invalid.';
                $logger->log($logger->logdate, "[LOGIN ERROR]: " . $username . " || ", $logMessage);
                $apiDetails = 'LOGIN-Authenticate-Failed: Member account is invalid.';
                $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, $refID, $apiDetails, '', 2);
                if ($isInserted == 0) {
                    $logMessage = "Failed to insert to APILogs.";
                    $logger->log($logger->logdate, "[LOGIN ERROR]: " . $username . " || ", $logMessage);
                }

                $retVal = false;
                exit;
            }
        }

        return $retVal;
    }

    //@purpose serves as input for the username & password of the member to access the portal
    public function actionLogin() {
        $request = $this->_readJsonRequest();
        $transMsg = '';
        $errorCode = '';
        $module = 'Login';
        $apiMethod = 1;

        $appLogger = new AppLogger();

        $paramval = CJSON::encode($request);
        $message = "[" . $module . "] Input: " . $paramval;
        $appLogger->log($appLogger->logdate, "[request]", $message);

        $logger = new ErrorLogger();
        $apiLogsModel = new APILogsModel();

        //check if username & password is inputted
        if (isset($request['Username']) && isset($request['Password'])) {
            if (($request['Username'] == '') || ($request['Password'] == '')) {
                $logMessage = "One or more fields is not set or is blank.";
                $transMsg = "One or more fields is not set or is blank.";
                $errorCode = 1;
                Utilities::log("ReturnMessage: " . $transMsg . " ErrorCode: " . $errorCode);
                $data = CommonController::retMsgLogin($module, '', '', '', '', $errorCode, $transMsg);
                $message = "[Login] Output: " . CJSON::encode($data);
                $appLogger->log($appLogger->logdate, "[response]", $message);
                $this->_sendResponse(200, CJSON::encode($data));
                $logger->log($logger->logdate, "[LOGIN ERROR]: " . $request['Username'] . " || ", $logMessage);
                $apiDetails = 'LOGIN-Failed: Invalid Login parameters.';
                $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, '', $apiDetails, '', 2);
                if ($isInserted == 0) {
                    $logMessage = "Failed to insert to APILogs.";
                    $logger->log($logger->logdate, "[LOGIN ERROR]: " . $request['Username'] . " || ", $logMessage);
                }

                exit;
            } else {
                if (Utilities::validateEmail($request['Username']) || ctype_alnum($request['Password'])) {
                    $username = trim($request['Username']);
                    $password = trim($request['Password']);

                    //start of declaration of models to be used
                    $memberSessionsModel = new MemberSessionsModel();
                    $memberCardsModel = new MemberCardsModel();
                    $cardsModel = new CardsModel();
                    $auditTrailModel = new AuditTrailModel();
                    $memberInfoModel = new MemberInfoModel();
                    $members = $this->_authenticate($username, $password);
                    if ($members) {
                        $MID = $members['MID'];

                        $isVIP = $members['IsVIP'];
                        $activeSession = $memberSessionsModel->checkSession($MID);

                        $remoteIP = $_SERVER['REMOTE_ADDR'];
                        $session = new CHttpSession();
                        $session->open();
                        $mpSessionID = $session->getSessionID();

                        $session->setSessionID($mpSessionID);

                        if ($activeSession['COUNT(MemberSessionID)'] > 0) {
                            $result = $memberSessionsModel->updateSession($mpSessionID, $MID, $remoteIP);
                        } else {

                            $result = $memberSessionsModel->insertMemberSession($MID, $mpSessionID, $remoteIP);
                        }

                        if ($result > 0) {
                            $memberSessions = $memberSessionsModel->getMemberSessions($MID);

                            $mpSessionID = $memberSessions['SessionID'];

                            $memberCards = $memberCardsModel->getActiveMemberCardInfo($MID);

                            $cardNumber = $memberCards['CardNumber'];

                            $cards = $cardsModel->getCardInfo($cardNumber);

                            $cardTypeID = $cards['CardTypeID'];
                            $refID = $username;

                            $isSuccessful = $auditTrailModel->logEvent(AuditTrailModel::API_LOGIN, 'Username: ' . $username, array('MID' => $MID, 'SessionID' => $mpSessionID, 'AID' => $MID));
                            if ($isSuccessful == 0) {
                                $logMessage = 'Failed to log event on Audittrail.';
                                $logger->log($logger->logdate, "[LOGIN ERROR]: " . $MID . " || ", $logMessage);
                            }
                            $isUpdated = $memberSessionsModel->updateTransactionDate($MID, $mpSessionID);
                            if ($isUpdated > 0) {
                                $transMsg = $mpSessionID;
                                $logMessage = 'Login successful.';
                                $errorCode = 0;
                                $data = CommonController::retMsgLogin($module, $mpSessionID, $cardTypeID, $isVIP, $cardNumber, $errorCode, $transMsg);
                                $message = "[Login] Output: " . CJSON::encode($data);
                                $appLogger->log($appLogger->logdate, "[response]", $message);
                                $this->_sendResponse(200, CJSON::encode($data));
                                $logger->log($logger->logdate, "[LOGIN SUCCESSFUL]: " . $MID . " || ", $logMessage);
                                $apiDetails = 'LOGIN-UpdateTransDate-Success: MID = ' . $MID . ' SessionID = ' . $mpSessionID;
                                $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, $refID, $apiDetails, '', 1);
                                if ($isInserted == 0) {
                                    $logMessage = "Failed to insert to APILogs.";
                                    $logger->log($logger->logdate, "[LOGIN ERROR]: " . $MID . " || ", $logMessage);
                                }
                            } else {
                                $logMessage = 'Failed to update transaction date in membersessions table WHERE MID = ' . $MID . ' AND SessionID = ' . $mpSessionID;
                                $transMsg = 'Transaction failed.';
                                $errorCode = 4;
                                Utilities::log("ReturnMessage: " . $transMsg . " ErrorCode: " . $errorCode);
                                $data = CommonController::retMsgLogin($module, '', '', '', '', $errorCode, $transMsg);
                                $message = "[Login] Output: " . CJSON::encode($data);
                                $appLogger->log($appLogger->logdate, "[response]", $message);
                                $this->_sendResponse(200, CJSON::encode($data));
                                $logger->log($logger->logdate, "[LOGIN ERROR]: " . $MID . " || ", $logMessage);
                                $apiDetails = 'LOGIN-UpdateTransDate-Failed: ' . 'Username: ' . $username . ' MID = ' . $MID . ' SessionID = ' . $mpSessionID;
                                $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, $refID, $apiDetails, '', 2);
                                if ($isInserted == 0) {
                                    $logMessage = "Failed to insert to APILogs.";
                                    $logger->log($logger->logdate, "[LOGIN ERROR]: " . $MID . " || ", $logMessage);
                                }

                                exit;
                            }
                        } else {
                            $logMessage = 'Failed to insert/update membersession in membersessions table WHERE MID = ' . $MID . ' AND SessionID = ' . $mpSessionID . ' AND RemoteIP = ' . $remoteIP;
                            $transMsg = 'Transaction failed.';
                            $errorCode = 4;
                            Utilities::log("ReturnMessage: " . $transMsg . " ErrorCode: " . $errorCode);
                            $data = CommonController::retMsgLogin($module, '', '', '', '', $errorCode, $transMsg);
                            $message = "[Login] Output: " . CJSON::encode($data);
                            $appLogger->log($appLogger->logdate, "[response]", $message);
                            $this->_sendResponse(200, CJSON::encode($data));
                            $logger->log($logger->logdate, "[LOGIN ERROR]: " . $MID . " || ", $logMessage);
                            $apiDetails = 'LOGIN-Insert/UpdateMemberSession-Failed: MID = ' . $MID . ' SessionID = ' . $mpSessionID;
                            $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, '', $apiDetails, '', 2);
                            if ($isInserted == 0) {
                                $logMessage = "Failed to insert to APILogs.";
                                $logger->log($logger->logdate, "[LOGIN ERROR]: " . $MID . " || ", $logMessage);
                            }

                            exit;
                        }
                    } else {
                        $logMessage = 'Member is not found in db.';
                        $transMsg = 'Member not found';
                        $errorCode = 3;
                        Utilities::log("ReturnMessage: " . $transMsg . " ErrorCode: " . $errorCode);
                        $data = CommonController::retMsgLogin($module, '', '', '', '', $errorCode, $transMsg);
                        $message = "[Login] Output: " . CJSON::encode($data);
                        $appLogger->log($appLogger->logdate, "[response]", $message);
                        $this->_sendResponse(200, CJSON::encode($data));
                        $logger->log($logger->logdate, "[LOGIN ERROR]: " . $username . " || ", $logMessage);
                        $apiDetails = 'LOGIN-Authenticate-Failed: Member account is invalid.';
                        $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, '', $apiDetails, '', 2);
                        if ($isInserted == 0) {
                            $logMessage = "Failed to insert to APILogs.";
                            $logger->log($logger->logdate, "[LOGIN ERROR]: " . $username . " || ", $logMessage);
                        }

                        exit;
                    }
                } else {
                    $logMessage = 'Invalid input.';
                    $transMsg = 'Invalid input.';
                    $errorCode = 2;
                    Utilities::log("ReturnMessage: " . $transMsg . " ErrorCode: " . $errorCode);
                    $data = CommonController::retMsgLogin($module, '', '', '', '', $errorCode, $transMsg);
                    $message = "[Login] Output: " . CJSON::encode($data);
                    $appLogger->log($appLogger->logdate, "[response]", $message);
                    $this->_sendResponse(200, CJSON::encode($data));
                    $logger->log($logger->logdate, "[LOGIN ERROR]: " . $username . " || ", $logMessage);
                    $apiDetails = 'LOGIN-Failed: Invalid input parameters';
                    $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, '', $apiDetails, '', 2);
                    if ($isInserted == 0) {
                        $logMessage = "Failed to insert to APILogs.";
                        $logger->log($logger->logdate, "[LOGIN ERROR]: " . $username . " || ", $logMessage);
                    }

                    exit;
                }
            }
        } else {
            $logMessage = 'One or more fields is not set or is blank';
            $transMsg = "One or more fields is not set or is blank.";
            $errorCode = 1;
            Utilities::log("ReturnMessage: " . $transMsg . " ErrorCode: " . $errorCode);
            $data = CommonController::retMsgLogin($module, '', '', '', '', $errorCode, $transMsg);
            $message = "[Login] Output: " . CJSON::encode($data);
            $appLogger->log($appLogger->logdate, "[response]", $message);
            $this->_sendResponse(200, CJSON::encode($data));
            $logger->log($logger->logdate, "[LOGIN ERROR]: " . $request['Username'] . " || ", $logMessage);
            $apiDetails = 'LOGIN-Failed: Invalid login parameters.';
            $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, '', $apiDetails, '', 2);
            if ($isInserted == 0) {
                $logMessage = "Failed to insert to APILogs.";
                $logger->log($logger->logdate, "[LOGIN ERROR]: " . $request['Username'] . " || ", $logMessage);
            }

            exit;
        }
    }

    public function actionChangePassword() {
        $request = $this->_readJsonRequest();

        $transMsg = '';
        $errorCode = '';
        $module = 'ChangePassword';
        $apiMethod = 20;

        $appLogger = new AppLogger();

        $paramval = CJSON::encode($request);
        $message = "[" . $module . "] Input: " . $paramval;
        $appLogger->log($appLogger->logdate, "[request]", $message);

        $logger = new ErrorLogger();
        $apiLogsModel = new APILogsModel();

        if (isset($request['CardNumber']) && isset($request['NewPassword'])) {
            if (($request['CardNumber'] == '') || ($request['NewPassword'] == '')) {
                $transMsg = "One or more fields is not set or is blank.";
                $errorCode = 1;
                Utilities::log("ReturnMessage: " . $transMsg . " ErrorCode: " . $errorCode);
                $data = CommonController::retMsgChangePassword($module, $errorCode, $transMsg);
                $message = "[" . $module . "] Output: " . CJSON::encode($data);
                $appLogger->log($appLogger->logdate, "[response]", $message);
                $this->_sendResponse(200, CJSON::encode($data));
                $logMessage = 'One or more fields is not set or is blank.';
                $logger->log($logger->logdate, "[CHANGEPASSWORD ERROR]: " . $request['CardNumber'] . " || ", $logMessage);
                $apiDetails = 'CHANGEPASSWORD-Failed: Invalid input parameter.';
                $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, '', $apiDetails, '', 2);
                if ($isInserted == 0) {
                    $logMessage = "Failed to insert to APILogs.";
                    $logger->log($logger->logdate, "[CHANGEPASSWORD ERROR]: " . $request['CardNumber'] . " || ", $logMessage);
                }

                exit;
            } else {
                $cardNumber = trim($request['CardNumber']);
                $newPassword = trim($request['NewPassword']);

                //start of declaration of models to be used
                $membersModel = new MembersModel();
                $memberCardsModel = new MemberCardsModel();
                $memberSessionsModel = new MemberSessionsModel();
                $membersRecentPasswordsModel = new MembersRecentPasswordsModel();
                $helpers = new Helpers();
                $cardsModel = new CardsModel();
                $auditTrailModel = new AuditTrailModel();

                if (ctype_alnum($cardNumber) == FALSE || ctype_alnum($newPassword) == FALSE) {
                    $transMsg = "Card number and new password must consist of letters and numbers only.";
                    $errorCode = 92;
                    Utilities::log("ReturnMessage: " . $transMsg . " ErrorCode: " . $errorCode);
                    $data = CommonController::retMsgChangePassword($module, $errorCode, $transMsg);
                    $message = "[" . $module . "] Output: " . CJSON::encode($data);
                    $appLogger->log($appLogger->logdate, "[response]", $message);
                    $this->_sendResponse(200, CJSON::encode($data));
                    $logMessage = 'Card number and new password must consist of letters and numbers only.';
                    $logger->log($logger->logdate, "[CHANGEPASSWORD ERROR]: " . $cardNumber . " || ", $logMessage);
                    $apiDetails = 'CHANGEPASSWORD-Failed: Invalid input parameter.';
                    $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, '', $apiDetails, '', 2);
                    if ($isInserted == 0) {
                        $logMessage = "Failed to insert to APILogs.";
                        $logger->log($logger->logdate, "[CHANGEPASSWORD ERROR]: " . $cardNumber . " || ", $logMessage);
                    }

                    exit;
                } else if (strlen($newPassword) < 5) {
                    $transMsg = "New password must be atleast 5 characters long.";
                    $errorCode = 93;
                    Utilities::log("ReturnMessage: " . $transMsg . " ErrorCode: " . $errorCode);
                    $data = CommonController::retMsgChangePassword($module, $errorCode, $transMsg);
                    $message = "[" . $module . "] Output: " . CJSON::encode($data);
                    $appLogger->log($appLogger->logdate, "[response]", $message);
                    $this->_sendResponse(200, CJSON::encode($data));
                    $logMessage = 'New password must be atleast 5 characters long.';
                    $logger->log($logger->logdate, "[CHANGEPASSWORD ERROR]: " . $cardNumber . " || ", $logMessage);
                    $apiDetails = 'CHANGEPASSWORD-Failed: Invalid input parameter.';
                    $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, '', $apiDetails, '', 2);
                    if ($isInserted == 0) {
                        $logMessage = "Failed to insert to APILogs.";
                        $logger->log($logger->logdate, "[CHANGEPASSWORD ERROR]: " . $cardNumber . " || ", $logMessage);
                    }

                    exit;
                } else {
                    $result = $memberCardsModel->getMIDUsingCard($cardNumber);
                    if ($result) {
                        $MID = $result['MID'];
                        $isDuplicate = $membersRecentPasswordsModel->isDuplicate($MID, $newPassword);
                        $countPassword = $isDuplicate['countpassword'];
                        if ($countPassword == 0) {
                            $countRecentPassword = $membersRecentPasswordsModel->countRecentPassword($MID);
                            $countRecentPassword = $countRecentPassword['countrecentpassword'];
                            if ($countRecentPassword == 5) {
                                $resultDate = $membersRecentPasswordsModel->getOldestDate($MID);
                                if ($resultDate) {
                                    $date = $resultDate['DateCreated'];
                                    $isPasswordUpdated = $membersRecentPasswordsModel->updateRecentPassword($MID, $newPassword, $date);
                                    if ($isPasswordUpdated == 0) {
                                        $transMsg = "Change password failed.";
                                        $errorCode = 94;
                                        Utilities::log("ReturnMessage: " . $transMsg . " ErrorCode: " . $errorCode);
                                        $data = CommonController::retMsgChangePassword($module, $errorCode, $transMsg);
                                        $message = "[" . $module . "] Output: " . CJSON::encode($data);
                                        $appLogger->log($appLogger->logdate, "[response]", $message);
                                        $this->_sendResponse(200, CJSON::encode($data));
                                        $logMessage = 'Change password failed.';
                                        $logger->log($logger->logdate, "[CHANGEPASSWORD ERROR]: " . $cardNumber . " || ", $logMessage);
                                        $apiDetails = 'CHANGEPASSWORD-UpdateRecentPassword-Failed: Failed to update.';
                                        $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, '', $apiDetails, '', 2);
                                        if ($isInserted == 0) {
                                            $logMessage = "Failed to insert to APILogs.";
                                            $logger->log($logger->logdate, "[CHANGEPASSWORD ERROR]: " . $cardNumber . " || ", $logMessage);
                                        }

                                        exit;
                                    }
                                }
                            } else {
                                $isPasswordInserted = $membersRecentPasswordsModel->insertRecentPassword($MID, $newPassword);
                                if ($isPasswordInserted == 0) {
                                    $transMsg = "Change password failed.";
                                    $errorCode = 94;
                                    Utilities::log("ReturnMessage: " . $transMsg . " ErrorCode: " . $errorCode);
                                    $data = CommonController::retMsgChangePassword($module, $errorCode, $transMsg);
                                    $message = "[" . $module . "] Output: " . CJSON::encode($data);
                                    $appLogger->log($appLogger->logdate, "[response]", $message);
                                    $this->_sendResponse(200, CJSON::encode($data));
                                    $logMessage = 'Change password failed.';
                                    $logger->log($logger->logdate, "[CHANGEPASSWORD ERROR]: " . $cardNumber . " || ", $logMessage);
                                    $apiDetails = 'CHANGEPASSWORD-InsertRecentPassword-Failed: Failed to insert.';
                                    $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, '', $apiDetails, '', 2);
                                    if ($isInserted == 0) {
                                        $logMessage = "Failed to insert to APILogs.";
                                        $logger->log($logger->logdate, "[CHANGEPASSWORD ERROR]: " . $cardNumber . " || ", $logMessage);
                                    }

                                    exit;
                                }
                            }
                            $isUpdated = $membersModel->updatePasswordUsingMID($MID, $newPassword);
                            $isSuccessful = $membersModel->updateForChangePasswordUsingMID($MID, 0);
                            if ($isUpdated > 0 && $isSuccessful > 0) {

                                $transMsg = "Change password successful.";
                                $errorCode = 0;
                                Utilities::log("ReturnMessage: " . $transMsg . " ErrorCode: " . $errorCode);
                                $data = CommonController::retMsgChangePassword($module, $errorCode, $transMsg);
                                $message = "[" . $module . "] Output: " . CJSON::encode($data);
                                $appLogger->log($appLogger->logdate, "[response]", $message);
                                $this->_sendResponse(200, CJSON::encode($data));
                                $logMessage = 'Change password successful.';
                                $logger->log($logger->logdate, "[CHANGEPASSWORD SUCCESSFUL]: " . $cardNumber . " || ", $logMessage);
                                $apiDetails = 'CHANGEPASSWORD-Successful: MID = ' . $MID;
                                $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, '', $apiDetails, '', 1);
                                if ($isInserted == 0) {
                                    $logMessage = "Failed to insert to APILogs.";
                                    $logger->log($logger->logdate, "[CHANGEPASSWORD ERROR]: " . $cardNumber . " || ", $logMessage);
                                }

                                $isLogged = $auditTrailModel->logEvent(AuditTrailModel::API_CHANGE_PASSWORD, 'CardNumber: ' . $cardNumber, array('MID' => $MID, 'SessionID' => ''));
                                if ($isLogged == 0) {
                                    $logMessage = 'Failed to log event on Audittrail.';
                                    $logger->log($logger->logdate, "[CHANGEPASSWORD ERROR]: " . $cardNumber . " || ", $logMessage);
                                }

                                exit;
                            } else {
                                $transMsg = "Change password failed.";
                                $errorCode = 94;
                                Utilities::log("ReturnMessage: " . $transMsg . " ErrorCode: " . $errorCode);
                                $data = CommonController::retMsgChangePassword($module, $errorCode, $transMsg);
                                $message = "[" . $module . "] Output: " . CJSON::encode($data);
                                $appLogger->log($appLogger->logdate, "[response]", $message);
                                $this->_sendResponse(200, CJSON::encode($data));
                                $logMessage = 'Change password failed.';
                                $logger->log($logger->logdate, "[CHANGEPASSWORD ERROR]: " . $cardNumber . " || ", $logMessage);
                                $apiDetails = 'CHANGEPASSWORD-UpdateMembersModel-Failed: Failed to update.';
                                $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, '', $apiDetails, '', 2);
                                if ($isInserted == 0) {
                                    $logMessage = "Failed to insert to APILogs.";
                                    $logger->log($logger->logdate, "[CHANGEPASSWORD ERROR]: " . $cardNumber . " || ", $logMessage);
                                }

                                exit;
                            }
                        } else {
                            $transMsg = "Password cannot be the same as the previous passwords.";
                            $errorCode = 96;
                            Utilities::log("ReturnMessage: " . $transMsg . " ErrorCode: " . $errorCode);
                            $data = CommonController::retMsgChangePassword($module, $errorCode, $transMsg);
                            $message = "[" . $module . "] Output: " . CJSON::encode($data);
                            $appLogger->log($appLogger->logdate, "[response]", $message);
                            $this->_sendResponse(200, CJSON::encode($data));
                            $logMessage = 'Change password failed.';
                            $logger->log($logger->logdate, "[CHANGEPASSWORD ERROR]: " . $cardNumber . " || ", $logMessage);
                            $apiDetails = 'CHANGEPASSWORD-Failed: Password inputted is already existing for this MID.';
                            $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, '', $apiDetails, '', 2);
                            if ($isInserted == 0) {
                                $logMessage = "Failed to insert to APILogs.";
                                $logger->log($logger->logdate, "[CHANGEPASSWORD ERROR]: " . $cardNumber . " || ", $logMessage);
                            }

                            exit;
                        }
                    } else {
                        $transMsg = "Card number does not exist.";
                        $errorCode = 61;
                        Utilities::log("ReturnMessage: " . $transMsg . " ErrorCode: " . $errorCode);
                        $data = CommonController::retMsgChangePassword($module, $errorCode, $transMsg);
                        $message = "[" . $module . "] Output: " . CJSON::encode($data);
                        $appLogger->log($appLogger->logdate, "[response]", $message);
                        $this->_sendResponse(200, CJSON::encode($data));
                        $logMessage = 'Card number does not exist..';
                        $logger->log($logger->logdate, "[CHANGEPASSWORD ERROR]: " . $cardNumber . " || ", $logMessage);
                        $apiDetails = 'CHANGEPASSWORD-Failed: Invalid input parameter.';
                        $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, '', $apiDetails, '', 2);
                        if ($isInserted == 0) {
                            $logMessage = "Failed to insert to APILogs.";
                            $logger->log($logger->logdate, "[CHANGEPASSWORD ERROR]: " . $cardNumber . " || ", $logMessage);
                        }

                        exit;
                    }
                }
            }
        } else {
            $transMsg = "One or more fields is not set or is blank.";
            $errorCode = 1;
            Utilities::log("ReturnMessage: " . $transMsg . " ErrorCode: " . $errorCode);
            $data = CommonController::retMsgChangePassword($module, $errorCode, $transMsg);
            $message = "[" . $module . "] Output: " . CJSON::encode($data);
            $appLogger->log($appLogger->logdate, "[response]", $message);
            $this->_sendResponse(200, CJSON::encode($data));
            $logMessage = 'One or more fields is not set or is blank.';
            $logger->log($logger->logdate, "[CHANGEPASSWORD ERROR]: " . $request['CardNumber'] . " || ", $logMessage);
            $apiDetails = 'CHANGEPASSWORD-Failed: Invalid input parameter.';
            $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, '', $apiDetails, '', 2);
            if ($isInserted == 0) {
                $logMessage = "Failed to insert to APILogs.";
                $logger->log($logger->logdate, "[CHANGEPASSWORD ERROR]: " . $request['CardNumber'] . " || ", $logMessage);
            }

            exit;
        }
    }

    public function actionForgotPassword() {
        $request = $this->_readJsonRequest();

        $transMsg = '';
        $errorCode = '';
        $module = 'ForgotPassword';
        $apiMethod = 2;
        $MID = 0;
        $ubCard = '';
        $isInserted = '';

        $appLogger = new AppLogger();

        $paramval = CJSON::encode($request);
        $message = "[" . $module . "] Input: " . $paramval;
        $appLogger->log($appLogger->logdate, "[request]", $message);

        $logger = new ErrorLogger();
        $apiLogsModel = new APILogsModel();

        if (isset($request['EmailCardNumber'])) {
            if ($request['EmailCardNumber'] == '') {
                $transMsg = "One or more fields is not set or is blank.";
                $logMessage = 'One or more fields is not set or is blank.';
                $errorCode = 1;
                Utilities::log("ReturnMessage: " . $transMsg . " ErrorCode: " . $errorCode);
                $data = CommonController::retMsg($module, $errorCode, $transMsg);
                $message = "[" . $module . "] Output: " . CJSON::encode($data);
                $appLogger->log($appLogger->logdate, "[response]", $message);
                $this->_sendResponse(200, CJSON::encode($data));
                $logger->log($logger->logdate, "[FORGOTPASSWORD ERROR]: " . $request['EmailCardNumber'] . " || ", $logMessage);
                $apiDetails = 'FORGOTPASSWORD-Failed: Invalid input parameter.';
                $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, '', $apiDetails, '', 2);
                if ($isInserted == 0) {
                    $logMessage = "Failed to insert to APILogs.";
                    $logger->log($logger->logdate, "[FORGOTPASSWORD ERROR]: " . $request['EmailCardNumber'] . " || ", $logMessage);
                }

                exit;
            } else {
                $emailCardNumber = trim($request['EmailCardNumber']);

                //start of declaration of models to be used
                $membersModel = new MembersModel();
                $memberInfoModel = new MemberInfoModel();
                $memberCardsModel = new MemberCardsModel();
                $memberSessionsModel = new MemberSessionsModel();
                $helpers = new Helpers();
                $cardsModel = new CardsModel();
                $auditTrailModel = new AuditTrailModel();

                $refID = $emailCardNumber;

                if (Utilities::validateEmail($emailCardNumber)) {
                    $data = $memberInfoModel->getDetailsUsingEmail($emailCardNumber);

                    if ($data) {
                        $MID = $data['MID'];
                        $firstname = $data['FirstName'];
                        $lastname = $data['LastName'];
                        $fullname = $firstname . ' ' . $lastname;

                        $ubCard = $memberCardsModel->getCardNumberUsingMID($MID);

                        $hashedUBCard = base64_encode($ubCard);

                        $result = $membersModel->updateForChangePasswordUsingMID($MID, 1);
                        if ($result > 0) {
                            $helpers->sendEmailForgotPassword($emailCardNumber, $fullname, $hashedUBCard);
                            $isSuccessful = $auditTrailModel->logEvent(AuditTrailModel::API_FORGOT_PASSWORD, 'EmailCardNumber: ' . $emailCardNumber, array('MID' => $MID, 'SessionID' => ''));
                            if ($isSuccessful == 0) {
                                $logMessage = "Failed to insert to Audittrail.";
                                $logger->log($logger->logdate, "[FORGOTPASSWORD ERROR]: " . $emailCardNumber . " || ", $logMessage);
                            }

                            $apiDetails = 'FORGOTPASSWORD-UpdateChangePass-Success: MID = ' . $MID;
                            $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, $refID, $apiDetails, '', 1);
                            if ($isInserted == 0) {
                                $logMessage = "Failed to insert to APILogs.";
                                $logger->log($logger->logdate, "[FORGOTPASSWORD ERROR]: " . $emailCardNumber . " || ", $logMessage);
                            }
                            $isUpdated = $memberSessionsModel->updateTransactionDate($MID);
                            if ($isUpdated > 0) {
                                $transMsg = 'Request for change password is successfully processed. Please verify the link sent to your email to reset your password.';
                                $errorCode = 0;
                                $data = CommonController::retMsg($module, $errorCode, $transMsg);
                                $message = "[" . $module . "] Output: " . CJSON::encode($data);
                                $appLogger->log($appLogger->logdate, "[response]", $message);
                                $this->_sendResponse(200, CJSON::encode($data));
                                $logMessage = 'Forgot Password successful.';
                                $logger->log($logger->logdate, "[FORGOTPASSWORD SUCCESSFUL]: " . $emailCardNumber . " || ", $logMessage);
                                $apiDetails = 'FORGOTPASSWORD-UpdateTransDate-Success: ' . ' MID = ' . $MID;
                                $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, $refID, $apiDetails, '', 1);
                                if ($isInserted == 0) {
                                    $logMessage = "Failed to insert to APILogs.";
                                    $logger->log($logger->logdate, "[FORGOTPASSWORD ERROR]: " . $emailCardNumber . " || ", $logMessage);
                                }

                                exit;
                            } else {
                                $transMsg = 'Error in updating.';
                                $logMessage = 'Error in updating.';
                                $errorCode = 29;
                                $data = CommonController::retMsg($module, $errorCode, $transMsg);
                                $message = "[" . $module . "] Output: " . CJSON::encode($data);
                                $appLogger->log($appLogger->logdate, "[response]", $message);
                                $this->_sendResponse(200, CJSON::encode($data));
                                $logger->log($logger->logdate, "[FORGOTPASSWORD ERROR]: " . $emailCardNumber . " || ", $logMessage);
                                $apiDetails = 'FORGOTPASSWORD-UpdateTransDate-Failed: ' . ' MID = ' . $MID;
                                $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, $refID, $apiDetails, '', 2);
                                if ($isInserted == 0) {
                                    $logMessage = "Failed to insert to APILogs.";
                                    $logger->log($logger->logdate, "[FORGOTPASSWORD ERROR]: " . $emailCardNumber . " || ", $logMessage);
                                }

                                exit;
                            }
                        } else {
                            $transMsg = 'Transaction failed.';
                            $logMessage = 'Transaction failed.';
                            $errorCode = 4;
                            Utilities::log("ReturnMessage: " . $transMsg . " ErrorCode: " . $errorCode);
                            $data = CommonController::retMsg($module, $errorCode, $transMsg);
                            $message = "[" . $module . "] Output: " . CJSON::encode($data);
                            $appLogger->log($appLogger->logdate, "[response]", $message);
                            $this->_sendResponse(200, CJSON::encode($data));
                            $logger->log($logger->logdate, "[FORGOTPASSWORD ERROR]: " . $emailCardNumber . " || ", $logMessage);
                            $apiDetails = 'FORGOTPASSWORD-UpdateChangePass-Failed: MID = ' . $MID;
                            $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, $refID, $apiDetails, '', 2);
                            if ($isInserted == 0) {
                                $logMessage = "Failed to insert to APILogs.";
                                $logger->log($logger->logdate, "[FORGOTPASSWORD ERROR]: " . $emailCardNumber . " || ", $logMessage);
                            }

                            exit;
                        }
                    } else {
                        $transMsg = 'Invalid Email Address.';
                        $logMessage = 'Invalid Email Address.';
                        $errorCode = 5;
                        Utilities::log("ReturnMessage: " . $transMsg . " ErrorCode: " . $errorCode);
                        $data = CommonController::retMsg($module, $errorCode, $transMsg);
                        $message = "[" . $module . "] Output: " . CJSON::encode($data);
                        $appLogger->log($appLogger->logdate, "[response]", $message);
                        $this->_sendResponse(200, CJSON::encode($data));
                        $logger->log($logger->logdate, "[FORGOTPASSWORD ERROR]: " . $emailCardNumber . " || ", $logMessage);
                        $apiDetails = 'FORGOTPASSWORD-Failed: Email is not found in db. MID = ' . $MID;
                        $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, $refID, $apiDetails, '', 2);
                        if ($isInserted == 0) {
                            $logMessage = "Failed to insert to APILogs.";
                            $logger->log($logger->logdate, "[FORGOTPASSWORD ERROR]: " . $emailCardNumber . " || ", $logMessage);
                        }

                        exit;
                    }
                } else if (Utilities::validateAlphaNumeric($emailCardNumber)) {
                    $isCardExist = $cardsModel->IsExist($emailCardNumber);
                    $data = $memberCardsModel->getMemberDetailsByCard($emailCardNumber);
                    if ($data && count($isCardExist) > 0) {
                        if (($data['Status'] == 1) || ($data['Status'] == 5)) {
                            $MID = $data['MID'];
                            $info = $memberInfoModel->getEmailFNameUsingMID($MID);
                            if (isset($info['Email']) && $info['Email'] != '') {
                                $fullname = $info['FirstName'] . ' ' . $info['LastName'];
                                $email = $info['Email'];
                                $hashedUBCard = base64_encode($emailCardNumber);
                                $result = $membersModel->updateForChangePasswordUsingMID($MID, 1);
                                if ($result > 0) {
                                    $helpers->sendEmailForgotPassword($email, $fullname, $hashedUBCard);
                                    $isSuccessful = $auditTrailModel->logEvent(AuditTrailModel::API_FORGOT_PASSWORD, 'EmailCardNumber: ' . $emailCardNumber, array('MID' => $MID, 'SessionID' => ''));
                                    if ($isSuccessful == 0) {
                                        $logMessage = "Failed to insert to Audittrail.";
                                        $logger->log($logger->logdate, "[FORGOTPASSWORD ERROR]: " . $emailCardNumber . " || ", $logMessage);
                                    }
                                    $apiDetails = 'FORGOTPASSWORD-UpdateChangePass-Success: MID = ' . $MID;
                                    $apiLogsModel->insertAPIlogs($apiMethod, $refID, $apiDetails, '', 1);
                                    if ($isInserted == 0) {
                                        $logMessage = "Failed to insert to APILogs.";
                                        $logger->log($logger->logdate, "[FORGOTPASSWORD ERROR]: " . $emailCardNumber . " || ", $logMessage);
                                    }
                                    $isUpdated = $memberSessionsModel->updateTransactionDate($MID);
                                    if ($isUpdated > 0) {
                                        $transMsg = 'Request for change password is successfully processed. Please verify the link sent to your email to reset your password.';
                                        $errorCode = 0;
                                        $data = CommonController::retMsg($module, $errorCode, $transMsg);
                                        $message = "[" . $module . "] Output: " . CJSON::encode($data);
                                        $appLogger->log($appLogger->logdate, "[response]", $message);
                                        $this->_sendResponse(200, CJSON::encode($data));
                                        $logMessage = 'Forgot Password successful.';
                                        $logger->log($logger->logdate, "[FORGOTPASSWORD SUCCESSFUL]: " . $emailCardNumber . " || ", $logMessage);
                                        $apiDetails = 'FORGOTPASSWORD-UpdateTransDate-Success: ' . ' MID = ' . $MID;
                                        $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, $refID, $apiDetails, '', 1);
                                        if ($isInserted == 0) {
                                            $logMessage = "Failed to insert to APILogs.";
                                            $logger->log($logger->logdate, "[FORGOTPASSWORD ERROR]: " . $emailCardNumber . " || ", $logMessage);
                                        }

                                        exit;
                                    } else {
                                        $transMsg = 'Error in updating.';
                                        $logMessage = 'Error in updating.';
                                        $errorCode = 29;
                                        $data = CommonController::retMsg($module, $errorCode, $transMsg);
                                        $message = "[" . $module . "] Output: " . CJSON::encode($data);
                                        $appLogger->log($appLogger->logdate, "[response]", $message);
                                        $this->_sendResponse(200, CJSON::encode($data));
                                        $logger->log($logger->logdate, "[FORGOTPASSWORD ERROR]: " . $emailCardNumber . " || ", $logMessage);
                                        $apiDetails = 'FORGOTPASSWORD-UpdateTransDate-Failed: ' . ' MID = ' . $MID;
                                        $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, $refID, $apiDetails, '', 2);
                                        if ($isInserted == 0) {
                                            $logMessage = "Failed to insert to APILogs.";
                                            $logger->log($logger->logdate, "[FORGOTPASSWORD ERROR]: " . $emailCardNumber . " || ", $logMessage);
                                        }

                                        exit;
                                    }
                                } else {
                                    $transMsg = 'Transaction failed.';
                                    $logMessage = 'Transaction failed.';
                                    $errorCode = 4;
                                    Utilities::log("ReturnMessage: " . $transMsg . " ErrorCode: " . $errorCode);
                                    $data = CommonController::retMsg($module, $errorCode, $transMsg);
                                    $message = "[" . $module . "] Output: " . CJSON::encode($data);
                                    $appLogger->log($appLogger->logdate, "[response]", $message);
                                    $this->_sendResponse(200, CJSON::encode($data));
                                    $logger->log($logger->logdate, "[FORGOTPASSWORD ERROR]: " . $emailCardNumber . " || ", $logMessage);
                                    $apiDetails = 'FORGOTPASSWORD-UpdateChangePass-Failed: MID = ' . $MID;
                                    $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, $refID, $apiDetails, '', 2);
                                    if ($isInserted == 0) {
                                        $logMessage = "Failed to insert to APILogs.";
                                        $logger->log($logger->logdate, "[FORGOTPASSWORD ERROR]: " . $emailCardNumber . " || ", $logMessage);
                                    }

                                    exit;
                                }
                            } else {
                                $transMsg = 'No Email Address found for this user. Please contact Philweb Customer Service Hotline 338-3388.';
                                $logMessage = 'No Email Address found for this user. Please contact Philweb Customer Service Hotline 338-3388.';
                                $errorCode = 12;
                                Utilities::log("ReturnMessage: " . $transMsg . " ErrorCode: " . $errorCode);
                                $data = CommonController::retMsg($module, $errorCode, $transMsg);
                                $message = "[" . $module . "] Output: " . CJSON::encode($data);
                                $appLogger->log($appLogger->logdate, "[response]", $message);
                                $this->_sendResponse(200, CJSON::encode($data));
                                $logger->log($logger->logdate, "[FORGOTPASSWORD ERROR]: " . $emailCardNumber . " || ", $logMessage);
                                $apiDetails = 'FORGOTPASSWORD-Failed: Email not found in db. MID = ' . $MID;
                                $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, $refID, $apiDetails, '', 2);
                                if ($isInserted == 0) {
                                    $logMessage = "Failed to insert to APILogs.";
                                    $logger->log($logger->logdate, "[FORGOTPASSWORD ERROR]: " . $emailCardNumber . " || ", $logMessage);
                                }

                                exit;
                            }
                        } else {
                            $transMsg = $helpers->setErrorMsgForCardStatus($data['Status']);

                            if ($transMsg == 'Membership Card is Inactive') {
                                $transMsg = 'Card is Inactive.';
                                $errorCode = 6;
                            } else if ($transMsg == 'Membership Card is Deactivated') {
                                $transMsg = 'Card is Deactivated.';
                                $errorCode = 11;
                            } else if ($transMsg == 'Membership Card is Newly Migrated') {
                                $transMsg = 'Card is Newly Migrated.';
                                $errorCode = 7;
                            } else if ($transMsg == 'Membership Card is Temporarily Migrated') {
                                $transMsg = 'Card is Temporarily Migrated.';
                                $errorCode = 8;
                            } else if ($transMsg == 'Membership Card is Banned') {
                                $transMsg = 'Card is Banned.';
                                $errorCode = 9;
                            } else {
                                $transMsg = 'Card is Invalid';
                                $errorCode = 10;
                            }

                            $logMessage = $transMsg;
                            Utilities::log("ReturnMessage: " . $transMsg . " ErrorCode: " . $errorCode);
                            $data = CommonController::retMsg($module, $errorCode, $transMsg);
                            $message = "[" . $module . "] Output: " . CJSON::encode($data);
                            $appLogger->log($appLogger->logdate, "[response]", $message);
                            $this->_sendResponse(200, CJSON::encode($data));
                            $logger->log($logger->logdate, "[FORGOTPASSWORD ERROR]: " . $emailCardNumber . " || ", $logMessage);
                            $apiDetails = 'FORGOTPASSWORD-Failed: ' . $transMsg . '.' . 'Status = ' . $data['Status'] . ' MID = ' . $MID;
                            $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, $refID, $apiDetails, '', 2);
                            if ($isInserted == 0) {
                                $logMessage = "Failed to insert to APILogs.";
                                $logger->log($logger->logdate, "[FORGOTPASSWORD ERROR]: " . $emailCardNumber . " || ", $logMessage);
                            }

                            exit;
                        }
                    } else {
                        if ($isCardExist == 0) {
                            $transMsg = "Card is Invalid.";
                            $logMessage = 'Card is Invalid.';
                            $errorCode = 10;
                            Utilities::log("ReturnMessage: " . $transMsg . " ErrorCode: " . $errorCode);
                            $data = CommonController::retMsg($module, $errorCode, $transMsg);
                            $message = "[" . $module . "] Output: " . CJSON::encode($data);
                            $appLogger->log($appLogger->logdate, "[response]", $message);
                            $this->_sendResponse(200, CJSON::encode($data));
                            $logger->log($logger->logdate, "[FORGOTPASSWORD ERROR]: " . $emailCardNumber . " || ", $logMessage);
                            $apiDetails = 'FORGOTPASSWORD-Failed: Membership card is invalid. MID = ' . $MID;
                            $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, $refID, $apiDetails, '', 2);
                            if ($isInserted == 0) {
                                $logMessage = "Failed to insert to APILogs.";
                                $logger->log($logger->logdate, "[FORGOTPASSWORD ERROR]: " . $emailCardNumber . " || ", $logMessage);
                            }

                            exit;
                        } else {
                            $transMsg = "Card is Inactive.";
                            $logMessage = 'Card is Inactive.';
                            $errorCode = 6;
                            Utilities::log("ReturnMessage: " . $transMsg . " ErrorCode: " . $errorCode);
                            $data = CommonController::retMsg($module, $errorCode, $transMsg);
                            $message = "[" . $module . "] Output: " . CJSON::encode($data);
                            $appLogger->log($appLogger->logdate, "[response]", $message);
                            $this->_sendResponse(200, CJSON::encode($data));
                            $logger->log($logger->logdate, "[FORGOTPASSWORD ERROR]: " . $emailCardNumber . " || ", $logMessage);
                            $apiDetails = 'FORGOTPASSWORD-Failed: Membership card is inactive. MID = ' . $MID;
                            $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, $refID, $apiDetails, '', 2);
                            if ($isInserted == 0) {
                                $logMessage = "Failed to insert to APILogs.";
                                $logger->log($logger->logdate, "[FORGOTPASSWORD ERROR]: " . $emailCardNumber . " || ", $logMessage);
                            }

                            exit;
                        }
                    }
                } else {
                    $transMsg = "Invalid input.";
                    $errorCode = 2;
                    Utilities::log("ReturnMessage: " . $transMsg . " ErrorCode: " . $errorCode);
                    $data = CommonController::retMsg($module, $errorCode, $transMsg);
                    $message = "[" . $module . "] Output: " . CJSON::encode($data);
                    $appLogger->log($appLogger->logdate, "[response]", $message);
                    $this->_sendResponse(200, CJSON::encode($data));
                    $logMessage = 'Invalid input.';
                    $logger->log($logger->logdate, "[FORGOTPASSWORD ERROR]: " . $emailCardNumber . " || ", $logMessage);
                    $apiDetails = 'FORGOTPASSWORD-Failed: Invalid card number. MID = ' . $MID;
                    $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, $refID, $apiDetails, '', 2);
                    if ($isInserted == 0) {
                        $logMessage = "Failed to insert to APILogs.";
                        $logger->log($logger->logdate, "[FORGOTPASSWORD ERROR]: " . $emailCardNumber . " || ", $logMessage);
                    }

                    exit;
                }
            }
        } else {
            $transMsg = "One or more fields is not set or is blank.";
            $errorCode = 1;
            Utilities::log("ReturnMessage: " . $transMsg . " ErrorCode: " . $errorCode);
            $data = CommonController::retMsg($module, $errorCode, $transMsg);
            $message = "[" . $module . "] Output: " . CJSON::encode($data);
            $appLogger->log($appLogger->logdate, "[response]", $message);
            $this->_sendResponse(200, CJSON::encode($data));
            $logMessage = 'One or more fields is not set or is blank.';
            $logger->log($logger->logdate, "[FORGOTPASSWORD ERROR]: " . $request['EmailCardNumber'] . " || ", $logMessage);
            $apiDetails = 'FORGOTPASSWORD-Failed: Invalid input parameter.';
            $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, '', $apiDetails, '', 2);
            if ($isInserted == 0) {
                $logMessage = "Failed to insert to APILogs.";
                $logger->log($logger->logdate, "[FORGOTPASSWORD ERROR]: " . $request['EmailCardNumber'] . " || ", $logMessage);
            }

            exit;
        }
    }

    //@date 07-24-2014
    //@purpose member registration
    public function actionRegisterMember() {
        $request = $this->_readJsonRequest();

        $transMsg = '';
        $errorCode = '';
        $module = 'RegisterMember';
        $apiMethod = 3;

        $appLogger = new AppLogger();

        $paramval = CJSON::encode($request);
        $message = "[" . $module . "] Input: " . $paramval;
        $appLogger->log($appLogger->logdate, "[request]", $message);

        $logger = new ErrorLogger();
        $apiLogsModel = new APILogsModel();

        if (isset($request['FirstName']) && isset($request['LastName']) && isset($request['MobileNo']) && isset($request['Password'])
                && isset($request['EmailAddress']) && isset($request['IDNumber']) && isset($request['Birthdate']) && isset($request['IDPresented']) && isset($request['PermanentAdd'])) {
            if (($request['FirstName'] == '') || ($request['LastName'] == '') || ($request['MobileNo'] == '') || ($request['Password'] == '') || ($request['EmailAddress'] == '')
                    || ($request['IDNumber'] == '') || ($request['Birthdate'] == '') || ($request['IDPresented'] == '') || ($request['PermanentAdd'] == '')) {
                $transMsg = "One or more fields is not set or is blank.";
                $errorCode = 1;
                Utilities::log("ReturnMessage: " . $transMsg . " ErrorCode: " . $errorCode);
                $data = CommonController::retMsgRegisterMember($module, $errorCode, $transMsg);
                $message = "[" . $module . "] Output: " . CJSON::encode($data);
                $appLogger->log($appLogger->logdate, "[response]", $message);
                $this->_sendResponse(200, CJSON::encode($data));
                $logMessage = 'One or more fields is not set or is blank.';
                $logger->log($logger->logdate, "[REGISTERMEMBER ERROR]: " . $request['FirstName'] . " || " . $request['LastName'] . " || " . $request['MobileNo'] . " || " . $request['EmailAddress'] . " || " . $request['Birthdate'] . " || ", $logMessage);
                $apiDetails = 'REGISTERMEMBER-Failed: Invalid input parameters. ';
                $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, '', $apiDetails, '', 2);
                if ($isInserted == 0) {
                    $logMessage = "Failed to insert to APILogs.";
                    $logger->log($logger->logdate, "[REGISTERMEMBER ERROR]: " . $request['FirstName'] . " || " . $request['LastName'] . " || " . $request['MobileNo'] . " || " . $request['EmailAddress'] . " || " . $request['Birthdate'] . " || ", $logMessage);
                }

                exit;
            } else if (strlen($request['FirstName']) < 2 || ($request['MiddleName'] != '' && strlen($request['MiddleName']) < 2) || strlen($request['LastName']) < 2) {
                $transMsg = "Name should not be less than 2 characters long.";
                $errorCode = 14;
                Utilities::log("ReturnMessage: " . $transMsg . " ErrorCode: " . $errorCode);
                $data = CommonController::retMsgRegisterMember($module, $errorCode, $transMsg);
                $message = "[" . $module . "] Output: " . CJSON::encode($data);
                $appLogger->log($appLogger->logdate, "[response]", $message);
                $this->_sendResponse(200, CJSON::encode($data));
                $logMessage = 'Name should not be less than 2 characters long.';
                $logger->log($logger->logdate, "[REGISTERMEMBER ERROR]: " . $request['FirstName'] . " || " . $request['LastName'] . " || " . $request['MobileNo'] . " || " . $request['EmailAddress'] . " || " . $request['Birthdate'] . " || ", $logMessage);
                $apiDetails = 'REGISTERMEMBER-Failed: Invalid input parameters. ';
                $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, '', $apiDetails, '', 2);
                if ($isInserted == 0) {
                    $logMessage = "Failed to insert to APILogs.";
                    $logger->log($logger->logdate, "[REGISTERMEMBER ERROR]: " . $request['FirstName'] . " || " . $request['LastName'] . " || " . $request['MobileNo'] . " || " . $request['EmailAddress'] . " || " . $request['Birthdate'] . " || ", $logMessage);
                }

                exit;
            } else if (preg_match("/^[A-Za-z\s]+$/", trim($request['FirstName'])) == 0 || (trim($request['MiddleName'] != '') && preg_match("/^[A-Za-z\s]+$/", trim($request['MiddleName'])) == 0) || preg_match("/^[A-Za-z\s]+$/", trim($request['LastName'])) == 0 || (trim($request['NickName'] != '') && preg_match("/^[A-Za-z\s]+$/", trim($request['NickName'])) == 0)) {
                $transMsg = "Name should consist of letters and spaces only.";
                $errorCode = 17;
                Utilities::log("ReturnMessage: " . $transMsg . " ErrorCode: " . $errorCode);
                $data = CommonController::retMsgRegisterMember($module, $errorCode, $transMsg);
                $message = "[" . $module . "] Output: " . CJSON::encode($data);
                $appLogger->log($appLogger->logdate, "[response]", $message);
                $this->_sendResponse(200, CJSON::encode($data));
                $logMessage = 'Name should consist of letters and spaces only.';
                $logger->log($logger->logdate, "[REGISTERMEMBER ERROR]: " . $request['FirstName'] . " || " . $request['LastName'] . " || " . $request['MobileNo'] . " || " . $request['EmailAddress'] . " || " . $request['Birthdate'] . " || ", $logMessage);
                $apiDetails = 'REGISTERMEMBER-Failed: Invalid input parameters. ';
                $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, '', $apiDetails, '', 2);
                if ($isInserted == 0) {
                    $logMessage = "Failed to insert to APILogs.";
                    $logger->log($logger->logdate, "[REGISTERMEMBER ERROR]: " . $request['FirstName'] . " || " . $request['LastName'] . " || " . $request['MobileNo'] . " || " . $request['EmailAddress'] . " || " . $request['Birthdate'] . " || ", $logMessage);
                }

                exit;
            } else if (ctype_alnum($request['Password']) == FALSE || ctype_alnum($request['IDNumber']) == FALSE) {
                $transMsg = "Password and ID Number should consist of letters and numbers only.";
                $errorCode = 18;
                Utilities::log("ReturnMessage: " . $transMsg . " ErrorCode: " . $errorCode);
                $data = CommonController::retMsgRegisterMember($module, $errorCode, $transMsg);
                $message = "[" . $module . "] Output: " . CJSON::encode($data);
                $appLogger->log($appLogger->logdate, "[response]", $message);
                $this->_sendResponse(200, CJSON::encode($data));
                $logMessage = 'Password and ID Number should consist of letters and numbers only.';
                $logger->log($logger->logdate, "[REGISTERMEMBER ERROR]: " . $request['FirstName'] . " || " . $request['LastName'] . " || " . $request['MobileNo'] . " || " . $request['EmailAddress'] . " || " . $request['Birthdate'] . " || ", $logMessage);
                $apiDetails = 'REGISTERMEMBER-Failed: Invalid input parameters. ';
                $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, '', $apiDetails, '', 2);
                if ($isInserted == 0) {
                    $logMessage = "Failed to insert to APILogs.";
                    $logger->log($logger->logdate, "[REGISTERMEMBER ERROR]: " . $request['FirstName'] . " || " . $request['LastName'] . " || " . $request['MobileNo'] . " || " . $request['EmailAddress'] . " || " . $request['Birthdate'] . " || ", $logMessage);
                }

                exit;
            } else if ($request['Password'] != '' && strlen($request['Password']) < 5) {
                $transMsg = "Password should not be less than 5 characters long.";
                $errorCode = 19;
                Utilities::log("ReturnMessage: " . $transMsg . " ErrorCode: " . $errorCode);
                $data = CommonController::retMsgRegisterMember($module, $errorCode, $transMsg);
                $message = "[" . $module . "] Output: " . CJSON::encode($data);
                $appLogger->log($appLogger->logdate, "[response]", $message);
                $this->_sendResponse(200, CJSON::encode($data));
                $logMessage = 'Password should not be less than 5 characters long.';
                $logger->log($logger->logdate, "[REGISTERMEMBER ERROR]: " . $request['FirstName'] . " || " . $request['LastName'] . " || " . $request['MobileNo'] . " || " . $request['EmailAddress'] . " || " . $request['Birthdate'] . " || ", $logMessage);
                $apiDetails = 'REGISTERMEMBER-Failed: Invalid input parameters. ';
                $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, '', $apiDetails, '', 2);
                if ($isInserted == 0) {
                    $logMessage = "Failed to insert to APILogs.";
                    $logger->log($logger->logdate, "[REGISTERMEMBER ERROR]: " . $request['FirstName'] . " || " . $request['LastName'] . " || " . $request['MobileNo'] . " || " . $request['EmailAddress'] . " || " . $request['Birthdate'] . " || ", $logMessage);
                }

                exit;
            } else if ((substr($request['MobileNo'], 0, 3) == "639" && strlen($request['MobileNo']) != 12) || (substr($request['MobileNo'], 0, 2) == "09" && strlen($request['MobileNo']) != 11)) {
                $transMsg = "Invalid Mobile Number.";
                $errorCode = 97;
                Utilities::log("ReturnMessage: " . $transMsg . " ErrorCode: " . $errorCode);
                $data = CommonController::retMsgRegisterMember($module, $errorCode, $transMsg);
                $message = "[" . $module . "] Output: " . CJSON::encode($data);
                $appLogger->log($appLogger->logdate, "[response]", $message);
                $this->_sendResponse(200, CJSON::encode($data));
                $logMessage = 'Invalid Mobile Number.';
                $logger->log($logger->logdate, "[REGISTERMEMBER ERROR]: " . $request['FirstName'] . " || " . $request['LastName'] . " || " . $request['MobileNo'] . " || " . $request['EmailAddress'] . " || " . $request['Birthdate'] . " || ", $logMessage);
                $apiDetails = 'REGISTERMEMBER-Failed: Invalid input parameters. ';
                $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, '', $apiDetails, '', 2);
                if ($isInserted == 0) {
                    $logMessage = "Failed to insert to APILogs.";
                    $logger->log($logger->logdate, "[REGISTERMEMBER ERROR]: " . $request['FirstName'] . " || " . $request['LastName'] . " || " . $request['MobileNo'] . " || " . $request['EmailAddress'] . " || " . $request['Birthdate'] . " || ", $logMessage);
                }

                exit;
            } else if ((substr($request['AlternateMobileNo'], 0, 3) == "639" && strlen($request['AlternateMobileNo']) != 12) || (substr($request['AlternateMobileNo'], 0, 2) == "09" && strlen($request['AlternateMobileNo']) != 11)) {
                $transMsg = "Invalid Mobile Number.";
                $errorCode = 97;
                Utilities::log("ReturnMessage: " . $transMsg . " ErrorCode: " . $errorCode);
                $data = CommonController::retMsgRegisterMember($module, $errorCode, $transMsg);
                $message = "[" . $module . "] Output: " . CJSON::encode($data);
                $appLogger->log($appLogger->logdate, "[response]", $message);
                $this->_sendResponse(200, CJSON::encode($data));
                $logMessage = 'Invalid Mobile Number.';
                $logger->log($logger->logdate, "[REGISTERMEMBER ERROR]: " . $request['FirstName'] . " || " . $request['LastName'] . " || " . $request['MobileNo'] . " || " . $request['EmailAddress'] . " || " . $request['Birthdate'] . " || ", $logMessage);
                $apiDetails = 'REGISTERMEMBER-Failed: Invalid input parameters. ';
                $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, '', $apiDetails, '', 2);
                if ($isInserted == 0) {
                    $logMessage = "Failed to insert to APILogs.";
                    $logger->log($logger->logdate, "[REGISTERMEMBER ERROR]: " . $request['FirstName'] . " || " . $request['LastName'] . " || " . $request['MobileNo'] . " || " . $request['EmailAddress'] . " || " . $request['Birthdate'] . " || ", $logMessage);
                }

                exit;
            } else if ((substr($request['MobileNo'], 0, 2) != '09' && substr($request['MobileNo'], 0, 3) != '639') && (substr($request['AlternateMobileNo'], 0, 2) != '09' && substr($request['AlternateMobileNo'], 0, 3) != '639')) {
                $transMsg = "Mobile number should begin with either '09' or '639'.";
                $errorCode = 69;
                Utilities::log("ReturnMessage: " . $transMsg . " ErrorCode: " . $errorCode);
                $data = CommonController::retMsgRegisterMember($module, $errorCode, $transMsg);
                $message = "[" . $module . "] Output: " . CJSON::encode($data);
                $appLogger->log($appLogger->logdate, "[response]", $message);
                $this->_sendResponse(200, CJSON::encode($data));
                $logMessage = "Mobile number should begin with either '09' or '639'.";
                $logger->log($logger->logdate, "[REGISTERMEMBER ERROR]: " . $request['FirstName'] . " || " . $request['LastName'] . " || " . $request['MobileNo'] . " || " . $request['EmailAddress'] . " || " . $request['Birthdate'] . " || ", $logMessage);
                $apiDetails = 'REGISTERMEMBER-Failed: Invalid input parameters. ';
                $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, '', $apiDetails, '', 2);
                if ($isInserted == 0) {
                    $logMessage = "Failed to insert to APILogs.";
                    $logger->log($logger->logdate, "[REGISTERMEMBER ERROR]: " . $request['FirstName'] . " || " . $request['LastName'] . " || " . $request['MobileNo'] . " || " . $request['EmailAddress'] . " || " . $request['Birthdate'] . " || ", $logMessage);
                }

                exit;
            } else if (!is_numeric($request['MobileNo']) || ($request['AlternateMobileNo'] != '' && !is_numeric($request['AlternateMobileNo']))) {
                $transMsg = "Mobile number should consist of numbers only.";
                $errorCode = 16;
                Utilities::log("ReturnMessage: " . $transMsg . " ErrorCode: " . $errorCode);
                $data = CommonController::retMsgRegisterMember($module, $errorCode, $transMsg);
                $message = "[" . $module . "] Output: " . CJSON::encode($data);
                $appLogger->log($appLogger->logdate, "[response]", $message);
                $this->_sendResponse(200, CJSON::encode($data));
                $logMessage = 'Mobile number should consist of numbers only.';
                $logger->log($logger->logdate, "[REGISTERMEMBER ERROR]: " . $request['FirstName'] . " || " . $request['LastName'] . " || " . $request['MobileNo'] . " || " . $request['EmailAddress'] . " || " . $request['Birthdate'] . " || ", $logMessage);
                $apiDetails = 'REGISTERMEMBER-Failed: Invalid input parameters. ';
                $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, '', $apiDetails, '', 2);
                if ($isInserted == 0) {
                    $logMessage = "Failed to insert to APILogs.";
                    $logger->log($logger->logdate, "[REGISTERMEMBER ERROR]: " . $request['FirstName'] . " || " . $request['LastName'] . " || " . $request['MobileNo'] . " || " . $request['EmailAddress'] . " || " . $request['Birthdate'] . " || ", $logMessage);
                }

                exit;
            } else if ($request['MobileNo'] == $request['AlternateMobileNo']) {
                $transMsg = "Mobile number should not be the same as alternate mobile number.";
                $errorCode = 86;
                Utilities::log("ReturnMessage: " . $transMsg . " ErrorCode: " . $errorCode);
                $data = CommonController::retMsgRegisterMember($module, $errorCode, $transMsg);
                $message = "[" . $module . "] Output: " . CJSON::encode($data);
                $appLogger->log($appLogger->logdate, "[response]", $message);
                $this->_sendResponse(200, CJSON::encode($data));
                $logMessage = 'Mobile number should not be the same as alternate mobile number.';
                $logger->log($logger->logdate, "[REGISTERMEMBER ERROR]: " . $request['FirstName'] . " || " . $request['LastName'] . " || " . $request['MobileNo'] . " || " . $request['EmailAddress'] . " || " . $request['Birthdate'] . " || ", $logMessage);
                $apiDetails = 'REGISTERMEMBER-Failed: Invalid input parameters. ';
                $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, '', $apiDetails, '', 2);
                if ($isInserted == 0) {
                    $logMessage = "Failed to insert to APILogs.";
                    $logger->log($logger->logdate, "[REGISTERMEMBER ERROR]: " . $request['FirstName'] . " || " . $request['LastName'] . " || " . $request['MobileNo'] . " || " . $request['EmailAddress'] . " || " . $request['Birthdate'] . " || ", $logMessage);
                }

                exit;
            } else if (!Utilities::validateEmail($request['EmailAddress']) || ($request['AlternateEmail'] != '' && !Utilities::validateEmail($request['AlternateEmail']))) {
                $transMsg = "Invalid Email Address.";
                $errorCode = 5;
                Utilities::log("ReturnMessage: " . $transMsg . " ErrorCode: " . $errorCode);
                $data = CommonController::retMsgRegisterMember($module, $errorCode, $transMsg);
                $message = "[" . $module . "] Output: " . CJSON::encode($data);
                $appLogger->log($appLogger->logdate, "[response]", $message);
                $this->_sendResponse(200, CJSON::encode($data));
                $logMessage = 'Invalid Email Address.';
                $logger->log($logger->logdate, "[REGISTERMEMBER ERROR]: " . $request['FirstName'] . " || " . $request['LastName'] . " || " . $request['MobileNo'] . " || " . $request['EmailAddress'] . " || " . $request['Birthdate'] . " || ", $logMessage);
                $apiDetails = 'REGISTERMEMBER-Failed: Invalid input parameters. ';
                $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, '', $apiDetails, '', 2);
                if ($isInserted == 0) {
                    $logMessage = "Failed to insert to APILogs.";
                    $logger->log($logger->logdate, "[REGISTERMEMBER ERROR]: " . $request['FirstName'] . " || " . $request['LastName'] . " || " . $request['MobileNo'] . " || " . $request['EmailAddress'] . " || " . $request['Birthdate'] . " || ", $logMessage);
                }

                exit;
            } else if ($request['EmailAddress'] == $request['AlternateEmail']) {
                $transMsg = "Email Address should not be the same as alternate email.";
                $errorCode = 87;
                Utilities::log("ReturnMessage: " . $transMsg . " ErrorCode: " . $errorCode);
                $data = CommonController::retMsgRegisterMember($module, $errorCode, $transMsg);
                $message = "[" . $module . "] Output: " . CJSON::encode($data);
                $appLogger->log($appLogger->logdate, "[response]", $message);
                $this->_sendResponse(200, CJSON::encode($data));
                $logMessage = 'Email Address should not be the same as alternate email.';
                $logger->log($logger->logdate, "[REGISTERMEMBER ERROR]: " . $request['FirstName'] . " || " . $request['LastName'] . " || " . $request['MobileNo'] . " || " . $request['EmailAddress'] . " || " . $request['Birthdate'] . " || ", $logMessage);
                $apiDetails = 'REGISTERMEMBER-Failed: Invalid input parameters. ';
                $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, '', $apiDetails, '', 2);
                if ($isInserted == 0) {
                    $logMessage = "Failed to insert to APILogs.";
                    $logger->log($logger->logdate, "[REGISTERMEMBER ERROR]: " . $request['FirstName'] . " || " . $request['LastName'] . " || " . $request['MobileNo'] . " || " . $request['EmailAddress'] . " || " . $request['Birthdate'] . " || ", $logMessage);
                }

                exit;
            } else if ($request['Gender'] != '' && $request['Gender'] != 1 && $request['Gender'] != 2) {
                $transMsg = "Please input 1 for male or 2 for female.";
                $errorCode = 77;
                Utilities::log("ReturnMessage: " . $transMsg . " ErrorCode: " . $errorCode);
                $data = CommonController::retMsgRegisterMember($module, $errorCode, $transMsg);
                $message = "[" . $module . "] Output: " . CJSON::encode($data);
                $appLogger->log($appLogger->logdate, "[response]", $message);
                $this->_sendResponse(200, CJSON::encode($data));
                $logMessage = 'Please input 1 for male or 2 for female.';
                $logger->log($logger->logdate, "[REGISTERMEMBER ERROR]: " . $request['FirstName'] . " || " . $request['LastName'] . " || " . $request['MobileNo'] . " || " . $request['EmailAddress'] . " || " . $request['Birthdate'] . " || ", $logMessage);
                $apiDetails = 'REGISTERMEMBER-Failed: Invalid input parameters. ';
                $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, '', $apiDetails, '', 2);
                if ($isInserted == 0) {
                    $logMessage = "Failed to insert to APILogs.";
                    $logger->log($logger->logdate, "[REGISTERMEMBER ERROR]: " . $request['FirstName'] . " || " . $request['LastName'] . " || " . $request['MobileNo'] . " || " . $request['EmailAddress'] . " || " . $request['Birthdate'] . " || ", $logMessage);
                }

                exit;
            } else if ($request['IDPresented'] != 1 && $request['IDPresented'] != 2 && $request['IDPresented'] != 3 && $request['IDPresented'] != 4 && $request['IDPresented'] != 5 && $request['IDPresented'] != 6 && $request['IDPresented'] != 7 && $request['IDPresented'] != 8 && $request['IDPresented'] != 9) {
                $transMsg = "Please input a valid ID Presented (1 to 9).";
                $errorCode = 78;
                Utilities::log("ReturnMessage: " . $transMsg . " ErrorCode: " . $errorCode);
                $data = CommonController::retMsgRegisterMember($module, $errorCode, $transMsg);
                $message = "[" . $module . "] Output: " . CJSON::encode($data);
                $appLogger->log($appLogger->logdate, "[response]", $message);
                $this->_sendResponse(200, CJSON::encode($data));
                $logMessage = 'Please input a valid ID Presented (1 to 9).';
                $logger->log($logger->logdate, "[REGISTERMEMBER ERROR]: " . $request['FirstName'] . " || " . $request['LastName'] . " || " . $request['MobileNo'] . " || " . $request['EmailAddress'] . " || " . $request['Birthdate'] . " || ", $logMessage);
                $apiDetails = 'REGISTERMEMBER-Failed: Invalid input parameters. ';
                $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, '', $apiDetails, '', 2);
                if ($isInserted == 0) {
                    $logMessage = "Failed to insert to APILogs.";
                    $logger->log($logger->logdate, "[REGISTERMEMBER ERROR]: " . $request['FirstName'] . " || " . $request['LastName'] . " || " . $request['MobileNo'] . " || " . $request['EmailAddress'] . " || " . $request['Birthdate'] . " || ", $logMessage);
                }

                exit;
            } else if ($request['Nationality'] != '' && $request['Nationality'] != 1 && $request['Nationality'] != 2 && $request['Nationality'] != 3 && $request['Nationality'] != 4 && $request['Nationality'] != 5 && $request['Nationality'] != 6) {
                $transMsg = "Please input a valid Nationality (1 to 6).";
                $errorCode = 79;
                Utilities::log("ReturnMessage: " . $transMsg . " ErrorCode: " . $errorCode);
                $data = CommonController::retMsgRegisterMember($module, $errorCode, $transMsg);
                $message = "[" . $module . "] Output: " . CJSON::encode($data);
                $appLogger->log($appLogger->logdate, "[response]", $message);
                $this->_sendResponse(200, CJSON::encode($data));
                $logMessage = 'Please input a valid Nationality (1 to 6).';
                $logger->log($logger->logdate, "[REGISTERMEMBER ERROR]: " . $request['FirstName'] . " || " . $request['LastName'] . " || " . $request['MobileNo'] . " || " . $request['EmailAddress'] . " || " . $request['Birthdate'] . " || ", $logMessage);
                $apiDetails = 'REGISTERMEMBER-Failed: Invalid input parameters. ';
                $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, '', $apiDetails, '', 2);
                if ($isInserted == 0) {
                    $logMessage = "Failed to insert to APILogs.";
                    $logger->log($logger->logdate, "[REGISTERMEMBER ERROR]: " . $request['FirstName'] . " || " . $request['LastName'] . " || " . $request['MobileNo'] . " || " . $request['EmailAddress'] . " || " . $request['Birthdate'] . " || ", $logMessage);
                }

                exit;
            } else if ($this->_validateDate($request['Birthdate']) == FALSE) {
                $transMsg = "Please input a valid Date (YYYY-MM-DD).";
                $errorCode = 80;
                Utilities::log("ReturnMessage: " . $transMsg . " ErrorCode: " . $errorCode);
                $data = CommonController::retMsgRegisterMember($module, $errorCode, $transMsg);
                $message = "[" . $module . "] Output: " . CJSON::encode($data);
                $appLogger->log($appLogger->logdate, "[response]", $message);
                $this->_sendResponse(200, CJSON::encode($data));
                $logMessage = 'Please input a valid Date.';
                $logger->log($logger->logdate, "[REGISTERMEMBER ERROR]: " . $request['FirstName'] . " || " . $request['LastName'] . " || " . $request['MobileNo'] . " || " . $request['EmailAddress'] . " || " . $request['Birthdate'] . " || ", $logMessage);
                $apiDetails = 'REGISTERMEMBER-Failed: Invalid input parameters. ';
                $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, '', $apiDetails, '', 2);
                if ($isInserted == 0) {
                    $logMessage = "Failed to insert to APILogs.";
                    $logger->log($logger->logdate, "[REGISTERMEMBER ERROR]: " . $request['FirstName'] . " || " . $request['LastName'] . " || " . $request['MobileNo'] . " || " . $request['EmailAddress'] . " || " . $request['Birthdate'] . " || ", $logMessage);
                }

                exit;
            } else if ($request['Occupation'] != '' && $request['Occupation'] != 1 && $request['Occupation'] != 2 && $request['Occupation'] != 3 && $request['Occupation'] != 4 && $request['Occupation'] != 5) {
                $transMsg = "Please input a valid Occupation (1 to 5).";
                $errorCode = 81;
                Utilities::log("ReturnMessage: " . $transMsg . " ErrorCode: " . $errorCode);
                $data = CommonController::retMsgRegisterMember($module, $errorCode, $transMsg);
                $message = "[" . $module . "] Output: " . CJSON::encode($data);
                $appLogger->log($appLogger->logdate, "[response]", $message);
                $this->_sendResponse(200, CJSON::encode($data));
                $logMessage = 'Please input a valid Occupation (1 to 5).';
                $logger->log($logger->logdate, "[REGISTERMEMBER ERROR]: " . $request['FirstName'] . " || " . $request['LastName'] . " || " . $request['MobileNo'] . " || " . $request['EmailAddress'] . " || " . $request['Birthdate'] . " || ", $logMessage);
                $apiDetails = 'REGISTERMEMBER-Failed: Invalid input parameters. ';
                $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, '', $apiDetails, '', 2);
                if ($isInserted == 0) {
                    $logMessage = "Failed to insert to APILogs.";
                    $logger->log($logger->logdate, "[REGISTERMEMBER ERROR]: " . $request['FirstName'] . " || " . $request['LastName'] . " || " . $request['MobileNo'] . " || " . $request['EmailAddress'] . " || " . $request['Birthdate'] . " || ", $logMessage);
                }

                exit;
            } else if ($request['IsSmoker'] != '' && $request['IsSmoker'] != 1 && $request['IsSmoker'] != 2) {
                $transMsg = "Please input 1 for smoker or 2 for non-smoker.";
                $errorCode = 82;
                Utilities::log("ReturnMessage: " . $transMsg . " ErrorCode: " . $errorCode);
                $data = CommonController::retMsgRegisterMember($module, $errorCode, $transMsg);
                $message = "[" . $module . "] Output: " . CJSON::encode($data);
                $appLogger->log($appLogger->logdate, "[response]", $message);
                $this->_sendResponse(200, CJSON::encode($data));
                $logMessage = 'Please input 1 for smoker or 2 for non-smoker.';
                $logger->log($logger->logdate, "[REGISTERMEMBER ERROR]: " . $request['FirstName'] . " || " . $request['LastName'] . " || " . $request['MobileNo'] . " || " . $request['EmailAddress'] . " || " . $request['Birthdate'] . " || ", $logMessage);
                $apiDetails = 'REGISTERMEMBER-Failed: Invalid input parameters. ';
                $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, '', $apiDetails, '', 2);
                if ($isInserted == 0) {
                    $logMessage = "Failed to insert to APILogs.";
                    $logger->log($logger->logdate, "[REGISTERMEMBER ERROR]: " . $request['FirstName'] . " || " . $request['LastName'] . " || " . $request['MobileNo'] . " || " . $request['EmailAddress'] . " || " . $request['Birthdate'] . " || ", $logMessage);
                }

                exit;
            } else if ($request['ReferrerID'] != '' && $request['ReferrerID'] != 1 && $request['ReferrerID'] != 2 && $request['ReferrerID'] != 3 && $request['ReferrerID'] != 4 && $request['ReferrerID'] != 5 && $request['ReferrerID'] != 6 && $request['ReferrerID'] != 7 && $request['ReferrerID'] != 8 && $request['ReferrerID'] != 9 && $request['ReferrerID'] != 10) {
                $transMsg = "Please input a valid Referrer ID (1 to 10).";
                $errorCode = 83;
                Utilities::log("ReturnMessage: " . $transMsg . " ErrorCode: " . $errorCode);
                $data = CommonController::retMsgRegisterMember($module, $errorCode, $transMsg);
                $message = "[" . $module . "] Output: " . CJSON::encode($data);
                $appLogger->log($appLogger->logdate, "[response]", $message);
                $this->_sendResponse(200, CJSON::encode($data));
                $logMessage = 'Please input a valid Referrer ID (1 to 10).';
                $logger->log($logger->logdate, "[REGISTERMEMBER ERROR]: " . $request['FirstName'] . " || " . $request['LastName'] . " || " . $request['MobileNo'] . " || " . $request['EmailAddress'] . " || " . $request['Birthdate'] . " || ", $logMessage);
                $apiDetails = 'REGISTERMEMBER-Failed: Invalid input parameters. ';
                $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, '', $apiDetails, '', 2);
                if ($isInserted == 0) {
                    $logMessage = "Failed to insert to APILogs.";
                    $logger->log($logger->logdate, "[REGISTERMEMBER ERROR]: " . $request['FirstName'] . " || " . $request['LastName'] . " || " . $request['MobileNo'] . " || " . $request['EmailAddress'] . " || " . $request['Birthdate'] . " || ", $logMessage);
                }

                exit;
            } else if ($request['EmailSubscription'] != '' && $request['EmailSubscription'] != '0' && $request['EmailSubscription'] != '1') {
                $transMsg = "Please input 0 for email non-subscription else input 1.";
                $errorCode = 84;
                Utilities::log("ReturnMessage: " . $transMsg . " ErrorCode: " . $errorCode);
                $data = CommonController::retMsgRegisterMember($module, $errorCode, $transMsg);
                $message = "[" . $module . "] Output: " . CJSON::encode($data);
                $appLogger->log($appLogger->logdate, "[response]", $message);
                $this->_sendResponse(200, CJSON::encode($data));
                $logMessage = 'Please input 0 for email non-subscription else input 1.';
                $logger->log($logger->logdate, "[REGISTERMEMBER ERROR]: " . $request['FirstName'] . " || " . $request['LastName'] . " || " . $request['MobileNo'] . " || " . $request['EmailAddress'] . " || " . $request['Birthdate'] . " || ", $logMessage);
                $apiDetails = 'REGISTERMEMBER-Failed: Invalid input parameters. ';
                $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, '', $apiDetails, '', 2);
                if ($isInserted == 0) {
                    $logMessage = "Failed to insert to APILogs.";
                    $logger->log($logger->logdate, "[REGISTERMEMBER ERROR]: " . $request['FirstName'] . " || " . $request['LastName'] . " || " . $request['MobileNo'] . " || " . $request['EmailAddress'] . " || " . $request['Birthdate'] . " || ", $logMessage);
                }

                exit;
            } else if ($request['SMSSubscription'] != '' && $request['SMSSubscription'] != '0' && $request['SMSSubscription'] != '1') {
                $transMsg = "Please input 0 for sms non-subscription else input 1.";
                $errorCode = 85;
                Utilities::log("ReturnMessage: " . $transMsg . " ErrorCode: " . $errorCode);
                $data = CommonController::retMsgRegisterMember($module, $errorCode, $transMsg);
                $message = "[" . $module . "] Output: " . CJSON::encode($data);
                $appLogger->log($appLogger->logdate, "[response]", $message);
                $this->_sendResponse(200, CJSON::encode($data));
                $logMessage = 'Please input 0 for sms non-subscription else input 1.';
                $logger->log($logger->logdate, "[REGISTERMEMBER ERROR]: " . $request['FirstName'] . " || " . $request['LastName'] . " || " . $request['MobileNo'] . " || " . $request['EmailAddress'] . " || " . $request['Birthdate'] . " || ", $logMessage);
                $apiDetails = 'REGISTERMEMBER-Failed: Invalid input parameters. ';
                $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, '', $apiDetails, '', 2);
                if ($isInserted == 0) {
                    $logMessage = "Failed to insert to APILogs.";
                    $logger->log($logger->logdate, "[REGISTERMEMBER ERROR]: " . $request['FirstName'] . " || " . $request['LastName'] . " || " . $request['MobileNo'] . " || " . $request['EmailAddress'] . " || " . $request['Birthdate'] . " || ", $logMessage);
                }

                exit;
            } else {
                //start of declaration of models to be used
                $memberInfoModel = new MemberInfoModel();
                $memberCardsModel = new MemberCardsModel();
                $memberSessionsModel = new MemberSessionsModel();
                $membershipTempModel = new MembershipTempModel();
                $membersModel = new MembersModel();
                $logger = new ErrorLogger();
                $auditTrailModel = new AuditTrailModel();
                $smsRequestLogsModel = new SMSRequestLogsModel();
                $ref_SMSApiMethodsModel = new Ref_SMSApiMethodsModel();
                $blackListsModel = new BlackListsModel();

                $emailAddress = trim($request['EmailAddress']);
                $firstname = trim($request['FirstName']);
                $firstname = str_replace(" ", "", $firstname);
                $middlename = trim($request['MiddleName']);
                if ($middlename == '')
                    $middlename = '';
                $lastname = trim($request['LastName']);
                $lastname = str_replace(" ", "", $lastname);
                $nickname = trim($request['NickName']);
                if ($nickname == '')
                    $nickname = '';
                $password = md5(trim($request['Password']));
                $permanentAddress = trim($request['PermanentAdd']);
                $mobileNumber = trim($request['MobileNo']);
                $alternateMobileNumber = trim($request['AlternateMobileNo']);
                if ($alternateMobileNumber == '')
                    $alternateMobileNumber = '';
                $alternateEmail = trim($request['AlternateEmail']);
                if ($alternateEmail == '')
                    $alternateEmail = '';
                $idNumber = trim($request['IDNumber']);
                $idPresented = trim($request['IDPresented']);
                $gender = trim($request['Gender']);
                if ($gender == '')
                    $gender = '';
                $birthdate = trim($request['Birthdate']);

                $tz = new DateTimeZone("Asia/Manila");
                $age = DateTime::createFromFormat('Y-m-d', $birthdate, $tz)->diff(new DateTime('now', $tz))->y;
                $nationalityID = trim($request['Nationality']);
                if ($nationalityID == '')
                    $nationalityID = '';
                $occupationID = trim($request['Occupation']);
                if ($occupationID == '')
                    $occupationID = '';
                $isSmoker = trim($request['IsSmoker']);
                if ($isSmoker == '')
                    $isSmoker = '';
                $referrerID = trim($request['ReferrerID']);
                if ($referrerID == '')
                    $referrerID = '';
                $referralCode = trim($request['ReferralCode']);
                if ($referralCode == '')
                    $referralCode = '';
                $emailSubscription = trim($request['EmailSubscription']);
                if ($emailSubscription == '')
                    $emailSubscription == '';
                $smsSubscription = trim($request['SMSSubscription']);
                if ($smsSubscription == '')
                    $smsSubscription == '';
                $refID = $firstname . ' ' . $lastname;

                //check if member is blacklisted
                $isBlackListed = $blackListsModel->checkIfBlackListed($firstname, $lastname, $birthdate, 3);
                //check if email is active and existing in live membership db
                $activeEmail = $memberInfoModel->checkIfActiveVerifiedEmail($emailAddress);

                if ($activeEmail['COUNT(MID)'] > 0) {
                    $transMsg = "Sorry, " . $emailAddress . " already belongs to an existing account. Please enter another email address.";
                    $errorCode = 21;
                    Utilities::log("ReturnMessage: " . $transMsg . " ErrorCode: " . $errorCode);
                    $data = CommonController::retMsgRegisterMember($module, $errorCode, $transMsg);
                    $message = "[" . $module . "] Output: " . CJSON::encode($data);
                    $appLogger->log($appLogger->logdate, "[response]", $message);
                    $this->_sendResponse(200, CJSON::encode($data));
                    $logMessage = 'Sorry, ' . $emailAddress . ' already belongs to an existing account. Please enter another email address.';
                    $logger->log($logger->logdate, "[REGISTERMEMBER ERROR]: " . $request['FirstName'] . " || " . $request['LastName'] . " || " . $request['MobileNo'] . " || " . $request['EmailAddress'] . " || " . $request['Birthdate'] . " || ", $logMessage);
                    $apiDetails = 'REGISTERMEMBER-Failed: Email is already used. EmailAddress = ' . $emailAddress;
                    $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, $refID, $apiDetails, '', 2);
                    if ($isInserted == 0) {
                        $logMessage = "Failed to insert to APILogs.";
                        $logger->log($logger->logdate, "[REGISTERMEMBER ERROR]: " . $request['FirstName'] . " || " . $request['LastName'] . " || " . $request['MobileNo'] . " || " . $request['EmailAddress'] . " || " . $request['Birthdate'] . " || ", $logMessage);
                    }

                    exit;
                } else if ($isBlackListed['Count'] > 0) {
                    $transMsg = "Registration cannot proceed. Please contact Customer Service.";
                    $errorCode = 22;
                    Utilities::log("ReturnMessage: " . $transMsg . " ErrorCode: " . $errorCode);
                    $data = CommonController::retMsgRegisterMember($module, $errorCode, $transMsg);
                    $message = "[" . $module . "] Output: " . CJSON::encode($data);
                    $appLogger->log($appLogger->logdate, "[response]", $message);
                    $this->_sendResponse(200, CJSON::encode($data));
                    $logMessage = 'Registration cannot proceed. Please contact Customer Service.';
                    $logger->log($logger->logdate, "[REGISTERMEMBER ERROR]: " . $request['FirstName'] . " || " . $request['LastName'] . " || " . $request['MobileNo'] . " || " . $request['EmailAddress'] . " || " . $request['Birthdate'] . " || ", $logMessage);
                    $apiDetails = 'REGISTERMEMBER-Failed: Player is blacklisted. Name = ' . $firstname . ' ' . $lastname . ', Birthdate = ' . $birthdate;
                    $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, $refID, $apiDetails, '', 2);
                    if ($isInserted == 0) {
                        $logMessage = "Failed to insert to APILogs.";
                        $logger->log($logger->logdate, "[REGISTERMEMBER ERROR]: " . $request['FirstName'] . " || " . $request['LastName'] . " || " . $request['MobileNo'] . " || " . $request['EmailAddress'] . " || " . $request['Birthdate'] . " || ", $logMessage);
                    }

                    exit;
                } else if ($age < 21) {
                    $transMsg = "Must be at least 21 years old to register.";
                    $errorCode = 89;
                    Utilities::log("ReturnMessage: " . $transMsg . " ErrorCode: " . $errorCode);
                    $data = CommonController::retMsgRegisterMember($module, $errorCode, $transMsg);
                    $message = "[" . $module . "] Output: " . CJSON::encode($data);
                    $appLogger->log($appLogger->logdate, "[response]", $message);
                    $this->_sendResponse(200, CJSON::encode($data));
                    $logMessage = 'Must be at least 21 years old to register.';
                    $logger->log($logger->logdate, "[REGISTERMEMBER ERROR]: " . $request['FirstName'] . " || " . $request['LastName'] . " || " . $request['MobileNo'] . " || " . $request['EmailAddress'] . " || " . $request['Birthdate'] . " || ", $logMessage);
                    $apiDetails = 'REGISTERMEMBER-Failed: Player is under 21. Name = ' . $firstname . ' ' . $lastname . ', Birthdate = ' . $birthdate;
                    $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, $refID, $apiDetails, '', 2);
                    if ($isInserted == 0) {
                        $logMessage = "Failed to insert to APILogs.";
                        $logger->log($logger->logdate, "[REGISTERMEMBER ERROR]: " . $request['FirstName'] . " || " . $request['LastName'] . " || " . $request['MobileNo'] . " || " . $request['EmailAddress'] . " || " . $request['Birthdate'] . " || ", $logMessage);
                    }

                    exit;
                } else {
                    //check if email is already verified in temp table
                    $tempEmail = $membershipTempModel->checkTempVerifiedEmail($emailAddress);

                    if ($tempEmail['COUNT(a.MID)'] > 0) {

                        $transMsg = "Email is already verified. Please choose a different email address.";
                        $errorCode = 52;
                        Utilities::log("ReturnMessage: " . $transMsg . " ErrorCode: " . $errorCode);
                        $data = CommonController::retMsgRegisterMember($module, $errorCode, $transMsg);
                        $message = "[" . $module . "] Output: " . CJSON::encode($data);
                        $appLogger->log($appLogger->logdate, "[response]", $message);
                        $this->_sendResponse(200, CJSON::encode($data));
                        $logMessage = 'Email is already verified. Please choose a different email address.';
                        $logger->log($logger->logdate, "[REGISTERMEMBER ERROR]: " . $request['FirstName'] . " || " . $request['LastName'] . " || " . $request['MobileNo'] . " || " . $request['EmailAddress'] . " || " . $request['Birthdate'] . " || ", $logMessage);
                        $apiDetails = 'REGISTERMEMBER-Failed: Email is already verified. Email = ' . $emailAddress;
                        $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, $refID, $apiDetails, '', 2);
                        if ($isInserted == 0) {
                            $logMessage = "Failed to insert to APILogs.";
                            $logger->log($logger->logdate, "[REGISTERMEMBER ERROR]: " . $request['FirstName'] . " || " . $request['LastName'] . " || " . $request['MobileNo'] . " || " . $request['EmailAddress'] . " || " . $request['Birthdate'] . " || ", $logMessage);
                        }

                        exit;
                    } else {
                        $lastInsertedMID = $membershipTempModel->register($emailAddress, $firstname, $middlename, $lastname, $nickname, $password, $permanentAddress, $mobileNumber, $alternateMobileNumber, $alternateEmail, $idNumber, $idPresented, $gender, $referralCode, $birthdate, $occupationID, $nationalityID, $isSmoker, $referrerID, $emailSubscription, $smsSubscription);

                        if ($lastInsertedMID > 0) {
			    $lastInsertedMID = $lastInsertedMID['MID'];
                            $mpSessionID = '';

                            $memberInfos = $membershipTempModel->getTempMemberInfoForSMS($lastInsertedMID);

                            //match to 09 or 639 in mobile number
                            $match = substr($memberInfos['MobileNumber'], 0, 3);
                            if ($match == "639") {
                                $mncount = count($memberInfos["MobileNumber"]);
                                if (!$mncount == 12) {
                                    $message = "Failed to send SMS. Invalid Mobile Number.";
                                    $logger->log($logger->logdate, "[REGISTERMEMBER ERROR]: " . $request['FirstName'] . " || " . $request['LastName'] . " || " . $request['MobileNo'] . " || " . $request['EmailAddress'] . " || " . $request['Birthdate'] . " || ", $message);
                                    $apiDetails = "REGISTERMEMBER-Failed: Failed to send SMS. Invalid Mobile Number [MID = $lastInsertedMID].";
                                    $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, $refID, $apiDetails, '', 2);
                                    if ($isInserted == 0) {
                                        $logMessage = "Failed to insert to APILogs.";
                                        $logger->log($logger->logdate, "[REGISTERMEMBER ERROR]: " . $request['FirstName'] . " || " . $request['LastName'] . " || " . $request['MobileNo'] . " || " . $request['EmailAddress'] . " || " . $request['Birthdate'] . " || ", $logMessage);
                                    }
                                } else {
                                    $templateid1 = $ref_SMSApiMethodsModel->getSMSMethodTemplateID(Ref_SMSApiMethodsModel::PLAYER_REGISTRATION_1OF2);
                                    $templateid2 = $ref_SMSApiMethodsModel->getSMSMethodTemplateID(Ref_SMSApiMethodsModel::PLAYER_REGISTRATION_2OF2);
                                    $templateid1 = $templateid1['SMSTemplateID'];
                                    $templateid2 = $templateid2['SMSTemplateID'];
                                    $methodid1 = Ref_SMSApiMethodsModel::PLAYER_REGISTRATION_1OF2;
                                    $methodid2 = Ref_SMSApiMethodsModel::PLAYER_REGISTRATION_2OF2;
                                    $mobileno = $memberInfos["MobileNumber"];
                                    $smslastinsertedid1 = $smsRequestLogsModel->insertSMSRequestLogs($methodid1, $mobileno, $memberInfos["DateCreated"]);
                                    $smslastinsertedid2 = $smsRequestLogsModel->insertSMSRequestLogs($methodid2, $mobileno, $memberInfos["DateCreated"]);
                                    if (($smslastinsertedid1 != 0 && $smslastinsertedid1 != '') && ($smslastinsertedid2 != 0 && $smslastinsertedid2 != '')) {
                                        $trackingid1 = "SMSR" . $smslastinsertedid1;
                                        $trackingid2 = "SMSR" . $smslastinsertedid2;
                                        $apiURL = Yii::app()->params["SMSURI"];
                                        $app_id = Yii::app()->params["app_id"];
                                        $membershipSMSApi = new MembershipSmsAPI($apiURL, $app_id);
                                        $smsresult1 = $membershipSMSApi->sendRegistration1($mobileno, $templateid1['SMSTemplateID'], $memberInfos["DateCreated"], $memberInfos["TemporaryAccountCode"], $trackingid1);
                                        $smsresult2 = $membershipSMSApi->sendRegistration2($mobileno, $templateid2['SMSTemplateID'], $memberInfos["DateCreated"], $memberInfos["TemporaryAccountCode"], $trackingid2);
                                        if (isset($smsresult1['status']) && isset($smsresult2['status'])) {
                                            if ($smsresult1['status'] != 1 && $smsresult2['status'] != 1) {
                                                $transMsg = 'Invalid Mobile Number.';
                                                $errorCode = 97;
                                                Utilities::log("ReturnMessage: " . $transMsg . " ErrorCode: " . $errorCode);
                                                $data = CommonController::retMsgRegisterMember($module, $errorCode, $transMsg);
                                                $message = "[" . $module . "] Output: " . CJSON::encode($data);
                                                $appLogger->log($appLogger->logdate, "[response]", $message);
                                                $this->_sendResponse(200, CJSON::encode($data));
                                                $logMessage = 'Invalid Mobile Number.';
                                                $logger->log($logger->logdate, "[REGISTERMEMBER ERROR]: " . $request['FirstName'] . " || " . $request['LastName'] . " || " . $request['MobileNo'] . " || " . $request['EmailAddress'] . " || " . $request['Birthdate'] . " || ", $logMessage);
                                                $apiDetails = 'REGISTERMEMBER-Failed: Invalid Mobile Number. MID = ' . $lastInsertedMID;
                                                $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, '', $apiDetails, '', 2);
                                                if ($isInserted == 0) {
                                                    $logMessage = "Failed to insert to APILogs.";
                                                    $logger->log($logger->logdate, "[REGISTERMEMBER ERROR]: " . $request['FirstName'] . " || " . $request['LastName'] . " || " . $request['MobileNo'] . " || " . $request['EmailAddress'] . " || " . $request['Birthdate'] . " || ", $logMessage);
                                                }
                                            } else {
                                                $transMsg = "You have successfully registered! An active Temporary Account will be sent to your email address or mobile number, which can be used to start session or credit points in the absence of Membership Card. Please note that your Registered Account and Temporary Account will be activated only after 24 hours.";
                                                $errorCode = 0;
                                                Utilities::log("ReturnMessage: " . $transMsg . " ErrorCode: " . $errorCode);
                                                $data = CommonController::retMsgRegisterMember($module, $errorCode, $transMsg);
                                                $message = "[" . $module . "] Output: " . CJSON::encode($data);
                                                $appLogger->log($appLogger->logdate, "[response]", $message);
                                                $this->_sendResponse(200, CJSON::encode($data));
                                                $logMessage = 'Registration is successful.';
                                                $logger->log($logger->logdate, "[REGISTERMEMBER SUCCESSFUL]: " . $request['FirstName'] . " || " . $request['LastName'] . " || " . $request['MobileNo'] . " || " . $request['EmailAddress'] . " || " . $request['Birthdate'] . " || ", $logMessage);
                                                $apiDetails = 'REGISTERMEMBER-Success: Registration is successful. MID = ' . $lastInsertedMID;
                                                $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, $refID, $apiDetails, '', 1);
                                                if ($isInserted == 0) {
                                                    $logMessage = "Failed to insert to APILogs.";
                                                    $logger->log($logger->logdate, "[REGISTERMEMBER ERROR]: " . $request['FirstName'] . " || " . $request['LastName'] . " || " . $request['MobileNo'] . " || " . $request['EmailAddress'] . " || " . $request['Birthdate'] . " || ", $logMessage);
                                                }
                                            }
                                        }
                                    } else {
                                        $message = "Failed to send SMS: Error on logging event in database.";
                                        $logger->log($logger->logdate, "[REGISTERMEMBER ERROR]: " . $request['FirstName'] . " || " . $request['LastName'] . " || " . $request['MobileNo'] . " || " . $request['EmailAddress'] . " || " . $request['Birthdate'] . " || ", $message);
                                        $apiDetails = "REGISTERMEMBER-Failed: Failed to send SMS. Error on logging event in database. [MID = $lastInsertedMID].";
                                        $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, $refID, $apiDetails, '', 2);
                                        if ($isInserted == 0) {
                                            $logMessage = "Failed to insert to APILogs.";
                                            $logger->log($logger->logdate, "[REGISTERMEMBER ERROR]: " . $request['FirstName'] . " || " . $request['LastName'] . " || " . $request['MobileNo'] . " || " . $request['EmailAddress'] . " || " . $request['Birthdate'] . " || ", $logMessage);
                                        }
                                    }
                                }
                            } else {
                                $match = substr($memberInfos["MobileNumber"], 0, 2);
                                if ($match == "09") {
                                    $mncount = count($memberInfos["MobileNumber"]);

                                    if (!$mncount == 11) {
                                        $message = "Failed to send SMS: Invalid Mobile Number.";
                                        $logger->log($logger->logdate, "[REGISTERMEMBER ERROR]: " . $request['FirstName'] . " || " . $request['LastName'] . " || " . $request['MobileNo'] . " || " . $request['EmailAddress'] . " || " . $request['Birthdate'] . " || ", $message);
                                        $apiDetails = "REGISTERMEMBER-Failed: Failed to send SMS. Invalid Mobile Number [MID = $lastInsertedMID].";
                                        $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, $refID, $apiDetails, '', 2);
                                        if ($isInserted == 0) {
                                            $logMessage = "Failed to insert to APILogs.";
                                            $logger->log($logger->logdate, "[REGISTERMEMBER ERROR]: " . $request['FirstName'] . " || " . $request['LastName'] . " || " . $request['MobileNo'] . " || " . $request['EmailAddress'] . " || " . $request['Birthdate'] . " || ", $logMessage);
                                        }
                                    } else {
                                        $cpNumber = $memberInfos["MobileNumber"];
                                        $mobileno = $this->formatMobileNumber($cpNumber);
                                        $templateid1 = $ref_SMSApiMethodsModel->getSMSMethodTemplateID(Ref_SMSApiMethodsModel::PLAYER_REGISTRATION_1OF2);
                                        $templateid2 = $ref_SMSApiMethodsModel->getSMSMethodTemplateID(Ref_SMSApiMethodsModel::PLAYER_REGISTRATION_2OF2);
                                        $templateid1 = $templateid1['SMSTemplateID'];
                                        $templateid2 = $templateid2['SMSTemplateID'];
                                        $methodid1 = Ref_SMSApiMethodsModel::PLAYER_REGISTRATION_1OF2;
                                        $methodid2 = Ref_SMSApiMethodsModel::PLAYER_REGISTRATION_2OF2;

                                        $smslastinsertedid1 = $smsRequestLogsModel->insertSMSRequestLogs($methodid1, $mobileno, $memberInfos["DateCreated"]);
                                        $smslastinsertedid2 = $smsRequestLogsModel->insertSMSRequestLogs($methodid2, $mobileno, $memberInfos["DateCreated"]);
                                        if (($smslastinsertedid1 != 0 && $smslastinsertedid1 != '') && ($smslastinsertedid2 != 0 && $smslastinsertedid2 != '')) {
                                            $trackingid1 = "SMSR" . $smslastinsertedid1;
                                            $trackingid2 = "SMSR" . $smslastinsertedid2;
                                            $apiURL = Yii::app()->params['SMSURI'];
                                            $app_id = Yii::app()->params['app_id'];
                                            $membershipSMSApi = new MembershipSmsAPI($apiURL, $app_id);
                                            $smsresult1 = $membershipSMSApi->sendRegistration1($mobileno, $templateid1, $memberInfos["DateCreated"], $memberInfos["TemporaryAccountCode"], $trackingid1);
                                            $smsresult2 = $membershipSMSApi->sendRegistration2($mobileno, $templateid2, $memberInfos["DateCreated"], $memberInfos["TemporaryAccountCode"], $trackingid2);
                                            if (isset($smsresult1['status']) && isset($smsresult2['status'])) {
                                                if ($smsresult1['status'] != 1 && $smsresult2['status'] != 1) {
                                                    $transMsg = 'Invalid Mobile Number.';
                                                    $errorCode = 97;
                                                    Utilities::log("ReturnMessage: " . $transMsg . " ErrorCode: " . $errorCode);
                                                    $data = CommonController::retMsgRegisterMember($module, $errorCode, $transMsg);
                                                    $message = "[" . $module . "] Output: " . CJSON::encode($data);
                                                    $appLogger->log($appLogger->logdate, "[response]", $message);
                                                    $this->_sendResponse(200, CJSON::encode($data));
                                                    $logMessage = 'Invalid Mobile Number.';
                                                    $logger->log($logger->logdate, "[REGISTERMEMBER ERROR]: " . $request['FirstName'] . " || " . $request['LastName'] . " || " . $request['MobileNo'] . " || " . $request['EmailAddress'] . " || " . $request['Birthdate'] . " || ", $logMessage);
                                                    $apiDetails = 'REGISTERMEMBER-Failed: Invalid Mobile Number. MID = ' . $lastInsertedMID;
                                                    $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, '', $apiDetails, '', 2);
                                                    if ($isInserted == 0) {
                                                        $logMessage = "Failed to insert to APILogs.";
                                                        $logger->log($logger->logdate, "[REGISTERMEMBER ERROR]: " . $request['FirstName'] . " || " . $request['LastName'] . " || " . $request['MobileNo'] . " || " . $request['EmailAddress'] . " || " . $request['Birthdate'] . " || ", $logMessage);
                                                    }
                                                } else {
                                                    $transMsg = "You have successfully registered! An active Temporary Account will be sent to your email address or mobile number, which can be used to start session or credit points in the absence of Membership Card. Please note that your Registered Account and Temporary Account will be activated only after 24 hours.";
                                                    $errorCode = 0;
                                                    Utilities::log("ReturnMessage: " . $transMsg . " ErrorCode: " . $errorCode);
                                                    $data = CommonController::retMsgRegisterMember($module, $errorCode, $transMsg);
                                                    $message = "[" . $module . "] Output: " . CJSON::encode($data);
                                                    $appLogger->log($appLogger->logdate, "[response]", $message);
                                                    $this->_sendResponse(200, CJSON::encode($data));
                                                    $logMessage = 'Registration is successful.';
                                                    $logger->log($logger->logdate, "[REGISTERMEMBER SUCCESSFUL]: " . $request['FirstName'] . " || " . $request['LastName'] . " || " . $request['MobileNo'] . " || " . $request['EmailAddress'] . " || " . $request['Birthdate'] . " || ", $logMessage);
                                                    $apiDetails = 'REGISTERMEMBER-Success: Registration is successful. MID = ' . $lastInsertedMID;
                                                    $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, $refID, $apiDetails, '', 1);
                                                    if ($isInserted == 0) {
                                                        $logMessage = "Failed to insert to APILogs.";
                                                        $logger->log($logger->logdate, "[REGISTERMEMBER ERROR]: " . $request['FirstName'] . " || " . $request['LastName'] . " || " . $request['MobileNo'] . " || " . $request['EmailAddress'] . " || " . $request['Birthdate'] . " || ", $logMessage);
                                                    }
                                                }
                                            }
                                        } else {
                                            $message = "Failed to send SMS: Error on logging event in database.";
                                            $logger->log($logger->logdate, "[REGISTERMEMBER ERROR]: " . $request['FirstName'] . " || " . $request['LastName'] . " || " . $request['MobileNo'] . " || " . $request['EmailAddress'] . " || " . $request['Birthdate'] . " || ", $message);
                                            $apiDetails = "REGISTERMEMBER-Failed: Failed to send SMS. Error on logging event in database [MID = $lastInsertedMID].";
                                            $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, $refID, $apiDetails, '', 2);
                                            if ($isInserted == 0) {
                                                $logMessage = "Failed to insert to APILogs.";
                                                $logger->log($logger->logdate, "[REGISTERMEMBER ERROR]: " . $request['FirstName'] . " || " . $request['LastName'] . " || " . $request['MobileNo'] . " || " . $request['EmailAddress'] . " || " . $request['Birthdate'] . " || ", $logMessage);
                                            }
                                        }
                                    }
                                } else {
                                    $message = "Failed to send SMS: Invalid Mobile Number.";
                                    $logger->log($logger->logdate, "[REGISTERMEMBER ERROR]: " . $request['FirstName'] . " || " . $request['LastName'] . " || " . $request['MobileNo'] . " || " . $request['EmailAddress'] . " || " . $request['Birthdate'] . " || ", $message);
                                    $apiDetails = "REGISTERMEMBER-Failed: Failed to send SMS. Invalid Mobile Number [MID = $lastInsertedMID].";
                                    $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, $refID, $apiDetails, '', 2);
                                    if ($isInserted == 0) {
                                        $logMessage = "Failed to insert to APILogs.";
                                        $logger->log($logger->logdate, "[REGISTERMEMBER ERROR]: " . $request['FirstName'] . " || " . $request['LastName'] . " || " . $request['MobileNo'] . " || " . $request['EmailAddress'] . " || " . $request['Birthdate'] . " || ", $logMessage);
                                    }
                                }
                            }
                            $auditTrailModel->logEvent(AuditTrailModel::API_REGISTER_MEMBER, 'Email: ' . $emailAddress, array('MID' => $lastInsertedMID, 'SessionID' => $mpSessionID));
                        } else {
                            //check if email is already verified in temp table
                            $tempEmail = $membershipTempModel->checkTempVerifiedEmail($emailAddress);
                            if ($tempEmail['COUNT(a.MID)'] > 0) {
                                $transMsg = "Email is already verified. Please choose a different email address.";
                                $errorCode = 52;
                                Utilities::log("ReturnMessage: " . $transMsg . " ErrorCode: " . $errorCode);
                                $data = CommonController::retMsgRegisterMember($module, $errorCode, $transMsg);
                                $message = "[" . $module . "] Output: " . CJSON::encode($data);
                                $appLogger->log($appLogger->logdate, "[response]", $message);
                                $this->_sendResponse(200, CJSON::encode($data));
                                $logMessage = 'Email is already verified. Please choose a different email address.';
                                $logger->log($logger->logdate, "[REGISTERMEMBER ERROR]: " . $request['FirstName'] . " || " . $request['LastName'] . " || " . $request['MobileNo'] . " || " . $request['EmailAddress'] . " || " . $request['Birthdate'] . " || ", $logMessage);
                                $apiDetails = 'REGISTERMEMBER-Failed: Email is already verified. Email = ' . $emailAddress;
                                $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, $refID, $apiDetails, '', 2);
                                if ($isInserted == 0) {
                                    $logMessage = "Failed to insert to APILogs.";
                                    $logger->log($logger->logdate, "[REGISTERMEMBER ERROR]: " . $request['FirstName'] . " || " . $request['LastName'] . " || " . $request['MobileNo'] . " || " . $request['EmailAddress'] . " || " . $request['Birthdate'] . " || ", $logMessage);
                                }

                                exit;
                            } else {
                                $lastInsertedMID = $membershipTempModel->register($emailAddress, $firstname, $middlename, $lastname, $nickname, $password, $permanentAddress, $mobileNumber, $alternateMobileNumber, $alternateEmail, $idNumber, $idPresented, $gender, $referralCode, $birthdate, $occupationID, $nationalityID, $isSmoker, $emailSubscription, $smsSubscription, $referrerID);

                                if ($lastInsertedMID > 0) {
                                    $SFID = $lastInsertedMID['SFID'];
                                    $lastInsertedMID = $lastInsertedMID['MID'];
                                    $ID = 0;
                                    $mpSessionID = '';

                                    $memberInfos = $membershipTempModel->getTempMemberInfoForSMS($lastInsertedMID);

                                    //match to 09 or 639 in mobile number
                                    $match = substr($memberInfos['MobileNumber'], 0, 3);
                                    if ($match == "639") {
                                        $mncount = count($memberInfos["MobileNumber"]);
                                        if (!$mncount == 12) {
                                            $message = "Failed to send SMS. Invalid Mobile Number.";
                                            $logger->log($logger->logdate, "[REGISTERMEMBER ERROR]: " . $request['FirstName'] . " || " . $request['LastName'] . " || " . $request['MobileNo'] . " || " . $request['EmailAddress'] . " || " . $request['Birthdate'] . " || ", $message);
                                            $apiDetails = "REGISTERMEMBER-Failed: Failed to send SMS. Invalid Mobile Number [MID = $lastInsertedMID].";
                                            $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, $refID, $apiDetails, '', 2);
                                            if ($isInserted == 0) {
                                                $logMessage = "Failed to insert to APILogs.";
                                                $logger->log($logger->logdate, "[REGISTERMEMBER ERROR]: " . $request['FirstName'] . " || " . $request['LastName'] . " || " . $request['MobileNo'] . " || " . $request['EmailAddress'] . " || " . $request['Birthdate'] . " || ", $logMessage);
                                            }
                                        } else {
                                            $templateid1 = $ref_SMSApiMethodsModel->getSMSMethodTemplateID(Ref_SMSApiMethodsModel::PLAYER_REGISTRATION_1OF2);
                                            $templateid2 = $ref_SMSApiMethodsModel->getSMSMethodTemplateID(Ref_SMSApiMethodsModel::PLAYER_REGISTRATION_2OF2);
                                            $methodid1 = Ref_SMSApiMethodsModel::PLAYER_REGISTRATION_1OF2;
                                            $methodid2 = Ref_SMSApiMethodsModel::PLAYER_REGISTRATION_2OF2;
                                            $mobileno = $memberInfos["MobileNumber"];
                                            $smslastinsertedid1 = $smsRequestLogsModel->insertSMSRequestLogs($methodid1, $mobileno, $memberInfos["DateCreated"]);
                                            $smslastinsertedid2 = $smsRequestLogsModel->insertSMSRequestLogs($methodid2, $mobileno, $memberInfos["DateCreated"]);
                                            if (($smslastinsertedid1 != 0 && $smslastinsertedid1 != '') && ($smslastinsertedid2 != 0 && $smslastinsertedid2 != '')) {
                                                $trackingid1 = "SMSR" . $smslastinsertedid1;
                                                $trackingid2 = "SMSR" . $smslastinsertedid2;
                                                $apiURL = Yii::app()->params["SMSURI"];
                                                $app_id = Yii::app()->params["app_id"];
                                                $membershipSMSApi = new MembershipSmsAPI($apiURL, $app_id);
                                                $smsresult1 = $membershipSMSApi->sendRegistration1($mobileno, $templateid1['SMSTemplateID'], $memberInfos["DateCreated"], $memberInfos["TemporaryAccountCode"], $trackingid1);
                                                $smsresult2 = $membershipSMSApi->sendRegistration2($mobileno, $templateid2['SMSTemplateID'], $memberInfos["DateCreated"], $memberInfos["TemporaryAccountCode"], $trackingid2);
                                                if (isset($smsresult1['status']) && isset($smsresult2['status'])) {
                                                    if ($smsresult1['status'] != 1 && $smsresult2['status'] != 1) {
                                                        $transMsg = 'Invalid Mobile Number.';
                                                        $errorCode = 97;
                                                        Utilities::log("ReturnMessage: " . $transMsg . " ErrorCode: " . $errorCode);
                                                        $data = CommonController::retMsgRegisterMember($module, $errorCode, $transMsg);
                                                        $message = "[" . $module . "] Output: " . CJSON::encode($data);
                                                        $appLogger->log($appLogger->logdate, "[response]", $message);
                                                        $this->_sendResponse(200, CJSON::encode($data));
                                                        $logMessage = 'Invalid Mobile Number.';
                                                        $logger->log($logger->logdate, "[REGISTERMEMBER ERROR]: " . $request['FirstName'] . " || " . $request['LastName'] . " || " . $request['MobileNo'] . " || " . $request['EmailAddress'] . " || " . $request['Birthdate'] . " || ", $logMessage);
                                                        $apiDetails = 'REGISTERMEMBER-Failed: Invalid Mobile Number. MID = ' . $lastInsertedMID;
                                                        $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, '', $apiDetails, '', 2);
                                                        if ($isInserted == 0) {
                                                            $logMessage = "Failed to insert to APILogs.";
                                                            $logger->log($logger->logdate, "[REGISTERMEMBER ERROR]: " . $request['FirstName'] . " || " . $request['LastName'] . " || " . $request['MobileNo'] . " || " . $request['EmailAddress'] . " || " . $request['Birthdate'] . " || ", $logMessage);
                                                        }
                                                    } else {
                                                        $transMsg = "You have successfully registered! An active Temporary Account will be sent to your email address or mobile number, which can be used to start session or credit points in the absence of Membership Card. Please note that your Registered Account and Temporary Account will be activated only after 24 hours.";
                                                        $errorCode = 0;
                                                        Utilities::log("ReturnMessage: " . $transMsg . " ErrorCode: " . $errorCode);
                                                        $data = CommonController::retMsgRegisterMember($module, $errorCode, $transMsg);
                                                        $message = "[" . $module . "] Output: " . CJSON::encode($data);
                                                        $appLogger->log($appLogger->logdate, "[response]", $message);
                                                        $this->_sendResponse(200, CJSON::encode($data));
                                                        $logMessage = 'Registration is successful.';
                                                        $logger->log($logger->logdate, "[REGISTERMEMBER SUCCESSFUL]: " . $request['FirstName'] . " || " . $request['LastName'] . " || " . $request['MobileNo'] . " || " . $request['EmailAddress'] . " || " . $request['Birthdate'] . " || ", $logMessage);
                                                        $apiDetails = 'REGISTERMEMBER-Success: Registration is successful. MID = ' . $lastInsertedMID;
                                                        $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, $refID, $apiDetails, '', 1);
                                                        if ($isInserted == 0) {
                                                            $logMessage = "Failed to insert to APILogs.";
                                                            $logger->log($logger->logdate, "[REGISTERMEMBER ERROR]: " . $request['FirstName'] . " || " . $request['LastName'] . " || " . $request['MobileNo'] . " || " . $request['EmailAddress'] . " || " . $request['Birthdate'] . " || ", $logMessage);
                                                        }
                                                    }
                                                }
                                            } else {
                                                $message = "Failed to send SMS: Error on logging event in database.";
                                                $logger->log($logger->logdate, "[REGISTERMEMBER ERROR]: " . $request['FirstName'] . " || " . $request['LastName'] . " || " . $request['MobileNo'] . " || " . $request['EmailAddress'] . " || " . $request['Birthdate'] . " || ", $message);
                                                $apiDetails = "REGISTERMEMBER-Failed: Failed to send SMS. Error on logging event in database. [MID = $lastInsertedMID].";
                                                $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, $refID, $apiDetails, '', 2);
                                                if ($isInserted == 0) {
                                                    $logMessage = "Failed to insert to APILogs.";
                                                    $logger->log($logger->logdate, "[REGISTERMEMBER ERROR]: " . $request['FirstName'] . " || " . $request['LastName'] . " || " . $request['MobileNo'] . " || " . $request['EmailAddress'] . " || " . $request['Birthdate'] . " || ", $logMessage);
                                                }
                                            }
                                        }
                                    } else {
                                        $match = substr($memberInfos["MobileNumber"], 0, 2);
                                        if ($match == "09") {
                                            $mncount = count($memberInfos["MobileNumber"]);
                                            if (!$mncount == 11) {
                                                $message = "Failed to send SMS: Invalid Mobile Number.";
                                                $logger->log($logger->logdate, "[REGISTERMEMBER ERROR]: " . $request['FirstName'] . " || " . $request['LastName'] . " || " . $request['MobileNo'] . " || " . $request['EmailAddress'] . " || " . $request['Birthdate'] . " || ", $message);
                                                $apiDetails = "REGISTERMEMBER-Failed: Failed to send SMS. Invalid Mobile Number [MID = $lastInsertedMID].";
                                                $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, $refID, $apiDetails, '', 2);
                                                if ($isInserted == 0) {
                                                    $logMessage = "Failed to insert to APILogs.";
                                                    $logger->log($logger->logdate, "[REGISTERMEMBER ERROR]: " . $request['FirstName'] . " || " . $request['LastName'] . " || " . $request['MobileNo'] . " || " . $request['EmailAddress'] . " || " . $request['Birthdate'] . " || ", $logMessage);
                                                }
                                            } else {
                                                $mobileno = str_replace("09", "639", $memberInfos["MobileNumber"]);
                                                $templateid1 = $ref_SMSApiMethodsModel->getSMSMethodTemplateID(Ref_SMSApiMethodsModel::PLAYER_REGISTRATION_1OF2);
                                                $templateid2 = $ref_SMSApiMethodsModel->getSMSMethodTemplateID(Ref_SMSApiMethodsModel::PLAYER_REGISTRATION_2OF2);
                                                $methodid1 = Ref_SMSApiMethodsModel::PLAYER_REGISTRATION_1OF2;
                                                $methodid2 = Ref_SMSApiMethodsModel::PLAYER_REGISTRATION_2OF2;
                                                $smslastinsertedid1 = $smsRequestLogsModel->insertSMSRequestLogs($methodid1, $mobileno, $memberInfos["DateCreated"]);
                                                $smslastinsertedid2 = $smsRequestLogsModel->insertSMSRequestLogs($methodid2, $mobileno, $memberInfos["DateCreated"]);
                                                if (($smslastinsertedid1 != 0 && $smslastinsertedid1 != '') && ($smslastinsertedid2 != 0 && $smslastinsertedid2 != '')) {
                                                    $trackingid1 = "SMSR" . $smslastinsertedid1;
                                                    $trackingid2 = "SMSR" . $smslastinsertedid2;
                                                    $apiURL = Yii::app()->params['SMSURI'];
                                                    $app_id = Yii::app()->params['app_id'];
                                                    $membershipSMSApi = new MembershipSmsAPI($apiURL, $app_id);
                                                    $smsresult1 = $membershipSMSApi->sendRegistration1($mobileno, $templateid1['SMSTemplateID'], $memberInfos["DateCreated"], $memberInfos["TemporaryAccountCode"], $trackingid1);
                                                    $smsresult2 = $membershipSMSApi->sendRegistration2($mobileno, $templateid2['SMSTemplateID'], $memberInfos["DateCreated"], $memberInfos["TemporaryAccountCode"], $trackingid2);
                                                    if (isset($smsresult1['status']) && isset($smsresult2['status'])) {
                                                        if ($smsresult1['status'] != 1 && $smsresult2['status'] != 1) {
                                                            $transMsg = 'Invalid Mobile Number.';
                                                            $errorCode = 97;
                                                            Utilities::log("ReturnMessage: " . $transMsg . " ErrorCode: " . $errorCode);
                                                            $data = CommonController::retMsgRegisterMember($module, $errorCode, $transMsg);
                                                            $message = "[" . $module . "] Output: " . CJSON::encode($data);
                                                            $appLogger->log($appLogger->logdate, "[response]", $message);
                                                            $this->_sendResponse(200, CJSON::encode($data));
                                                            $logMessage = 'Invalid Mobile Number.';
                                                            $logger->log($logger->logdate, "[REGISTERMEMBER ERROR]: " . $request['FirstName'] . " || " . $request['LastName'] . " || " . $request['MobileNo'] . " || " . $request['EmailAddress'] . " || " . $request['Birthdate'] . " || ", $logMessage);
                                                            $apiDetails = 'REGISTERMEMBER-Failed: Invalid Mobile Number. MID = ' . $lastInsertedMID;
                                                            $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, '', $apiDetails, '', 2);
                                                            if ($isInserted == 0) {
                                                                $logMessage = "Failed to insert to APILogs.";
                                                                $logger->log($logger->logdate, "[REGISTERMEMBER ERROR]: " . $request['FirstName'] . " || " . $request['LastName'] . " || " . $request['MobileNo'] . " || " . $request['EmailAddress'] . " || " . $request['Birthdate'] . " || ", $logMessage);
                                                            }
                                                        } else {
                                                            $transMsg = "You have successfully registered! An active Temporary Account will be sent to your email address or mobile number, which can be used to start session or credit points in the absence of Membership Card. Please note that your Registered Account and Temporary Account will be activated only after 24 hours.";
                                                            $errorCode = 0;
                                                            Utilities::log("ReturnMessage: " . $transMsg . " ErrorCode: " . $errorCode);
                                                            $data = CommonController::retMsgRegisterMember($module, $errorCode, $transMsg);
                                                            $message = "[" . $module . "] Output: " . CJSON::encode($data);
                                                            $appLogger->log($appLogger->logdate, "[response]", $message);
                                                            $this->_sendResponse(200, CJSON::encode($data));
                                                            $logMessage = 'Registration is successful.';
                                                            $logger->log($logger->logdate, "[REGISTERMEMBER SUCCESSFUL]: " . $request['FirstName'] . " || " . $request['LastName'] . " || " . $request['MobileNo'] . " || " . $request['EmailAddress'] . " || " . $request['Birthdate'] . " || ", $logMessage);
                                                            $apiDetails = 'REGISTERMEMBER-Success: Registration is successful. MID = ' . $lastInsertedMID;
                                                            $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, $refID, $apiDetails, '', 1);
                                                            if ($isInserted == 0) {
                                                                $logMessage = "Failed to insert to APILogs.";
                                                                $logger->log($logger->logdate, "[REGISTERMEMBER ERROR]: " . $request['FirstName'] . " || " . $request['LastName'] . " || " . $request['MobileNo'] . " || " . $request['EmailAddress'] . " || " . $request['Birthdate'] . " || ", $logMessage);
                                                            }
                                                        }
                                                    }
                                                } else {
                                                    $message = "Failed to send SMS: Error on logging event in database.";
                                                    $logger->log($logger->logdate, "[REGISTERMEMBER ERROR]: " . $request['FirstName'] . " || " . $request['LastName'] . " || " . $request['MobileNo'] . " || " . $request['EmailAddress'] . " || " . $request['Birthdate'] . " || ", $message);
                                                    $apiDetails = "REGISTERMEMBER-Failed: Failed to send SMS. Error on logging event in database [MID = $lastInsertedMID].";
                                                    $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, $refID, $apiDetails, '', 2);
                                                    if ($isInserted == 0) {
                                                        $logMessage = "Failed to insert to APILogs.";
                                                        $logger->log($logger->logdate, "[REGISTERMEMBER ERROR]: " . $request['FirstName'] . " || " . $request['LastName'] . " || " . $request['MobileNo'] . " || " . $request['EmailAddress'] . " || " . $request['Birthdate'] . " || ", $logMessage);
                                                    }
                                                }
                                            }
                                        } else {
                                            $message = "Failed to send SMS: Invalid Mobile Number.";
                                            $logger->log($logger->logdate, "[REGISTERMEMBER ERROR]: " . $request['FirstName'] . " || " . $request['LastName'] . " || " . $request['MobileNo'] . " || " . $request['EmailAddress'] . " || " . $request['Birthdate'] . " || ", $message);
                                            $apiDetails = "REGISTERMEMBER-Failed: Failed to send SMS. Invalid Mobile Number [MID = $lastInsertedMID].";
                                            $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, $refID, $apiDetails, '', 2);
                                            if ($isInserted == 0) {
                                                $logMessage = "Failed to insert to APILogs.";
                                                $logger->log($logger->logdate, "[REGISTERMEMBER ERROR]: " . $request['FirstName'] . " || " . $request['LastName'] . " || " . $request['MobileNo'] . " || " . $request['EmailAddress'] . " || " . $request['Birthdate'] . " || ", $logMessage);
                                            }
                                        }
                                    }

                                    $auditTrailModel->logEvent(AuditTrailModel::API_REGISTER_MEMBER, 'Email: ' . $emailAddress, array('ID' => $ID));
                                } else {
                                    if (strpos($lastInsertedMID['MID'], " Integrity constraint violation: 1062 Duplicate entry") > 0) {
                                        $transMsg = "Sorry, " . $emailAddress . "already belongs to an existing account. Please enter another email address.";
                                        $errorCode = 21;
                                        Utilities::log("ReturnMessage: " . $transMsg . " ErrorCode: " . $errorCode);
                                        $data = CommonController::retMsgRegisterMember($module, $errorCode, $transMsg);
                                        $message = "[" . $module . "] Output: " . CJSON::encode($data);
                                        $appLogger->log($appLogger->logdate, "[response]", $message);
                                        $this->_sendResponse(200, CJSON::encode($data));
                                        $logMessage = "Sorry, " . $emailAddress . "already belongs to an existing account. Please enter another email address.";
                                        $logger->log($logger->logdate, "[REGISTERMEMBER ERROR]: " . $request['FirstName'] . " || " . $request['LastName'] . " || " . $request['MobileNo'] . " || " . $request['EmailAddress'] . " || " . $request['Birthdate'] . " || ", $logMessage);
                                        $apiDetails = 'REGISTERMEMBER-Failed: Email already exists. Please choose a different email address. Email = ' . $emailAddress;
                                        $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, $refID, $apiDetails, '', 2);
                                        if ($isInserted == 0) {
                                            $logMessage = "Failed to insert to APILogs.";
                                            $logger->log($logger->logdate, "[REGISTERMEMBER ERROR]: " . $request['FirstName'] . " || " . $request['LastName'] . " || " . $request['MobileNo'] . " || " . $request['EmailAddress'] . " || " . $request['Birthdate'] . " || ", $logMessage);
                                        }

                                        exit;
                                    } else {
                                        $transMsg = "Registration failed.";
                                        $errorCode = 53;
                                        Utilities::log("ReturnMessage: " . $transMsg . " ErrorCode: " . $errorCode);
                                        $data = CommonController::retMsgRegisterMember($module, $errorCode, $transMsg);
                                        $message = "[" . $module . "] Output: " . CJSON::encode($data);
                                        $appLogger->log($appLogger->logdate, "[response]", $message);
                                        $this->_sendResponse(200, CJSON::encode($data));
                                        $logMessage = "Registration failed.";
                                        $logger->log($logger->logdate, "[REGISTERMEMBER ERROR]: " . $request['FirstName'] . " || " . $request['LastName'] . " || " . $request['MobileNo'] . " || " . $request['EmailAddress'] . " || " . $request['Birthdate'] . " || ", $logMessage);
                                        $apiDetails = 'REGISTERMEMBER-Failed: Registration failed.';
                                        $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, $refID, $apiDetails, '', 2);
                                        if ($isInserted == 0) {
                                            $logMessage = "Failed to insert to APILogs.";
                                            $logger->log($logger->logdate, "[REGISTERMEMBER ERROR]: " . $request['FirstName'] . " || " . $request['LastName'] . " || " . $request['MobileNo'] . " || " . $request['EmailAddress'] . " || " . $request['Birthdate'] . " || ", $logMessage);
                                        }

                                        exit;
                                    }
                                }
                            }
                        }
                    }
                }
            }
        } else {
            $transMsg = "One or more fields is not set or is blank.";
            $errorCode = 1;
            Utilities::log("ReturnMessage: " . $transMsg . " ErrorCode: " . $errorCode);
            $data = CommonController::retMsgRegisterMember($module, $errorCode, $transMsg);
            $message = "[" . $module . "] Output: " . CJSON::encode($data);
            $appLogger->log($appLogger->logdate, "[response]", $message);
            $this->_sendResponse(200, CJSON::encode($data));
            $logMessage = 'One or more fields is not set or is blank.';
            $logger->log($logger->logdate, "[REGISTERMEMBER ERROR]: " . $request['FirstName'] . " || " . $request['LastName'] . " || " . $request['MobileNo'] . " || " . $request['EmailAddress'] . " || " . $request['Birthdate'] . " || ", $logMessage);
            $apiDetails = 'REGISTERMEMBER-Failed: Invalid input parameters.';
            $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, '', $apiDetails, '', 2);
            if ($isInserted == 0) {
                $logMessage = "Failed to insert to APILogs.";
                $logger->log($logger->logdate, "[REGISTERMEMBER ERROR]: " . $request['FirstName'] . " || " . $request['LastName'] . " || " . $request['MobileNo'] . " || " . $request['EmailAddress'] . " || " . $request['Birthdate'] . " || ", $logMessage);
            }

            exit;
        }
    }

    private function formatMobileNumber($cpNumber) {
        return str_replace("09", "639", substr($cpNumber, 0, 2)) . substr($cpNumber, 2, strlen($cpNumber));
    }

    public function actionUpdateProfile() {
        $request = $this->_readJsonRequest();

        $transMsg = '';
        $errorCode = '';
        $module = 'UpdateProfile';
        $apiMethod = 4;

        $appLogger = new AppLogger();

        $paramval = CJSON::encode($request);
        $message = "[" . $module . "] Input: " . $paramval;
        $appLogger->log($appLogger->logdate, "[request]", $message);

        $logger = new ErrorLogger();
        $apiLogsModel = new APILogsModel();
        $memberSessionsModel = new MemberSessionsModel();

        $result = $memberSessionsModel->getMID($request['MPSessionID']);
        $MID = $result['MID'];

        $isValid = $this->_validateMPSession($request['MPSessionID']);
        if (isset($isValid) && !$isValid) {
            $transMsg = "MPSessionID is already expired. Please login again.";
            $errorCode = 91;
            Utilities::log("ReturnMessage: " . $transMsg . " ErrorCode: " . $errorCode);
            $data = CommonController::retMsgUpdateProfile($module, $errorCode, $transMsg);
            $message = "[" . $module . "] Output: " . CJSON::encode($data);
            $appLogger->log($appLogger->logdate, "[response]", $message);
            $this->_sendResponse(200, CJSON::encode($data));
            $logMessage = 'MPSessionID is already expired. Please login again.';
            $logger->log($logger->logdate, "[UPDATEPROFILE ERROR]: MID " . $MID . " || ", $logMessage);
            $apiDetails = 'UPDATEPROFILE-Failed: MPSessionID is already expired. Please login again.. MID = ' . $MID;
            $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, '', $apiDetails, '', 2);
            if ($isInserted == 0) {
                $logMessage = "Failed to insert to APILogs.";
                $logger->log($logger->logdate, "[UPDATEPROFILE ERROR]: MID " . $MID . " || ", $logMessage);
            }

            exit;
        }

        if (isset($result)) {
            $isUpdated = $memberSessionsModel->updateTransactionDate($MID);
            if ($isUpdated == 0) {
                $logMessage = 'Failed to update transaction date in membersessions table WHERE MID = ' . $MID . ' AND SessionID = ' . $request['MPSessionID'];
                $transMsg = 'Transaction failed.';
                $errorCode = 4;
                Utilities::log("ReturnMessage: " . $transMsg . " ErrorCode: " . $errorCode);
                $data = CommonController::retMsgUpdateProfile($module, $errorCode, $transMsg);
                $message = "[" . $module . "] Output: " . CJSON::encode($data);
                $appLogger->log($appLogger->logdate, "[response]", $message);
                $this->_sendResponse(200, CJSON::encode($data));
                $logger->log($logger->logdate, "[UPDATEPROFILE ERROR]: MID " . $MID . " || ", $logMessage);
                $apiDetails = 'UPDATEPROFILE-UpdateTransDate-Failed: ' . 'MID = ' . $MID . ' SessionID = ' . $request['MPSessionID'];
                $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, '', $apiDetails, '', 2);
                if ($isInserted == 0) {
                    $logMessage = "Failed to insert to APILogs.";
                    $logger->log($logger->logdate, "[UPDATEPROFILE ERROR]: MID " . $MID . " || ", $logMessage);
                }

                exit;
            }
        }

        if (isset($request['FirstName']) && isset($request['LastName']) && isset($request['MobileNo'])
                && isset($request['EmailAddress']) && ((isset($request['IDNumber']) && isset($request['IDPresented'])) || (isset($request['Region']) && isset($request['City']))) && isset($request['Birthdate']) && isset($request['MPSessionID'])) {
            if (($request['FirstName'] == '') || ($request['LastName'] == '') || ($request['MobileNo'] == '') || ($request['EmailAddress'] == '')
                    || ($request['Birthdate'] == '') || ($request['MPSessionID'] == '')) {
                $transMsg = "One or more fields is not set or is blank.";
                $errorCode = 1;
                Utilities::log("ReturnMessage: " . $transMsg . " ErrorCode: " . $errorCode);
                $data = CommonController::retMsgUpdateProfile($module, $errorCode, $transMsg);
                $message = "[" . $module . "] Output: " . CJSON::encode($data);
                $appLogger->log($appLogger->logdate, "[response]", $message);
                $this->_sendResponse(200, CJSON::encode($data));
                $logMessage = 'One or more fields is not set or is blank.';
                $logger->log($logger->logdate, "[UPDATEPROFILE ERROR]: MID " . $MID . " || ", $logMessage);
                $apiDetails = 'UPDATEPROFILE-Failed: Invalid input parameters. ';
                $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, '', $apiDetails, '', 2);
                if ($isInserted == 0) {
                    $logMessage = "Failed to insert to APILogs.";
                    $logger->log($logger->logdate, "[UPDATEPROFILE ERROR]: MID " . $MID . " || ", $logMessage);
                }

                exit;
            } else if (($request['IDNumber'] == '' && $request['IDPresented'] == '') && ($request['Region'] == '' && $request['City'] == '')) {
                $transMsg = "One or more fields is not set or is blank.";
                $errorCode = 1;
                Utilities::log("ReturnMessage: " . $transMsg . " ErrorCode: " . $errorCode);
                $data = CommonController::retMsgUpdateProfile($module, $errorCode, $transMsg);
                $message = "[" . $module . "] Output: " . CJSON::encode($data);
                $appLogger->log($appLogger->logdate, "[response]", $message);
                $this->_sendResponse(200, CJSON::encode($data));
                $logMessage = 'One or more fields is not set or is blank.';
                $logger->log($logger->logdate, "[UPDATEPROFILE ERROR]: MID " . $MID . " || ", $logMessage);
                $apiDetails = 'UPDATEPROFILE-Failed: Invalid input parameters. ';
                $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, '', $apiDetails, '', 2);
                if ($isInserted == 0) {
                    $logMessage = "Failed to insert to APILogs.";
                    $logger->log($logger->logdate, "[UPDATEPROFILE ERROR]: MID " . $MID . " || ", $logMessage);
                }

                exit;
            } else if (strlen($request['FirstName']) < 2 || ($request['MiddleName'] != '' && strlen($request['MiddleName']) < 2) || strlen($request['LastName']) < 2) {
                $transMsg = "Name should not be less than 2 characters long.";
                $errorCode = 14;
                Utilities::log("ReturnMessage: " . $transMsg . " ErrorCode: " . $errorCode);
                $data = CommonController::retMsgUpdateProfile($module, $errorCode, $transMsg);
                $message = "[" . $module . "] Output: " . CJSON::encode($data);
                $appLogger->log($appLogger->logdate, "[response]", $message);
                $this->_sendResponse(200, CJSON::encode($data));
                $logMessage = 'Name should not be less than 2 characters long.';
                $logger->log($logger->logdate, "[UPDATEPROFILE ERROR]: MID " . $MID . " || ", $logMessage);
                $apiDetails = 'UPDATEPROFILE-Failed: Invalid input parameters. ';
                $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, '', $apiDetails, '', 2);
                if ($isInserted == 0) {
                    $logMessage = "Failed to insert to APILogs.";
                    $logger->log($logger->logdate, "[UPDATEPROFILE ERROR]: MID " . $MID . " || ", $logMessage);
                }

                exit;
            } else if (preg_match("/^[A-Za-z\s]+$/", trim($request['FirstName'])) == 0 || (trim($request['MiddleName'] != '') && preg_match("/^[A-Za-z\s]+$/", trim($request['MiddleName'])) == 0) || preg_match("/^[A-Za-z\s]+$/", trim($request['LastName'])) == 0 || (trim($request['NickName'] != '') && preg_match("/^[A-Za-z\s]+$/", trim($request['NickName'])) == 0)) {
                $transMsg = "Name should consist of letters and spaces only.";
                $errorCode = 17;
                Utilities::log("ReturnMessage: " . $transMsg . " ErrorCode: " . $errorCode);
                $data = CommonController::retMsgUpdateProfile($module, $errorCode, $transMsg);
                $message = "[" . $module . "] Output: " . CJSON::encode($data);
                $appLogger->log($appLogger->logdate, "[response]", $message);
                $this->_sendResponse(200, CJSON::encode($data));
                $logMessage = 'Name should consist of letters and spaces only.';
                $logger->log($logger->logdate, "[UPDATEPROFILE ERROR]: MID " . $MID . " || ", $logMessage);
                $apiDetails = 'UPDATEPROFILE-Failed: Invalid input parameters. ';
                $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, '', $apiDetails, '', 2);
                if ($isInserted == 0) {
                    $logMessage = "Failed to insert to APILogs.";
                    $logger->log($logger->logdate, "[UPDATEPROFILE ERROR]: MID " . $MID . " || ", $logMessage);
                }

                exit;
            } else if (($request['Password'] != '' && ctype_alnum($request['Password']) == FALSE) || ($request['IDNumber'] != '' && ctype_alnum($request['IDNumber']) == FALSE)) {
                $transMsg = "Password and ID Number should consist of letters and numbers only.";
                $errorCode = 18;
                Utilities::log("ReturnMessage: " . $transMsg . " ErrorCode: " . $errorCode);
                $data = CommonController::retMsgUpdateProfile($module, $errorCode, $transMsg);
                $message = "[" . $module . "] Output: " . CJSON::encode($data);
                $appLogger->log($appLogger->logdate, "[response]", $message);
                $this->_sendResponse(200, CJSON::encode($data));
                $logMessage = 'Password and ID Number should consist of letters and numbers only.';
                $logger->log($logger->logdate, "[UPDATEPROFILE ERROR]: MID " . $MID . " || ", $logMessage);
                $apiDetails = 'UPDATEPROFILE-Failed: Invalid input parameters. ';
                $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, '', $apiDetails, '', 2);
                if ($isInserted == 0) {
                    $logMessage = "Failed to insert to APILogs.";
                    $logger->log($logger->logdate, "[UPDATEPROFILE ERROR]: MID " . $MID . " || ", $logMessage);
                }

                exit;
            } else if ($request['Password'] != '' && strlen($request['Password']) < 5) {
                $transMsg = "Password should not be less than 5 characters long.";
                $errorCode = 19;
                Utilities::log("ReturnMessage: " . $transMsg . " ErrorCode: " . $errorCode);
                $data = CommonController::retMsgUpdateProfile($module, $errorCode, $transMsg);
                $message = "[" . $module . "] Output: " . CJSON::encode($data);
                $appLogger->log($appLogger->logdate, "[response]", $message);
                $this->_sendResponse(200, CJSON::encode($data));
                $logMessage = 'Password should not be less than 5 characters long.';
                $logger->log($logger->logdate, "[UPDATEPROFILE ERROR]: MID " . $MID . " || ", $logMessage);
                $apiDetails = 'UPDATEPROFILE-Failed: Invalid input parameters. ';
                $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, '', $apiDetails, '', 2);
                if ($isInserted == 0) {
                    $logMessage = "Failed to insert to APILogs.";
                    $logger->log($logger->logdate, "[UPDATEPROFILE ERROR]: MID " . $MID . " || ", $logMessage);
                }

                exit;
            } else if ((substr($request['MobileNo'], 0, 3) == "639" && strlen($request['MobileNo']) != 12) || (substr($request['MobileNo'], 0, 2) == "09" && strlen($request['MobileNo']) != 11)) {
                $transMsg = "Invalid Mobile Number.";
                $errorCode = 95;
                Utilities::log("ReturnMessage: " . $transMsg . " ErrorCode: " . $errorCode);
                $data = CommonController::retMsgUpdateProfile($module, $errorCode, $transMsg);
                $message = "[" . $module . "] Output: " . CJSON::encode($data);
                $appLogger->log($appLogger->logdate, "[response]", $message);
                $this->_sendResponse(200, CJSON::encode($data));
                $logMessage = 'Invalid Mobile Number.';
                $logger->log($logger->logdate, "[UPDATEPROFILE ERROR]: MID " . $MID . " || ", $logMessage);
                $apiDetails = 'UPDATEPROFILE-Failed: Invalid input parameters. ';
                $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, '', $apiDetails, '', 2);
                if ($isInserted == 0) {
                    $logMessage = "Failed to insert to APILogs.";
                    $logger->log($logger->logdate, "[UPDATEPROFILE ERROR]: MID " . $MID . " || ", $logMessage);
                }

                exit;
            } else if ((substr($request['AlternateMobileNo'], 0, 3) == "639" && strlen($request['AlternateMobileNo']) != 12) || (substr($request['AlternateMobileNo'], 0, 2) == "09" && strlen($request['AlternateMobileNo']) != 11)) {
                $transMsg = "Invalid Mobile Number.";
                $errorCode = 95;
                Utilities::log("ReturnMessage: " . $transMsg . " ErrorCode: " . $errorCode);
                $data = CommonController::retMsgUpdateProfile($module, $errorCode, $transMsg);
                $message = "[" . $module . "] Output: " . CJSON::encode($data);
                $appLogger->log($appLogger->logdate, "[response]", $message);
                $this->_sendResponse(200, CJSON::encode($data));
                $logMessage = 'Invalid Mobile Number.';
                $logger->log($logger->logdate, "[UPDATEPROFILE ERROR]: MID " . $MID . " || ", $logMessage);
                $apiDetails = 'UPDATEPROFILE-Failed: Invalid input parameters. ';
                $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, '', $apiDetails, '', 2);
                if ($isInserted == 0) {
                    $logMessage = "Failed to insert to APILogs.";
                    $logger->log($logger->logdate, "[UPDATEPROFILE ERROR]: MID " . $MID . " || ", $logMessage);
                }

                exit;
            } else if ((substr($request['MobileNo'], 0, 2) != '09' && substr($request['MobileNo'], 0, 3) != '639') && (substr($request['AlternateMobileNo'], 0, 2) != '09' && substr($request['AlternateMobileNo'], 0, 3) != '639')) {
                $transMsg = "Mobile number should begin with either '09' or '639'.";
                $errorCode = 69;
                Utilities::log("ReturnMessage: " . $transMsg . " ErrorCode: " . $errorCode);
                $data = CommonController::retMsgUpdateProfile($module, $errorCode, $transMsg);
                $message = "[" . $module . "] Output: " . CJSON::encode($data);
                $appLogger->log($appLogger->logdate, "[response]", $message);
                $this->_sendResponse(200, CJSON::encode($data));
                $logMessage = "Mobile number should begin with either '09' or '639'.";
                $logger->log($logger->logdate, "[UPDATEPROFILE ERROR]: MID " . $MID . " || ", $logMessage);
                $apiDetails = 'UPDATEPROFILE-Failed: Invalid input parameters. ';
                $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, '', $apiDetails, '', 2);
                if ($isInserted == 0) {
                    $logMessage = "Failed to insert to APILogs.";
                    $logger->log($logger->logdate, "[UPDATEPROFILE ERROR]: MID " . $MID . " || ", $logMessage);
                }

                exit;
            } else if (!is_numeric($request['MobileNo']) || ($request['AlternateMobileNo'] != '' && !is_numeric($request['AlternateMobileNo']))) {
                $transMsg = "Mobile number should consist of numbers only.";
                $errorCode = 16;
                Utilities::log("ReturnMessage: " . $transMsg . " ErrorCode: " . $errorCode);
                $data = CommonController::retMsgUpdateProfile($module, $errorCode, $transMsg);
                $message = "[" . $module . "] Output: " . CJSON::encode($data);
                $appLogger->log($appLogger->logdate, "[response]", $message);
                $this->_sendResponse(200, CJSON::encode($data));
                $logMessage = 'Mobile number should consist of numbers only.';
                $logger->log($logger->logdate, "[UPDATEPROFILE ERROR]: MID " . $MID . " || ", $logMessage);
                $apiDetails = 'UPDATEPROFILE-Failed: Invalid input parameters. ';
                $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, '', $apiDetails, '', 2);
                if ($isInserted == 0) {
                    $logMessage = "Failed to insert to APILogs.";
                    $logger->log($logger->logdate, "[UPDATEPROFILE ERROR]: MID " . $MID . " || ", $logMessage);
                }

                exit;
            } else if ($request['MobileNo'] == $request['AlternateMobileNo']) {
                $transMsg = "Mobile number should not be the same as alternate mobile number.";
                $errorCode = 86;
                Utilities::log("ReturnMessage: " . $transMsg . " ErrorCode: " . $errorCode);
                $data = CommonController::retMsgUpdateProfile($module, $errorCode, $transMsg);
                $message = "[" . $module . "] Output: " . CJSON::encode($data);
                $appLogger->log($appLogger->logdate, "[response]", $message);
                $this->_sendResponse(200, CJSON::encode($data));
                $logMessage = 'Mobile number should not be the same as alternate mobile number.';
                $logger->log($logger->logdate, "[UPDATEPROFILE ERROR]: MID " . $MID . " || ", $logMessage);
                $apiDetails = 'UPDATEPROFILE-Failed: Invalid input parameters. ';
                $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, '', $apiDetails, '', 2);
                if ($isInserted == 0) {
                    $logMessage = "Failed to insert to APILogs.";
                    $logger->log($logger->logdate, "[UPDATEPROFILE ERROR]: MID " . $MID . " || ", $logMessage);
                }

                exit;
            } else if (!Utilities::validateEmail($request['EmailAddress']) || ($request['AlternateEmail'] != '' && !Utilities::validateEmail($request['AlternateEmail']))) {
                $transMsg = "Invalid Email Address.";
                $errorCode = 5;
                Utilities::log("ReturnMessage: " . $transMsg . " ErrorCode: " . $errorCode);
                $data = CommonController::retMsgUpdateProfile($module, $errorCode, $transMsg);
                $message = "[" . $module . "] Output: " . CJSON::encode($data);
                $appLogger->log($appLogger->logdate, "[response]", $message);
                $this->_sendResponse(200, CJSON::encode($data));
                $logMessage = 'Invalid Email Address.';
                $logger->log($logger->logdate, "[UPDATEPROFILE ERROR]: MID " . $MID . " || ", $logMessage);
                $apiDetails = 'UPDATEPROFILE-Failed: Invalid input parameters. ';
                $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, '', $apiDetails, '', 2);
                if ($isInserted == 0) {
                    $logMessage = "Failed to insert to APILogs.";
                    $logger->log($logger->logdate, "[UPDATEPROFILE ERROR]: MID " . $MID . " || ", $logMessage);
                }

                exit;
            } else if ($request['EmailAddress'] == $request['AlternateEmail']) {
                $transMsg = "Email Address should not be the same as alternate email.";
                $errorCode = 87;
                Utilities::log("ReturnMessage: " . $transMsg . " ErrorCode: " . $errorCode);
                $data = CommonController::retMsgUpdateProfile($module, $errorCode, $transMsg);
                $message = "[" . $module . "] Output: " . CJSON::encode($data);
                $appLogger->log($appLogger->logdate, "[response]", $message);
                $this->_sendResponse(200, CJSON::encode($data));
                $logMessage = 'Email Address should not be the same as alternate email.';
                $logger->log($logger->logdate, "[UPDATEPROFILE ERROR]: MID " . $MID . " || ", $logMessage);
                $apiDetails = 'UPDATEPROFILE-Failed: Invalid input parameters. ';
                $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, '', $apiDetails, '', 2);
                if ($isInserted == 0) {
                    $logMessage = "Failed to insert to APILogs.";
                    $logger->log($logger->logdate, "[UPDATEPROFILE ERROR]: MID " . $MID . " || ", $logMessage);
                }

                exit;
            } else if ($request['Gender'] != '' && $request['Gender'] != 1 && $request['Gender'] != 2) {
                $transMsg = "Please input 1 for male or 2 for female.";
                $errorCode = 77;
                Utilities::log("ReturnMessage: " . $transMsg . " ErrorCode: " . $errorCode);
                $data = CommonController::retMsgUpdateProfile($module, $errorCode, $transMsg);
                $message = "[" . $module . "] Output: " . CJSON::encode($data);
                $appLogger->log($appLogger->logdate, "[response]", $message);
                $this->_sendResponse(200, CJSON::encode($data));
                $logMessage = 'Please input 1 for male or 2 for female.';
                $logger->log($logger->logdate, "[UPDATEPROFILE ERROR]: MID " . $MID . " || ", $logMessage);
                $apiDetails = 'UPDATEPROFILE-Failed: Invalid input parameters. ';
                $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, '', $apiDetails, '', 2);
                if ($isInserted == 0) {
                    $logMessage = "Failed to insert to APILogs.";
                    $logger->log($logger->logdate, "[UPDATEPROFILE ERROR]: MID " . $MID . " || ", $logMessage);
                }

                exit;
            } else if ($request['IDPresented'] != '' && $request['IDPresented'] != 1 && $request['IDPresented'] != 2 && $request['IDPresented'] != 3 && $request['IDPresented'] != 4 && $request['IDPresented'] != 5 && $request['IDPresented'] != 6 && $request['IDPresented'] != 7 && $request['IDPresented'] != 8 && $request['IDPresented'] != 9) {
                $transMsg = "Please input a valid ID Presented (1 to 9).";
                $errorCode = 78;
                Utilities::log("ReturnMessage: " . $transMsg . " ErrorCode: " . $errorCode);
                $data = CommonController::retMsgUpdateProfile($module, $errorCode, $transMsg);
                $message = "[" . $module . "] Output: " . CJSON::encode($data);
                $appLogger->log($appLogger->logdate, "[response]", $message);
                $this->_sendResponse(200, CJSON::encode($data));
                $logMessage = 'Please input a valid ID Presented (1 to 9).';
                $logger->log($logger->logdate, "[UPDATEPROFILE ERROR]: MID " . $MID . " || ", $logMessage);
                $apiDetails = 'UPDATEPROFILE-Failed: Invalid input parameters. ';
                $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, '', $apiDetails, '', 2);
                if ($isInserted == 0) {
                    $logMessage = "Failed to insert to APILogs.";
                    $logger->log($logger->logdate, "[UPDATEPROFILE ERROR]: MID " . $MID . " || ", $logMessage);
                }

                exit;
            } else if ($request['Nationality'] != '' && $request['Nationality'] != 1 && $request['Nationality'] != 2 && $request['Nationality'] != 3 && $request['Nationality'] != 4 && $request['Nationality'] != 5 && $request['Nationality'] != 6) {
                $transMsg = "Please input a valid Nationality (1 to 6).";
                $errorCode = 79;
                Utilities::log("ReturnMessage: " . $transMsg . " ErrorCode: " . $errorCode);
                $data = CommonController::retMsgUpdateProfile($module, $errorCode, $transMsg);
                $message = "[" . $module . "] Output: " . CJSON::encode($data);
                $appLogger->log($appLogger->logdate, "[response]", $message);
                $this->_sendResponse(200, CJSON::encode($data));
                $logMessage = 'Please input a valid Nationality (1 to 6).';
                $logger->log($logger->logdate, "[UPDATEPROFILE ERROR]: MID " . $MID . " || ", $logMessage);
                $apiDetails = 'UPDATEPROFILE-Failed: Invalid input parameters. ';
                $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, '', $apiDetails, '', 2);
                if ($isInserted == 0) {
                    $logMessage = "Failed to insert to APILogs.";
                    $logger->log($logger->logdate, "[UPDATEPROFILE ERROR]: MID " . $MID . " || ", $logMessage);
                }

                exit;
            } else if ($request['Occupation'] != '' && $request['Occupation'] != 1 && $request['Occupation'] != 2 && $request['Occupation'] != 3 && $request['Occupation'] != 4 && $request['Occupation'] != 5) {
                $transMsg = "Please input a valid Occupation (1 to 5).";
                $errorCode = 81;
                Utilities::log("ReturnMessage: " . $transMsg . " ErrorCode: " . $errorCode);
                $data = CommonController::retMsgUpdateProfile($module, $errorCode, $transMsg);
                $message = "[" . $module . "] Output: " . CJSON::encode($data);
                $appLogger->log($appLogger->logdate, "[response]", $message);
                $this->_sendResponse(200, CJSON::encode($data));
                $logMessage = 'Please input a valid Occupation (1 to 5).';
                $logger->log($logger->logdate, "[UPDATEPROFILE ERROR]: MID " . $MID . " || ", $logMessage);
                $apiDetails = 'UPDATEPROFILE-Failed: Invalid input parameters. ';
                $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, '', $apiDetails, '', 2);
                if ($isInserted == 0) {
                    $logMessage = "Failed to insert to APILogs.";
                    $logger->log($logger->logdate, "[UPDATEPROFILE ERROR]: MID " . $MID . " || ", $logMessage);
                }

                exit;
            } else if ($request['IsSmoker'] != '' && $request['IsSmoker'] != 1 && $request['IsSmoker'] != 2) {
                $transMsg = "Please input 1 for smoker or 2 for non-smoker.";
                $errorCode = 82;
                Utilities::log("ReturnMessage: " . $transMsg . " ErrorCode: " . $errorCode);
                $data = CommonController::retMsgUpdateProfile($module, $errorCode, $transMsg);
                $message = "[" . $module . "] Output: " . CJSON::encode($data);
                $appLogger->log($appLogger->logdate, "[response]", $message);
                $this->_sendResponse(200, CJSON::encode($data));
                $logMessage = 'Please input 1 for smoker or 2 for non-smoker.';
                $logger->log($logger->logdate, "[UPDATEPROFILE ERROR]: MID " . $MID . " || ", $logMessage);
                $apiDetails = 'UPDATEPROFILE-Failed: Invalid input parameters. ';
                $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, '', $apiDetails, '', 2);
                if ($isInserted == 0) {
                    $logMessage = "Failed to insert to APILogs.";
                    $logger->log($logger->logdate, "[UPDATEPROFILE ERROR]: MID " . $MID . " || ", $logMessage);
                }

                exit;
            } else if ($this->_validateDate($request['Birthdate']) == FALSE) {
                $transMsg = "Please input a valid Date (YYYY-MM-DD).";
                $errorCode = 80;
                Utilities::log("ReturnMessage: " . $transMsg . " ErrorCode: " . $errorCode);
                $data = CommonController::retMsgUpdateProfile($module, $errorCode, $transMsg);
                $message = "[" . $module . "] Output: " . CJSON::encode($data);
                $appLogger->log($appLogger->logdate, "[response]", $message);
                $this->_sendResponse(200, CJSON::encode($data));
                $logMessage = 'Please input a valid Date.';
                $logger->log($logger->logdate, "[UPDATEPROFILE ERROR]: MID " . $MID . " || ", $logMessage);
                $apiDetails = 'UPDATEPROFILE-Failed: Invalid input parameters. ';
                $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, '', $apiDetails, '', 2);
                if ($isInserted == 0) {
                    $logMessage = "Failed to insert to APILogs.";
                    $logger->log($logger->logdate, "[UPDATEPROFILE ERROR]: MID " . $MID . " || ", $logMessage);
                }

                exit;
            } else {

                //start of declaration of models to be used
                $memberInfoModel = new MemberInfoModel();
                $memberCardsModel = new MemberCardsModel();
                $membershipTempModel = new MembershipTempModel();
                $membersModel = new MembersModel();
                $auditTrailModel = new AuditTrailModel();

                $emailAddress = trim($request['EmailAddress']);
                $firstname = trim($request['FirstName']);
                $middlename = trim($request['MiddleName']);
                $lastname = trim($request['LastName']);
                $nickname = trim($request['NickName']);
                if (trim($request['Password']) != '')
                    $password = md5(trim($request['Password']));
                else
                    $password = '';
                $permanentAddress = trim($request['PermanentAdd']);
                $mobileNumber = trim($request['MobileNo']);
                $alternateMobileNumber = trim($request['AlternateMobileNo']);

                $alternateEmail = trim($request['AlternateEmail']);
                $idNumber = trim($request['IDNumber']);
                $idPresented = trim($request['IDPresented']);
                $gender = trim($request['Gender']);
                $birthdate = trim($request['Birthdate']);
                $tz = new DateTimeZone("Asia/Manila");
                $age = DateTime::createFromFormat('Y-m-d', $birthdate, $tz)->diff(new DateTime('now', $tz))->y;
                $nationalityID = trim($request['Nationality']);
                $occupationID = trim($request['Occupation']);
                $isSmoker = trim($request['IsSmoker']);
                $mpSessionID = trim($request['MPSessionID']);
                $region = trim($request['Region']);
                $city = trim($request['City']);

                $memberSessions = $memberSessionsModel->getMID($mpSessionID);

                if ($memberSessions)
                    $MID = $memberSessions['MID'];
                else {
                    $transMsg = "MPSessionID does not exist.";
                    $errorCode = 13;
                    Utilities::log("ReturnMessage: " . $transMsg . " ErrorCode: " . $errorCode);
                    $data = CommonController::retMsgUpdateProfile($module, $errorCode, $transMsg);
                    $message = "[" . $module . "] Output: " . CJSON::encode($data);
                    $appLogger->log($appLogger->logdate, "[response]", $message);
                    $this->_sendResponse(200, CJSON::encode($data));
                    $logMessage = 'MPSessionID does not exist.';
                    $logger->log($logger->logdate, "[UPDATEPROFILE ERROR]: MID " . $MID . " || ", $logMessage);
                    $apiDetails = 'UPDATEPROFILE-Failed: There is no active session. MID = ' . $MID;
                    $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, '', $apiDetails, '', 2);
                    if ($isInserted == 0) {
                        $logMessage = "Failed to insert to APILogs.";
                        $logger->log($logger->logdate, "[UPDATEPROFILE ERROR]: MID " . $MID . " || ", $logMessage);
                    }

                    exit;
                }

                $isExist = $memberSessionsModel->checkIfSessionExist($MID, $mpSessionID);

                if (count($isExist) > 0) {
                    //check if from old to newly migrated card
                    $mid = $MID;
                    $cardNumber = $memberCardsModel->getTempMigratedCardUsingMID($mid);

                    $tempAcctCode = $membershipTempModel->getTempCodeUsingCard($cardNumber);
                    $tempHasEmailCount = $membershipTempModel->checkIfEmailExistsWithMID($mid, $emailAddress);
                    if (is_null($tempHasEmailCount))
                        $tempHasEmailCount = 0;
                    else
                        $tempHasEmailCount = $tempHasEmailCount['COUNT'];

                    $hasEmailCount = $memberInfoModel->checkIfEmailExistsWithMID($MID, $emailAddress);

                    if (is_null($hasEmailCount))
                        $hasEmailCount = 0;
                    else
                        $hasEmailCount = $hasEmailCount['COUNT'];

                    if (($hasEmailCount > 0) && ($tempHasEmailCount > 0)) {
                        $transMsg = "Sorry, " . $emailAddress . " already belongs to an existing account. Please enter another email address.";
                        $errorCode = 21;
                        Utilities::log("ReturnMessage: " . $transMsg . " ErrorCode: " . $errorCode);
                        $data = CommonController::retMsgUpdateProfile($module, $errorCode, $transMsg);
                        $message = "[" . $module . "] Output: " . CJSON::encode($data);
                        $appLogger->log($appLogger->logdate, "[response]", $message);
                        $this->_sendResponse(200, CJSON::encode($data));
                        $logMessage = 'Sorry, ' . $emailAddress . ' already belongs to an existing account. Please enter another email address.';
                        $logger->log($logger->logdate, "[UPDATEPROFILE ERROR]: MID " . $MID . " || ", $logMessage);
                        $apiDetails = 'UPDATEPROFILE-Failed: Email is already used. ';
                        $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, '', $apiDetails, '', 2);
                        if ($isInserted == 0) {
                            $logMessage = "Failed to insert to APILogs.";
                            $logger->log($logger->logdate, "[UPDATEPROFILE ERROR]: MID " . $MID . " || ", $logMessage);
                        }

                        exit;
                    } else if ($age < 21) {
                        $transMsg = "Must be at least 21 years old to register.";
                        $errorCode = 89;
                        Utilities::log("ReturnMessage: " . $transMsg . " ErrorCode: " . $errorCode);
                        $data = CommonController::retMsgUpdateProfile($module, $errorCode, $transMsg);
                        $message = "[" . $module . "] Output: " . CJSON::encode($data);
                        $appLogger->log($appLogger->logdate, "[response]", $message);
                        $this->_sendResponse(200, CJSON::encode($data));
                        $logMessage = 'Must be at least 21 years old to register.';
                        $logger->log($logger->logdate, "[UPDATEPROFILE ERROR]: MID " . $MID . " || ", $logMessage);
                        $apiDetails = 'UPDATEPROFILE-Failed: Player is under 21. Name = ' . $firstname . ' ' . $lastname . ', Birthdate = ' . $birthdate;
                        $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, '', $apiDetails, '', 2);
                        if ($isInserted == 0) {
                            $logMessage = "Failed to insert to APILogs.";
                            $logger->log($logger->logdate, "[UPDATEPROFILE ERROR]: MID " . $MID . " || ", $logMessage);
                        }

                        exit;
                    } else {
                        $refID = $firstname . ' ' . $lastname;

                        //$hasEmail = $membersModel->checkIfUsernameExistsWithMID($MID, $emailAddress);
                        $tempHasEmail = $membershipTempModel->checkIfUsernameExistsWithTAC($mid, $emailAddress);

                        if (is_null($tempHasEmail))
                            $tempHasEmail = 0;
                        else
                            $tempHasEmail = $tempHasEmail['COUNT'];

                        if (($tempHasEmail > 0)) {
                            $transMsg = "Sorry, " . $emailAddress . " already belongs to an existing account. Please enter another email address.";
                            $errorCode = 21;
                            Utilities::log("ReturnMessage: " . $transMsg . " ErrorCode: " . $errorCode);
                            $data = CommonController::retMsgUpdateProfile($module, $errorCode, $transMsg);
                            $message = "[" . $module . "] Output: " . CJSON::encode($data);
                            $appLogger->log($appLogger->logdate, "[response]", $message);
                            $this->_sendResponse(200, CJSON::encode($data));
                            $logMessage = 'Sorry, ' . $emailAddress . ' already belongs to an existing account. Please enter another email address.';
                            $logger->log($logger->logdate, "[UPDATEPROFILE ERROR]: MID " . $MID . " || ", $logMessage);
                            $apiDetails = 'UPDATEPROFILE-Failed: Email is already used. ';
                            $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, '', $apiDetails, '', 2);
                            if ($isInserted == 0) {
                                $logMessage = "Failed to insert to APILogs.";
                                $logger->log($logger->logdate, "[UPDATEPROFILE ERROR]: MID " . $MID . " || ", $logMessage);
                            }

                            exit;
                        }
                        //proceed with the updating of member profile
                        if ($region != '' && $city != '' && $idNumber == '' && $idPresented == '')
                            $result = $memberInfoModel->updateProfilev2($firstname, $middlename, $lastname, $nickname, $mid, $permanentAddress, $mobileNumber, $alternateMobileNumber, $emailAddress, $alternateEmail, $birthdate, $nationalityID, $occupationID, $gender, $isSmoker, $region, $city);
                        else if ($region == '' && $city == '' && $idNumber != '' && $idPresented != '')
                            $result = $memberInfoModel->updateProfile($firstname, $middlename, $lastname, $nickname, $mid, $permanentAddress, $mobileNumber, $alternateMobileNumber, $emailAddress, $alternateEmail, $birthdate, $nationalityID, $occupationID, $idNumber, $idPresented, $gender, $isSmoker);
                        else if ($region != '' && $city != '' && $idNumber != '' && $idPresented != '')
                            $result = $memberInfoModel->updateProfilev3($firstname, $middlename, $lastname, $nickname, $mid, $permanentAddress, $mobileNumber, $alternateMobileNumber, $emailAddress, $alternateEmail, $birthdate, $nationalityID, $occupationID, $idNumber, $idPresented, $gender, $isSmoker, $region, $city);
                        else {
                            $transMsg = 'One or more fields is not set or is blank.';
                            $errorCode = 1;
                            $data = CommonController::retMsgUpdateProfile($module, $errorCode, $transMsg);
                            $message = "[" . $module . "] Output: " . CJSON::encode($data);
                            $appLogger->log($appLogger->logdate, "[response]", $message);
                            $this->_sendResponse(200, CJSON::encode($data));
                            $logMessage = 'One or more fields is not set or is blank.';
                            $logger->log($logger->logdate, "[UPDATEPROFILE ERROR]: MID " . $MID . " || ", $logMessage);
                            $apiDetails = 'UPDATEPROFILE-Failed: Invalid input parameters.';
                            $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, '', $apiDetails, '', 2);
                            if ($isInserted == 0) {
                                $logMessage = "Failed to insert to APILogs.";
                                $logger->log($logger->logdate, "[UPDATEPROFILE ERROR]: MID " . $MID . " || ", $logMessage);
                            }

                            exit;
                        }

                        if ($result > 0) {
                            $result2 = $membersModel->updateMemberUsername($mid, $emailAddress, $password);

                            if ($result2 > 0) {
                                $result3 = $membershipTempModel->updateTempEmail($mid, $emailAddress);

                                if ($result3 > 0) {
                                    $result4 = $membershipTempModel->updateTempMemberUsername($tempAcctCode, $emailAddress, $password);

                                    if ($result4 > 0) {
                                        $isSuccessful = $auditTrailModel->logEvent(AuditTrailModel::API_UPDATE_PROFILE, 'Email: ' . $emailAddress, array('MID' => $MID, 'SessionID' => $mpSessionID));
                                        if ($isSuccessful == 0) {
                                            $logMessage = "Failed to insert to Audittrail.";
                                            $logger->log($logger->logdate, "[UPDATEPROFILE ERROR]: MID " . $MID . " || ", $logMessage);
                                        }

                                        $result5 = $memberInfoModel->updateProfileDateUpdated($MID, $mid);
                                        $result6 = $membershipTempModel->updateTempProfileDateUpdated($MID, $mid);

                                        if ($result5 > 0 && $result6 > 0) {
                                            $transMsg = 'No Error, Transaction successful.';
                                            $errorCode = 0;
                                            $data = CommonController::retMsgUpdateProfile($module, $errorCode, $transMsg);
                                            $message = "[" . $module . "] Output: " . CJSON::encode($data);
                                            $appLogger->log($appLogger->logdate, "[response]", $message);
                                            $this->_sendResponse(200, CJSON::encode($data));
                                            $logMessage = 'Update profile successful.';
                                            $logger->log($logger->logdate, "[UPDATEPROFILE SUCCESSFUL]: MID " . $MID . " || ", $logMessage);
                                            $apiDetails = 'UPDATEPROFILE-UpdateProfile/TempDateUpdated-Success: Username = ' . $emailAddress . ' MID = ' . $MID;
                                            $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, $refID, $apiDetails, '', 1);
                                            if ($isInserted == 0) {
                                                $logMessage = "Failed to insert to APILogs.";
                                                $logger->log($logger->logdate, "[UPDATEPROFILE ERROR]: MID " . $MID . " || ", $logMessage);
                                            }

                                            exit;
                                        } else {
                                            $transMsg = 'Transaction failed.';
                                            $errorCode = 4;
                                            Utilities::log("ReturnMessage: " . $transMsg . " ErrorCode: " . $errorCode);
                                            $data = CommonController::retMsgUpdateProfile($module, $errorCode, $transMsg);
                                            $message = "[" . $module . "] Output: " . CJSON::encode($data);
                                            $appLogger->log($appLogger->logdate, "[response]", $message);
                                            $this->_sendResponse(200, CJSON::encode($data));
                                            $logMessage = 'Transaction failed.';
                                            $logger->log($logger->logdate, "[UPDATEPROFILE ERROR]: MID " . $MID . " || ", $logMessage);
                                            $apiDetails = 'UPDATEPROFILE-UpdateProfile/TempDateUpdated-Failed: Username = ' . $emailAddress . ' MID = ' . $MID;
                                            $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, $refID, $apiDetails, '', 2);
                                            if ($isInserted == 0) {
                                                $logMessage = "Failed to insert to APILogs.";
                                                $logger->log($logger->logdate, "[UPDATEPROFILE ERROR]: MID " . $MID . " || ", $logMessage);
                                            }

                                            exit;
                                        }
                                    } else {
                                        $transMsg = 'Transaction failed.';
                                        $errorCode = 4;
                                        Utilities::log("ReturnMessage: " . $transMsg . " ErrorCode: " . $errorCode);
                                        $data = CommonController::retMsgUpdateProfile($module, $errorCode, $transMsg);
                                        $message = "[" . $module . "] Output: " . CJSON::encode($data);
                                        $appLogger->log($appLogger->logdate, "[response]", $message);
                                        $this->_sendResponse(200, CJSON::encode($data));
                                        $logMessage = 'Transaction failed.';
                                        $logger->log($logger->logdate, "[UPDATEPROFILE ERROR]: MID " . $MID . " || ", $logMessage);
                                        $apiDetails = 'UPDATEPROFILE-UpdateTempMemberUsername-Failed: Username = ' . $emailAddress . ' MID = ' . $MID;
                                        $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, $refID, $apiDetails, '', 2);
                                        if ($isInserted == 0) {
                                            $logMessage = "Failed to insert to APILogs.";
                                            $logger->log($logger->logdate, "[UPDATEPROFILE ERROR]: MID " . $MID . " || ", $logMessage);
                                        }

                                        exit;
                                    }
                                } else {
                                    $transMsg = 'Transaction failed.';
                                    $errorCode = 4;
                                    Utilities::log("ReturnMessage: " . $transMsg . " ErrorCode: " . $errorCode);
                                    $data = CommonController::retMsgUpdateProfile($module, $errorCode, $transMsg);
                                    $message = "[" . $module . "] Output: " . CJSON::encode($data);
                                    $appLogger->log($appLogger->logdate, "[response]", $message);
                                    $this->_sendResponse(200, CJSON::encode($data));
                                    $logMessage = 'Transaction failed.';
                                    $logger->log($logger->logdate, "[UPDATEPROFILE ERROR]: MID " . $MID . " || ", $logMessage);
                                    $apiDetails = 'UPDATEPROFILE-UpdateTempEmail-Failed: Username = ' . $emailAddress . ' MID = ' . $MID;
                                    $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, $refID, $apiDetails, '', 2);
                                    if ($isInserted == 0) {
                                        $logMessage = "Failed to insert to APILogs.";
                                        $logger->log($logger->logdate, "[UPDATEPROFILE ERROR]: MID " . $MID . " || ", $logMessage);
                                    }

                                    exit;
                                }
                            } else {
                                $transMsg = 'Transaction failed.';
                                $errorCode = 4;
                                Utilities::log("ReturnMessage: " . $transMsg . " ErrorCode: " . $errorCode);
                                $data = CommonController::retMsgUpdateProfile($module, $errorCode, $transMsg);
                                $message = "[" . $module . "] Output: " . CJSON::encode($data);
                                $appLogger->log($appLogger->logdate, "[response]", $message);
                                $this->_sendResponse(200, CJSON::encode($data));
                                $logMessage = 'Transaction failed.';
                                $logger->log($logger->logdate, "[UPDATEPROFILE ERROR]: MID " . $MID . " || ", $logMessage);
                                $apiDetails = 'UPDATEPROFILE-UpdateMemberUsername-Failed: Username = ' . $emailAddress . ' MID = ' . $mid;
                                $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, $refID, $apiDetails, '', 2);
                                if ($isInserted == 0) {
                                    $logMessage = "Failed to insert to APILogs.";
                                    $logger->log($logger->logdate, "[UPDATEPROFILE ERROR]: MID " . $MID . " || ", $logMessage);
                                }

                                exit;
                            }
                        } else {
                            $transMsg = 'Transaction failed.';
                            $errorCode = 4;
                            Utilities::log("ReturnMessage: " . $transMsg . " ErrorCode: " . $errorCode);
                            $data = CommonController::retMsgUpdateProfile($module, $errorCode, $transMsg);
                            $message = "[" . $module . "] Output: " . CJSON::encode($data);
                            $appLogger->log($appLogger->logdate, "[response]", $message);
                            $this->_sendResponse(200, CJSON::encode($data));
                            $logMessage = 'Transaction failed.';
                            $logger->log($logger->logdate, "[UPDATEPROFILE ERROR]: MID " . $MID . " || ", $logMessage);
                            $apiDetails = 'UPDATEPROFILE-UpdateProfileMemberInfo-Failed: Name = ' . $firstname . ' ' . $middlename . ' ' . $lastname . ' MID = ' . $mid;
                            $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, $refID, $apiDetails, '', 2);
                            if ($isInserted == 0) {
                                $logMessage = "Failed to insert to APILogs.";
                                $logger->log($logger->logdate, "[UPDATEPROFILE ERROR]: MID " . $MID . " || ", $logMessage);
                            }

                            exit;
                        }
                    }
                } else {
                    $transMsg = "MPSessionID does not exist.";
                    $errorCode = 13;
                    Utilities::log("ReturnMessage: " . $transMsg . " ErrorCode: " . $errorCode);
                    $data = CommonController::retMsgUpdateProfile($module, $errorCode, $transMsg);
                    $message = "[" . $module . "] Output: " . CJSON::encode($data);
                    $appLogger->log($appLogger->logdate, "[response]", $message);
                    $this->_sendResponse(200, CJSON::encode($data));
                    $logMessage = 'MPSessionID does not exist.';
                    $logger->log($logger->logdate, "[UPDATEPROFILE ERROR]: MID " . $MID . " || ", $logMessage);
                    $apiDetails = 'UPDATEPROFILE-Failed: There is no active session. ';
                    $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, '', $apiDetails, '', 2);
                    if ($isInserted == 0) {
                        $logMessage = "Failed to insert to APILogs.";
                        $logger->log($logger->logdate, "[UPDATEPROFILE ERROR]: MID " . $MID . " || ", $logMessage);
                    }

                    exit;
                }
            }
        } else {
            $transMsg = "One or more fields is not set or is blank.";
            $errorCode = 1;
            Utilities::log("ReturnMessage: " . $transMsg . " ErrorCode: " . $errorCode);
            $data = CommonController::retMsgUpdateProfile($module, $errorCode, $transMsg);
            $message = "[" . $module . "] Output: " . CJSON::encode($data);
            $appLogger->log($appLogger->logdate, "[response]", $message);
            $this->_sendResponse(200, CJSON::encode($data));
            $logMessage = 'One or more fields is not set or is blank.';
            $logger->log($logger->logdate, "[UPDATEPROFILE ERROR]: MID " . $MID . " || ", $logMessage);
            $apiDetails = 'UPDATEPROFILE-Failed: Invalid input parameters.';
            $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, '', $apiDetails, '', 2);
            if ($isInserted == 0) {
                $logMessage = "Failed to insert to APILogs.";
                $logger->log($logger->logdate, "[UPDATEPROFILE ERROR]: MID " . $MID . " || ", $logMessage);
            }

            exit;
        }
    }

    public function actionCheckPoints() {

        $request = $this->_readJsonRequest();

        $transMsg = '';
        $errorCode = '';
        $module = 'CheckPoints';
        $apiMethod = 6;
        $message = '';

        $appLogger = new AppLogger();

        $paramval = CJSON::encode($request);
        $message = "[" . $module . "] Input: " . $paramval;
        $appLogger->log($appLogger->logdate, "[request]", $message);

        $logger = new ErrorLogger();
        $apiLogsModel = new APILogsModel();

        if (isset($request['CardNumber'])) {
            if ($request['CardNumber'] == '') {
                $transMsg = "One or more fields is not set or is blank.";
                $errorCode = 1;
                Utilities::log("ReturnMessage: " . $transMsg . " ErrorCode: " . $errorCode);
                $data = CommonController::retMsgCheckPoints($module, '', '', $errorCode, $transMsg);
                $message = "[" . $module . "] Output: " . CJSON::encode($data);
                $appLogger->log($appLogger->logdate, "[response]", $message);
                $this->_sendResponse(200, CJSON::encode($data));
                $logMessage = 'One or more fields is not set or is blank.';
                $logger->log($logger->logdate, "[CHECKPOINTS ERROR]: " . $request['CardNumber'] . " || ", $logMessage);
                $apiDetails = 'CHECKPOINTS-Failed: Invalid input parameters.';
                $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, '', $apiDetails, '', 2);
                if ($isInserted == 0) {
                    $logMessage = "Failed to insert to APILogs.";
                    $logger->log($logger->logdate, "[CHECKPOINTS ERROR]: " . $request['CardNumber'] . " || ", $logMessage);
                }

                exit;
            } else {
                $cardNumber = trim($request['CardNumber']);

                $refID = $cardNumber;
                //start of declaration of models to be used
                $memberCardsModel = new MemberCardsModel();
                $auditTrailModel = new AuditTrailModel();
//                $pcwsWrapper = new PcwsWrapper();
//
//                $result = $pcwsWrapper->getCompPoints($cardNumber, 1);
//                if ($result) {
//                    $currentPoints = $result['GetCompPoints']['CompBalance'];
//                } else {
//                    $transMsg = "Cannot access PCWS API.";
//                    $errorCode = 120;
//                    Utilities::log("ReturnMessage: " . $transMsg . " ErrorCode: " . $errorCode);
//                    $data = CommonController::retMsgCheckPoints($module, '', '', $errorCode, $transMsg);
//                    $message = "[" . $module . "] Output: " . CJSON::encode($data);
//                    $appLogger->log($appLogger->logdate, "[response]", $message);
//                    $this->_sendResponse(200, CJSON::encode($data));
//                    $logMessage = 'Cannot access PCWS API.';
//                    $logger->log($logger->logdate, "[CHECKPOINTS ERROR]: " . $cardNumber . " || ", $logMessage);
//                    $apiDetails = 'CHECKPOINTS-Failed: Cannot access PCWS API. Card Number = ' . $cardNumber;
//                    $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, $refID, $apiDetails, '', 2);
//                    if ($isInserted == 0) {
//                        $logMessage = "Failed to insert to APILogs.";
//                        $logger->log($logger->logdate, "[CHECKPOINTS ERROR]: " . $cardNumber . " || ", $logMessage);
//                    }
//
//                    exit;
//                }

                $memberDetails = $memberCardsModel->getMemberDetailsByCard($cardNumber);

                if (!empty($memberDetails))
                    $MID = $memberDetails['MID'];
                else
                    $MID = 0;

                if (!empty($memberDetails)) {
 		    $currentPoints = $memberDetails['CurrentPoints'];

                    $status = $memberDetails['Status'];

                    switch ($status) {
                        case 0: $message = 'Card is Inactive';
                            break;
                        case 1: $message = $currentPoints;
                            break;
                        case 5: $message = $currentPoints;
                            break;
                        case 2: $message = 'Card is Deactivated';
                            break;
                        case 7: $message = 'Card is Newly Migrated.';
                            break;
                        case 8: $message = 'Card is Temporarily Migrated';
                            break;
                        case 9: $message = 'Card is Banned';
                            break;
                        default: $message = 'Card is Invalid';
                            break;
                    }

                    $isSuccessful = $auditTrailModel->logEvent(AuditTrailModel::API_CHECK_POINTS, 'CardNumber: ' . $cardNumber, array('MID' => $MID, 'SessionID' => ''));
                    if ($isSuccessful == 0) {
                        $logMessage = "Failed to insert to Audittrail.";
                        $logger->log($logger->logdate, "[CHECKPOINTS ERROR]: " . $cardNumber . " || ", $logMessage);
                    }

                    $transMsg = $message;
                    if ($status == 1 || $status == 5)
                        $errorCode = 0;
                    else if ($status == 0)
                        $errorCode = 6;
                    else if ($status == 2)
                        $errorCode = 11;
                    else if ($status == 7)
                        $errorCode = 7;
                    else if ($status == 8)
                        $errorCode = 8;
                    else if ($status == 9)
                        $errorCode = 9;
                    else
                        $errorCode = 10;

                    $data = CommonController::retMsgCheckPoints($module, $currentPoints, $cardNumber, $errorCode, $transMsg);
                    $message = "[" . $module . "] Output: " . CJSON::encode($data);
                    $appLogger->log($appLogger->logdate, "[response]", $message);
                    $this->_sendResponse(200, CJSON::encode($data));

                    $logMessage = 'Check Points is successful.';
                    $logger->log($logger->logdate, "[CHECKPOINTS SUCCESSFUL]: " . $cardNumber . " || ", $logMessage);
                    $apiDetails = 'CHECKPOINTS-Successful: MID = ' . $MID;
                    $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, $refID, $apiDetails, '', 2);
                    if ($isInserted == 0) {
                        $logMessage = "Failed to insert to APILogs.";
                        $logger->log($logger->logdate, "[CHECKPOINTS ERROR]: " . $cardNumber . " || ", $logMessage);
                    }

                    exit;
                } else {
                    $transMsg = "Card is Invalid.";
                    $errorCode = 10;
                    Utilities::log("ReturnMessage: " . $transMsg . " ErrorCode: " . $errorCode);
                    $data = CommonController::retMsgCheckPoints($module, '', '', $errorCode, $transMsg);
                    $message = "[" . $module . "] Output: " . CJSON::encode($data);
                    $appLogger->log($appLogger->logdate, "[response]", $message);
                    $this->_sendResponse(200, CJSON::encode($data));
                    $logMessage = 'Card is Invalid.';
                    $logger->log($logger->logdate, "[CHECKPOINTS ERROR]: " . $cardNumber . " || ", $logMessage);
                    $apiDetails = 'CHECKPOINTS-Failed: Membership card is invalid. Card Number = ' . $cardNumber;
                    $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, $refID, $apiDetails, '', 2);
                    if ($isInserted == 0) {
                        $logMessage = "Failed to insert to APILogs.";
                        $logger->log($logger->logdate, "[CHECKPOINTS ERROR]: " . $cardNumber . " || ", $logMessage);
                    }

                    exit;
                }
            }
        } else {
            $transMsg = "One or more fields is not set or is blank.";
            $errorCode = 1;
            Utilities::log("ReturnMessage: " . $transMsg . " ErrorCode: " . $errorCode);
            $data = CommonController::retMsgCheckPoints($module, '', '', $errorCode, $transMsg);
            $message = "[" . $module . "] Output: " . CJSON::encode($data);
            $appLogger->log($appLogger->logdate, "[response]", $message);
            $this->_sendResponse(200, CJSON::encode($data));
            $logMessage = 'One or more fields is not set or is blank.';
            $logger->log($logger->logdate, "[CHECKPOINTS ERROR]: " . $request['CardNumber'] . " || ", $logMessage);
            $apiDetails = 'CHECKPOINTS-Failed: Invalid input parameters.';
            $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, '', $apiDetails, '', 2);
            if ($isInserted == 0) {
                $logMessage = "Failed to insert to APILogs.";
                $logger->log($logger->logdate, "[CHECKPOINTS ERROR]: " . $request['CardNumber'] . " || ", $logMessage);
            }

            exit;
        }
    }

    public function actionListItems() {
        $request = $this->_readJsonRequest();

        $transMsg = '';
        $errorCode = '';
        $module = 'ListItems';
        $apiMethod = 7;
        $rewardname = '';
        $MID = 0;
        $rewardID = '';
        $rewardItemID = '';
        $description = '';
        $availableItemCount = '';
        $productName = '';
        $partnerName = '';
        $points = '';
        $thumbnailLimitedImage = '';
        $thumbnailOutOfStockImage = '';
        $eCouponImage = '';
        $learnMoreLimitedImage = '';
        $learnMoreOutOfStockImage = '';
        $promoName = '';
        $isMystery = '';
        $mysteryName = '';
        $mysteryAbout = '';
        $mysterySubtext = '';
        $mysteryTerms = '';
        $about = '';
        $terms = '';
        $companyAddress = '';
        $companyPhone = '';
        $companyWebsite = '';

        $appLogger = new AppLogger();

        $paramval = CJSON::encode($request);
        $message = "[" . $module . "] Input: " . $paramval;
        $appLogger->log($appLogger->logdate, "[request]", $message);

        $logger = new ErrorLogger();
        $apiLogsModel = new APILogsModel();
        $memberSessionsModel = new MemberSessionsModel();
        $result = $memberSessionsModel->getMID($request['MPSessionID']);
        $MID = $result['MID'];

        $itemsList = array('RewardID' => $rewardID, 'RewardItemID' => $rewardItemID, 'Description' => $description, 'AvailableItemCount' => $availableItemCount,
            'ProductName' => $productName, 'PartnerName' => $partnerName, 'Points' => $points, 'ThumbnailLimitedImage' => $thumbnailLimitedImage,
            'ECouponImage' => $eCouponImage, 'LearnMoreLimitedImage' => $learnMoreLimitedImage, 'LearnMoreOutOfStockImage' => $learnMoreOutOfStockImage,
            'ThumbnailOutOfStockImage' => $thumbnailOutOfStockImage, 'PromoName' => $promoName, 'IsMystery' => $isMystery,
            'MysteryName' => $mysteryName, 'MysteryAbout' => $mysteryAbout, 'MysteryTerms' => $mysteryTerms, 'MysterySubtext' => $mysterySubtext, 'About' => $about, 'Terms' => $terms,
            'CompanyAddress' => $companyAddress, 'CompanyPhone' => $companyPhone, 'CompanyWebsite' => $companyWebsite);

        $isValid = $this->_validateMPSession($request['MPSessionID']);
        if (isset($isValid) && !$isValid) {
            $transMsg = "MPSessionID is already expired. Please login again.";
            $errorCode = 91;
            Utilities::log("ReturnMessage: " . $transMsg . " ErrorCode: " . $errorCode);
            $data = CommonController::retMsgListItems($module, $itemsList, $errorCode, $transMsg);
            $message = "[" . $module . "] Output: " . CJSON::encode($data);
            $appLogger->log($appLogger->logdate, "[response]", $message);
            $this->_sendResponse(200, CJSON::encode($data));
            $logMessage = 'MPSessionID is already expired. Please login again.';
            $logger->log($logger->logdate, "[LISTITEMS ERROR]: MID " . $MID . " || ", $logMessage);
            $apiDetails = 'LISTITEMS-Failed: MPSessionID is already expired. Please login again.. MID = ' . $MID;
            $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, '', $apiDetails, '', 2);
            if ($isInserted == 0) {
                $logMessage = "Failed to insert to APILogs.";
                $logger->log($logger->logdate, "[LISTITEMS ERROR]: MID " . $MID . " || ", $logMessage);
            }

            exit;
        }

        if (isset($result)) {
            $isUpdated = $memberSessionsModel->updateTransactionDate($MID);
            if ($isUpdated == 0) {
                $logMessage = 'Failed to update transaction date in membersessions table WHERE MID = ' . $MID . ' AND SessionID = ' . $request['MPSessionID'];
                $transMsg = 'Transaction failed.';
                $errorCode = 4;
                Utilities::log("ReturnMessage: " . $transMsg . " ErrorCode: " . $errorCode);
                $data = CommonController::retMsgListItems($module, $itemsList, $errorCode, $transMsg);
                $message = "[" . $module . "] Output: " . CJSON::encode($data);
                $appLogger->log($appLogger->logdate, "[response]", $message);
                $this->_sendResponse(200, CJSON::encode($data));
                $logger->log($logger->logdate, "[LISTITEMS ERROR]: MID " . $MID . " || ", $logMessage);
                $apiDetails = 'LISTITEMS-UpdateTransDate-Failed: ' . 'MID = ' . $MID . ' SessionID = ' . $request['MPSessionID'];
                $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, '', $apiDetails, '', 2);
                if ($isInserted == 0) {
                    $logMessage = "Failed to insert to APILogs.";
                    $logger->log($logger->logdate, "[LISTITEMS ERROR]: MID " . $MID . " || ", $logMessage);
                }

                exit;
            }
        }

        if (isset($request['MPSessionID']) && isset($request['PlayerClassID'])) {
            if ($request['MPSessionID'] == '' || $request['PlayerClassID'] == '') {
                $transMsg = "One or more fields is not set or is blank.";
                $errorCode = 1;
                Utilities::log("ReturnMessage: " . $transMsg . " ErrorCode: " . $errorCode);
                $data = CommonController::retMsgListItems($module, $itemsList, $errorCode, $transMsg);
                $message = "[" . $module . "] Output: " . CJSON::encode($data);
                $appLogger->log($appLogger->logdate, "[response]", $message);
                $this->_sendResponse(200, CJSON::encode($data));
                $logMessage = 'One or more fields is not set or is blank.';
                $logger->log($logger->logdate, "[LISTITEMS ERROR]: MID " . $MID . " || ", $logMessage);
                $apiDetails = 'LISTITEMS-Failed: Invalid input parameters.';
                $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, '', $apiDetails, '', 2);
                if ($isInserted == 0) {
                    $logMessage = "Failed to insert to APILogs.";
                    $logger->log($logger->logdate, "[LISTITEMS ERROR]: MID " . $MID . " || ", $logMessage);
                }

                exit;
            } else {
                $playerClassID = trim($request['PlayerClassID']);
                $mpSessionID = trim($request['MPSessionID']);

                //start of declaration of models to be used
                $rewardItemsModel = new RewardItemsModel();
                $memberSessionsModel = new MemberSessionsModel();
                $auditTrailModel = new AuditTrailModel();
                $refPartnersModel = new Ref_PartnersModel();

                if ($playerClassID != 2 && $playerClassID != 3) {
                    $transMsg = "Please input 2 for regular or 3 for vip.";
                    $errorCode = 67;
                    Utilities::log("ReturnMessage: " . $transMsg . " ErrorCode: " . $errorCode);
                    $data = CommonController::retMsgListItems($module, $itemsList, $errorCode, $transMsg);
                    $message = "[" . $module . "] Output: " . CJSON::encode($data);
                    $appLogger->log($appLogger->logdate, "[response]", $message);
                    $this->_sendResponse(200, CJSON::encode($data));
                    $logMessage = 'Please input 2 for regular or 3 for vip.';
                    $logger->log($logger->logdate, "[LISTITEMS ERROR]: MID " . $MID . " || ", $logMessage);
                    $apiDetails = 'LISTITEMS-Failed: Invalid input parameter(s)';
                    $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, '', $apiDetails, '', 2);
                    if ($isInserted == 0) {
                        $logMessage = "Failed to insert to APILogs.";
                        $logger->log($logger->logdate, "[LISTITEMS ERROR]: MID " . $MID . " || ", $logMessage);
                    }

                    exit;
                }

                $memberSessions = $memberSessionsModel->getMID($mpSessionID);

                if ($memberSessions)
                    $MID = $memberSessions['MID'];
                else {
                    $transMsg = "MPSessionID does not exist.";
                    $errorCode = 13;
                    Utilities::log("ReturnMessage: " . $transMsg . " ErrorCode: " . $errorCode);
                    $data = CommonController::retMsgListItems($module, $itemsList, $errorCode, $transMsg);
                    $message = "[" . $module . "] Output: " . CJSON::encode($data);
                    $appLogger->log($appLogger->logdate, "[response]", $message);
                    $this->_sendResponse(200, CJSON::encode($data));
                    $logMessage = 'MPSessionID does not exist.';
                    $logger->log($logger->logdate, "[LISTITEMS ERROR]: MID " . $MID . " || ", $logMessage);
                    $apiDetails = 'LISTITEMS-Failed: There is no active session. MID = ' . $MID;
                    $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, '', $apiDetails, '', 2);
                    if ($isInserted == 0) {
                        $logMessage = "Failed to insert to APILogs.";
                        $logger->log($logger->logdate, "[LISTITEMS ERROR]: MID " . $MID . " || ", $logMessage);
                    }

                    exit;
                }

                $isExist = $memberSessionsModel->checkIfSessionExist($MID, $mpSessionID);

                if (count($isExist) > 0) {
                    $refID = $playerClassID;
                    $rewardoffers = $rewardItemsModel->getAllRewardOffersBasedOnPlayerClassification($playerClassID);

                    for ($itr = 0; $itr < count($rewardoffers); $itr++) {
                        $rewardID[$itr] = $rewardoffers[$itr]["RewardID"];
                        $rewardItemID[$itr] = $rewardoffers[$itr]["RewardItemID"];
                        $description[$itr] = $rewardoffers[$itr]["Description"];
                        $availableItemCount[$itr] = $rewardoffers[$itr]["AvailableItemCount"];
                        $productName[$itr] = $rewardoffers[$itr]["ProductName"];
                        $partnerName[$itr] = $rewardoffers[$itr]["PartnerName"];
                        $points[$itr] = $rewardoffers[$itr]["Points"];
                        $thumbnailLimitedImage[$itr] = $rewardoffers[$itr]["ThumbnailLimitedImage"];
                        $eCouponImage[$itr] = $rewardoffers[$itr]["ECouponImage"];
                        $learnMoreLimitedImage[$itr] = $rewardoffers[$itr]["LearnMoreLimitedImage"];
                        $learnMoreOutOfStockImage[$itr] = $rewardoffers[$itr]["LearnMoreOutOfStockImage"];
                        $thumbnailOutOfStockImage[$itr] = $rewardoffers[$itr]["ThumbnailOutOfStockImage"];
                        $promoName[$itr] = $rewardoffers[$itr]["PromoName"];
                        $isMystery[$itr] = $rewardoffers[$itr]["IsMystery"];
                        $mysteryName[$itr] = $rewardoffers[$itr]["MysteryName"];
                        $mysteryAbout[$itr] = $rewardoffers[$itr]["MysteryAbout"];
                        $mysteryTerms[$itr] = $rewardoffers[$itr]["MysteryTerms"];
                        $mysterySubtext[$itr] = $rewardoffers[$itr]["MysterySubtext"];
                        $about[$itr] = $rewardoffers[$itr]['About'];
                        $terms[$itr] = $rewardoffers[$itr]['Terms'];

                        //get partner details
                        $partnerSD[$itr] = $refPartnersModel->getPartnerDetailsUsingPartnerName($partnerName[$itr]);

                        if (isset($partnerSD) || $partnerSD != null || $partnerSD != '') {
                            $companyAddress[$itr] = $partnerSD[$itr]['CompanyAddress'];
                            $companyPhone[$itr] = $partnerSD[$itr]['CompanyPhone'];
                            $companyWebsite[$itr] = $partnerSD[$itr]['CompanyWebsite'];
                        } else {
                            $companyAddress[$itr] = '';
                            $companyPhone[$itr] = '';
                            $companyWebsite[$itr] = '';
                        }

                        $companyList[$itr] = array('CompanyAddress' => $companyAddress[$itr], 'CompanyPhone' => $companyPhone[$itr], 'CompanyWebsite' => $companyWebsite[$itr]);
                        $itemsList[$itr] = array('RewardID' => $rewardID[$itr], 'RewardItemID' => $rewardItemID[$itr], 'Description' => $description[$itr], 'AvailableItemCount' => $availableItemCount[$itr],
                            'ProductName' => $productName[$itr], 'PartnerName' => $partnerName[$itr], 'Points' => $points[$itr], 'ThumbnailLimitedImage' => $thumbnailLimitedImage[$itr],
                            'ECouponImage' => $eCouponImage[$itr], 'LearnMoreLimitedImage' => $learnMoreLimitedImage[$itr], 'LearnMoreOutOfStockImage' => $learnMoreOutOfStockImage[$itr],
                            'ThumbnailOutOfStockImage' => $thumbnailOutOfStockImage[$itr], 'PromoName' => $promoName[$itr], 'IsMystery' => $isMystery[$itr],
                            'MysteryName' => $mysteryName[$itr], 'MysteryAbout' => $mysteryAbout[$itr], 'MysteryTerms' => $mysteryTerms[$itr], 'MysterySubtext' => $mysterySubtext[$itr], 'About' => $about[$itr], 'Terms' => $terms[$itr]);
                        $itemsList[$itr] = array_merge($itemsList[$itr], $companyList[$itr]);

                        $items[$itr] = $itemsList[$itr];
                    }

                    $isSuccessful = $auditTrailModel->logEvent(AuditTrailModel::API_LIST_ITEMS, 'PlayerClassID: ' . $playerClassID, array('MID' => $MID, 'SessionID' => $mpSessionID));

                    if ($isSuccessful == 0) {
                        $logMessage = "Failed to insert to Audittrail.";
                        $logger->log($logger->logdate, "[LISTITEMS ERROR]: MID " . $MID . " || ", $logMessage);
                    }

                    $transMsg = 'No Error, Transaction successful.';
                    $errorCode = 0;
                    $this->_sendResponse(200, CJSON::encode(CommonController::retMsgListItems($module, $items, $errorCode, $transMsg)));
                    $logMessage = 'List Items is successful.';
                    $logger->log($logger->logdate, "[LISTITEMS SUCCESSFUL]: MID " . $MID . " || ", $logMessage);
                    $apiDetails = 'LISTITEMS-Successful: MID = ' . $MID;
                    $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, $refID, $apiDetails, '', 1);
                    if ($isInserted == 0) {
                        $logMessage = "Failed to insert to APILogs.";
                        $logger->log($logger->logdate, "[LISTITEMS ERROR]: MID " . $MID . " || ", $logMessage);
                    }

                    exit;
                } else {
                    $transMsg = "MPSessionID does not exist.";
                    $errorCode = 13;
                    Utilities::log("ReturnMessage: " . $transMsg . " ErrorCode: " . $errorCode);
                    $data = CommonController::retMsgListItems($module, $itemsList, $errorCode, $transMsg);
                    $message = "[" . $module . "] Output: " . CJSON::encode($data);
                    $appLogger->log($appLogger->logdate, "[response]", $message);
                    $this->_sendResponse(200, CJSON::encode($data));
                    $logMessage = 'MPSessionID does not exist.';
                    $logger->log($logger->logdate, "[LISTITEMS ERROR]: MID " . $MID . " || ", $logMessage);
                    $apiDetails = 'LISTITEMS-Failed: There is no active session. MID = ' . $MID;
                    $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, '', $apiDetails, '', 2);
                    if ($isInserted == 0) {
                        $logMessage = "Failed to insert to APILogs.";
                        $logger->log($logger->logdate, "[LISTITEMS ERROR]: MID " . $MID . " || ", $logMessage);
                    }

                    exit;
                }
            }
        } else {
            $transMsg = "One or more fields is not set or is blank.";
            $errorCode = 1;
            Utilities::log("ReturnMessage: " . $transMsg . " ErrorCode: " . $errorCode);
            $data = CommonController::retMsgListItems($module, $itemsList, $errorCode, $transMsg);
            $message = "[" . $module . "] Output: " . CJSON::encode($data);
            $appLogger->log($appLogger->logdate, "[response]", $message);
            $this->_sendResponse(200, CJSON::encode($data));
            $logMessage = 'One or more fields is not set or is blank.';
            $logger->log($logger->logdate, "[LISTITEMS ERROR]: MID " . $MID . " || ", $logMessage);
            $apiDetails = 'LISTITEMS-Failed: Invalid input parameters.';
            $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, '', $apiDetails, '', 2);
            if ($isInserted == 0) {
                $logMessage = "Failed to insert to APILogs.";
                $logger->log($logger->logdate, "[LISTITEMS ERROR]: MID " . $MID . " || ", $logMessage);
            }

            exit;
        }
    }

    public function actionRedeemItems() {
        $request = $this->_readJsonRequest();

        $transMsg = '';
        $errorCode = '';
        $module = 'RedeemItems';
        $apiMethod = 8;
        $oldCurrentPoints = 0;
        $logType = '';
        $itemImage = '';
        $itemName = '';
        $partnerName = '';
        $playerName = '';
        $cardNumber = '';
        $redemptionDate = '';
        $serialCode = '';
        $securityCode = '';
        $validUntil = '';
        $companyAddress = '';
        $companyPhone = '';
        $companyWebsite = '';
        $quantity = '';
        $siteCode = '';
        $promoCode = '';
        $promoTitle = '';
        $promoPeriod = '';
        $drawDate = '';
        $address = '';
        $birthdate = '';
        $email = '';
        $contactNo = '';
        $checkSum = '';
        $about = '';
        $terms = '';

        $appLogger = new AppLogger();

        $paramval = CJSON::encode($request);
        $message = "[" . $module . "] Input: " . $paramval;
        $appLogger->log($appLogger->logdate, "[request]", $message);

        $redemption = array('ItemImage' => $itemImage, 'ItemName' => $itemName, 'PartnerName' => $partnerName, 'PlayerName' => $playerName, 'CardNumber' => $cardNumber, 'RedemptionDate' => $redemptionDate, 'SerialNumber' => $serialCode, 'SecurityCode' => $securityCode, 'ValidityDate' => $validUntil, 'CompanyAddress' => $companyAddress, 'CompanyPhone' => $companyPhone, 'CompanyWebsite' => $companyWebsite, 'Quantity' => $quantity, 'SiteCode' => $siteCode, 'PromoCode' => $promoCode, 'PromoTitle' => $promoTitle,
            'PromoPeriod' => $promoPeriod, 'DrawDate' => $drawDate, 'Address' => $address, 'Birthdate' => $birthdate, 'EmailAddress' => $email, 'ContactNo' => $contactNo, 'CheckSum' => $checkSum, 'About' => $about, 'Terms' => $terms);

        $logger = new ErrorLogger();
        $apiLogsModel = new APILogsModel();
        $memberSessionsModel = new MemberSessionsModel();
        $memberInfoModel = new MemberInfoModel();

        $result = $memberSessionsModel->getMID($request['MPSessionID']);
        $MID = $result['MID'];

        $isValid = $this->_validateMPSession($request['MPSessionID']);
        if (isset($isValid) && !$isValid) {
            $transMsg = "MPSessionID is already expired. Please login again.";
            $errorCode = 91;
            Utilities::log("ReturnMessage: " . $transMsg . " ErrorCode: " . $errorCode);
            $data = CommonController::retMsgRedemption($module, $redemption, $errorCode, $transMsg);
            $message = "[" . $module . "] Output: " . CJSON::encode($data);
            $appLogger->log($appLogger->logdate, "[response]", $message);
            $this->_sendResponse(200, CJSON::encode($data));
            $logMessage = 'MPSessionID is already expired. Please login again.';
            $logger->log($logger->logdate, "[REDEEMITEMS ERROR]: MID " . $MID . " || ", $logMessage);
            $apiDetails = 'REDEEMITEMS-Failed: MPSessionID is already expired. Please login again.. MID = ' . $MID;
            $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, '', $apiDetails, '', 2);
            if ($isInserted == 0) {
                $logMessage = "Failed to insert to APILogs.";
                $logger->log($logger->logdate, "[REDEEMITEMS ERROR]: MID " . $MID . " || ", $logMessage);
            }

            exit;
        }

        if (isset($result)) {
            $isUpdated = $memberSessionsModel->updateTransactionDate($MID);
            if ($isUpdated == 0) {
                $logMessage = 'Failed to update transaction date in membersessions table WHERE MID = ' . $MID . ' AND SessionID = ' . $request['MPSessionID'];
                $transMsg = 'Transaction failed.';
                $errorCode = 4;
                Utilities::log("ReturnMessage: " . $transMsg . " ErrorCode: " . $errorCode);
                $data = CommonController::retMsgRedemption($module, $redemption, $errorCode, $transMsg);
                $message = "[" . $module . "] Output: " . CJSON::encode($data);
                $appLogger->log($appLogger->logdate, "[response]", $message);
                $this->_sendResponse(200, CJSON::encode($data));
                $logger->log($logger->logdate, "[REDEEMITEMS ERROR]: MID " . $MID . " || ", $logMessage);
                $apiDetails = 'REDEEMITEMS-UpdateTransDate-Failed: ' . 'MID = ' . $MID . ' SessionID = ' . $request['MPSessionID'];
                $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, '', $apiDetails, '', 2);
                if ($isInserted == 0) {
                    $logMessage = "Failed to insert to APILogs.";
                    $logger->log($logger->logdate, "[REDEEMITEMS ERROR]: MID " . $MID . " || ", $logMessage);
                }

                exit;
            }
        }

        $process = new Processing();

        if (isset($request['CardNumber']) && isset($request['RewardItemID']) && isset($request['Quantity'])
                && isset($request['RewardID']) && isset($request['Source']) && isset($request['MPSessionID'])) {
            if (($request['CardNumber'] == '') || ($request['RewardItemID'] == '') || ($request['RewardID'] == '') || ($request['Quantity'] == '')
                    || ($request['Source'] == '') || ($request['MPSessionID'] == '')) {
                $transMsg = "One or more fields is not set or is blank.";
                $errorCode = 1;
                Utilities::log("ReturnMessage: " . $transMsg . " ErrorCode: " . $errorCode);
                $data = CommonController::retMsgRedemption($module, $redemption, $errorCode, $transMsg);
                $message = "[" . $module . "] Output: " . CJSON::encode($data);
                $appLogger->log($appLogger->logdate, "[response]", $message);
                $this->_sendResponse(200, CJSON::encode($data));
                $logMessage = 'One or more fields is not set or is blank.';
                $logger->log($logger->logdate, "[REDEEMITEMS ERROR]: MID " . $MID . " || ", $logMessage);
                $apiDetails = 'REDEEMITEMS-Failed: Invalid input parameters.';
                $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, '', $apiDetails, '', 2);
                if ($isInserted == 0) {
                    $logMessage = "Failed to insert to APILogs.";
                    $logger->log($logger->logdate, "[REDEEMITEMS ERROR]: MID " . $MID . " || ", $logMessage);
                }

                exit;
            } else {
                $cardNumber = trim($request['CardNumber']);
                $rewardItemID = trim($request['RewardItemID']);
                $rewardID = trim($request['RewardID']);
                $quantity = trim($request['Quantity']);
                $source = trim($request['Source']);
                $mpSessionID = trim($request['MPSessionID']);

                $memberSessionsModel = new MemberSessionsModel();
                $memberCardsModel = new MemberCardsModel();
                $auditTrailModel = new AuditTrailModel();
                $raffleCouponsModel = new RaffleCouponsModel();
                $rewardItemsModel = new RewardItemsModel();
                $pendingRedemptionModel = new PendingRedemptionModel();
                $couponRedemptionLogsModel = new CouponRedemptionLogsModel();
                $memberInfoModel = new MemberInfoModel();
                $itemSerialCodesModel = new ItemSerialCodesModel();
                $refPartnersModel = new Ref_PartnersModel();
                $helpers = new Helpers();
                $pcwsWrapper = new PcwsWrapper();

                if ($rewardID == 1) {
                    $qty1 = $quantity;
                } else if ($rewardID == 2) {
                    $itemQty1 = $quantity;
                } else {
                    $transMsg = "RewardID does not exist.";
                    $errorCode = 62;
                    Utilities::log("ReturnMessage: " . $transMsg . " ErrorCode: " . $errorCode);
                    $data = CommonController::retMsgRedemption($module, $redemption, $errorCode, $transMsg);
                    $message = "[" . $module . "] Output: " . CJSON::encode($data);
                    $appLogger->log($appLogger->logdate, "[response]", $message);
                    $this->_sendResponse(200, CJSON::encode($data));
                    $logMessage = 'RewardID does not exist.';
                    $logger->log($logger->logdate, "[REDEEMITEMS ERROR]: MID " . $MID . " || ", $logMessage);
                    $apiDetails = 'REDEEMITEMS-Failed: Invalid input parameters.';
                    $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, '', $apiDetails, '', 2);
                    if ($isInserted == 0) {
                        $logMessage = "Failed to insert to APILogs.";
                        $logger->log($logger->logdate, "[REDEEMITEMS ERROR]: MID " . $MID . " || ", $logMessage);
                    }

                    exit;
                }

                $result = $memberSessionsModel->getMID($mpSessionID);
                if ($result)
                    $MID = $result['MID'];
                else {
                    $transMsg = "MPSessionID does not exist.";
                    $errorCode = 13;
                    Utilities::log("ReturnMessage: " . $transMsg . " ErrorCode: " . $errorCode);
                    $data = CommonController::retMsgRedemption($module, $redemption, $errorCode, $transMsg);
                    $message = "[" . $module . "] Output: " . CJSON::encode($data);
                    $appLogger->log($appLogger->logdate, "[response]", $message);
                    $this->_sendResponse(200, CJSON::encode($data));
                    $logMessage = 'MPSessionID does not exist.';
                    $logger->log($logger->logdate, "[REDEEMITEMS ERROR]: MID " . $MID . " || ", $logMessage);
                    $apiDetails = 'REDEEMITEMS-Failed: There is no active session.';
                    $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, '', $apiDetails, '', 2);
                    if ($isInserted == 0) {
                        $logMessage = "Failed to insert to APILogs.";
                        $logger->log($logger->logdate, "[REDEEMITEMS ERROR]: MID " . $MID . " || ", $logMessage);
                    }

                    exit;
                }

                $memberInfo = $memberInfoModel->getMemberInfoUsingMID($MID);
                if ($memberInfo)
                    $mobileNumber = $memberInfo['MobileNumber'];
                else {
                    $transMsg = "No member found for that account.";
                    $errorCode = 55;
                    Utilities::log("ReturnMessage: " . $transMsg . " ErrorCode: " . $errorCode);
                    $data = CommonController::retMsgRedemption($module, $redemption, $errorCode, $transMsg);
                    $message = "[" . $module . "] Output: " . CJSON::encode($data);
                    $appLogger->log($appLogger->logdate, "[response]", $message);
                    $this->_sendResponse(200, CJSON::encode($data));
                    $logMessage = 'No found member for that account.';
                    $logger->log($logger->logdate, "[REDEEMITEMS ERROR]: MID " . $MID . " || ", $logMessage);
                    $apiDetails = 'REDEEMITEMS-Failed: No member found for that account. [MID] = ' . $MID;
                    $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, '', $apiDetails, '', 2);
                    if ($isInserted == 0) {
                        $logMessage = "Failed to insert to APILogs.";
                        $logger->log($logger->logdate, "[REDEEMITEMS ERROR]: MID " . $MID . " || ", $logMessage);
                    }

                    exit;
                }

                $isExist = $memberSessionsModel->checkIfSessionExist($MID, $mpSessionID);

                if (count($isExist) > 0) {
                    $refID = $cardNumber . ';' . $rewardID . ';' . $rewardItemID . ';' . $quantity;
                    if ($source == 3) {
                        $result = $memberCardsModel->getMemberPointsAndStatus($cardNumber);
                        $memberDetails = $memberInfoModel->getMemberInfoUsingMID($MID);
                        $currentPoints = $result['CurrentPoints'];
                    } else {
                        $transMsg = "Please input 3 as source.";
                        $errorCode = 23;
                        Utilities::log("ReturnMessage: " . $transMsg . " ErrorCode: " . $errorCode);
                        $data = CommonController::retMsgRedemption($module, $redemption, $errorCode, $transMsg);
                        $message = "[" . $module . "] Output: " . CJSON::encode($data);
                        $appLogger->log($appLogger->logdate, "[response]", $message);
                        $this->_sendResponse(200, CJSON::encode($data));
                        $logMessage = 'Please input 3 as source.';
                        $logger->log($logger->logdate, "[REDEEMITEMS ERROR]: MID " . $MID . " || ", $logMessage);
                        $apiDetails = 'REDEEMITEMS-Failed: Invalid input parameters.';
                        $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, $refID, $apiDetails, '', 2);
                        if ($isInserted == 0) {
                            $logMessage = "Failed to insert to APILogs.";
                            $logger->log($logger->logdate, "[REDEEMITEMS ERROR]: MID " . $MID . " || ", $logMessage);
                        }

                        exit;
                    }

                    if (!$result) {
                        $transMsg = "Card number does not exist.";
                        $errorCode = 61;
                        Utilities::log("ReturnMessage: " . $transMsg . " ErrorCode: " . $errorCode);
                        $data = CommonController::retMsgRedemption($module, $redemption, $errorCode, $transMsg);
                        $message = "[" . $module . "] Output: " . CJSON::encode($data);
                        $appLogger->log($appLogger->logdate, "[response]", $message);
                        $this->_sendResponse(200, CJSON::encode($data));
                        $logMessage = 'Card number does not exist.';
                        $logger->log($logger->logdate, "[REDEEMITEMS ERROR]: MID " . $MID . " || ", $logMessage);
                        $apiDetails = 'REDEEMITEMS-Failed: Invalid input parameters.';
                        $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, $refID, $apiDetails, '', 2);
                        if ($isInserted == 0) {
                            $logMessage = "Failed to insert to APILogs.";
                            $logger->log($logger->logdate, "[REDEEMITEMS ERROR]: MID " . $MID . " || ", $logMessage);
                        }

                        exit;
                    }

                    if ($rewardID == 1) {
                        $quantity = $qty1;
                    } else {
                        $quantity = $itemQty1;
                    }

                    $itemDetail = $rewardItemsModel->getItemDetails($rewardItemID);
                    if (!$itemDetail || $itemDetail == FALSE) {
                        $transMsg = "RewardItemID does not exist.";
                        $errorCode = 63;
                        Utilities::log("ReturnMessage: " . $transMsg . " ErrorCode: " . $errorCode);
                        $data = CommonController::retMsgRedemption($module, $redemption, $errorCode, $transMsg);
                        $message = "[" . $module . "] Output: " . CJSON::encode($data);
                        $appLogger->log($appLogger->logdate, "[response]", $message);
                        $this->_sendResponse(200, CJSON::encode($data));
                        $logMessage = 'RewardItemID does not exist.';
                        $logger->log($logger->logdate, "[REDEEMITEMS ERROR]: MID " . $MID . " || ", $logMessage);
                        $apiDetails = 'REDEEMITEMS-Failed: Invalid input parameters.';
                        $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, $refID, $apiDetails, '', 2);
                        if ($isInserted == 0) {
                            $logMessage = "Failed to insert to APILogs.";
                            $logger->log($logger->logdate, "[REDEEMITEMS ERROR]: MID " . $MID . " || ", $logMessage);
                        }
                        exit;
                    }

                    $requiredPoints = $itemDetail['RequiredPoints'];

                    if ($quantity > 0) {
                        if ($quantity > 1 && $itemDetail['IsMystery'] == 1) {
                            $transMsg = "Item to be redeemed is a mystery item. Only one mystery item per redeem is allowed.";
                            $errorCode = 60;
                            Utilities::log("ReturnMessage: " . $transMsg . " ErrorCode: " . $errorCode);
                            $data = CommonController::retMsgRedemption($module, $redemption, $errorCode, $transMsg);
                            $message = "[" . $module . "] Output: " . CJSON::encode($data);
                            $appLogger->log($appLogger->logdate, "[response]", $message);
                            $this->_sendResponse(200, CJSON::encode($data));
                            $quantity = '';
                            $totalItemPoints = '';
                            $logMessage = 'Item to be redeemed is a mystery item. Only one mystery item per redeem is allowed.';
                            $logger->log($logger->logdate, "[REDEEMITEMS ERROR]: MID " . $MID . " || ", $logMessage);
                            $apiDetails = 'REDEEMITEMS-Failed: Item to be redeemed is a mystery item. Only one mystery item per redeem is allowed. RewardItemID = ' . $rewardItemID;
                            $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, $refID, $apiDetails, '', 2);
                            if ($isInserted == 0) {
                                $logMessage = "Failed to insert to APILogs.";
                                $logger->log($logger->logdate, "[REDEEMITEMS ERROR]: MID " . $MID . " || ", $logMessage);
                            }

                            exit;
                        }

                        $totalItemPoints = $quantity * $requiredPoints;
                        if ($currentPoints < $totalItemPoints) {
                            $logMessage = 'Transaction failed. Card has insufficient points.';
                            $logger->log($logger->logdate, "[REDEEMITEMS ERROR]: MID " . $MID . " || ", $logMessage);
                            $apiDetails = 'REDEEMITEMS-Failed: Card has insufficient points. CurrentPoints = ' . $currentPoints;
                            $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, $refID, $apiDetails, '', 2);
                            if ($isInserted == 0) {
                                $logMessage = "Failed to insert to APILogs.";
                                $logger->log($logger->logdate, "[REDEEMITEMS ERROR]: MID " . $MID . " || ", $logMessage);
                            }

                            if ($rewardID == 1) {
                                $result = $auditTrailModel->logEvent(AuditTrailModel::API_REDEEM_ITEMS, 'Message: ' . $transMsg, array('MID' => $MID, 'SessionID' => $mpSessionID));
                            } else {
                                $result = $auditTrailModel->logEvent(AuditTrailModel::API_REDEEM_ITEMS, 'Message: ' . $transMsg, array('MID' => $MID, 'SessionID' => $mpSessionID));
                            }

                            if ($result > 0) {
                                $transMsg = "Transaction failed. Card has insufficient points.";
                                $errorCode = 24;
                                Utilities::log("ReturnMessage: " . $transMsg . " ErrorCode: " . $errorCode);
                                $data = CommonController::retMsgRedemption($module, $redemption, $errorCode, $transMsg);
                                $message = "[" . $module . "] Output: " . CJSON::encode($data);
                                $appLogger->log($appLogger->logdate, "[response]", $message);
                                $this->_sendResponse(200, CJSON::encode($data));
                                $quantity = '';
                                $totalItemPoints = '';
                                $logMessage = 'Transaction failed. Card has insufficient points.';
                                $logger->log($logger->logdate, "[REDEEMITEMS ERROR]: MID " . $MID . " || ", $logMessage);
                                $apiDetails = 'REDEEMITEMS-Failed: Card has insufficient points. CurrentPoints = ' . $currentPoints;
                                $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, $refID, $apiDetails, '', 2);
                                if ($isInserted == 0) {
                                    $logMessage = "Failed to insert to APILogs.";
                                    $logger->log($logger->logdate, "[REDEEMITEMS ERROR]: MID " . $MID . " || ", $logMessage);
                                }

                                exit;
                            } else {
                                $quantity = '';
                                $totalItemPoints = '';
                                if ($rewardID == 2) {
                                    $logType == '[COUPON REDEMPTION ERROR]';
                                } else {
                                    $logType == '[ITEM REDEMPTION ERROR]';
                                }

                                $logMessage = "Failed to log event on Audit Trail.";

                                $transMsg = "Transaction failed. Card has insufficient points.";
                                $errorCode = 24;
                                $logger->log($logger->logdate, "[REDEEMITEMS ERROR]: MID " . $MID . " || " . $logType . " || ", $logMessage);
                                Utilities::log("ReturnMessage: " . $transMsg . " ErrorCode: " . $errorCode);
                                $data = CommonController::retMsgRedemption($module, $redemption, $errorCode, $transMsg);
                                $message = "[" . $module . "] Output: " . CJSON::encode($data);
                                $appLogger->log($appLogger->logdate, "[response]", $message);
                                $this->_sendResponse(200, CJSON::encode($data));
                                $apiDetails = 'REDEEMITEMS-Failed: Card has insufficient points. CurrentPoints = ' . $currentPoints;
                                $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, $refID, $apiDetails, '', 2);
                                if ($isInserted == 0) {
                                    $logMessage = "Failed to insert to APILogs.";
                                    $logger->log($logger->logdate, "[REDEEMITEMS ERROR]: MID " . $MID . " || ", $logMessage);
                                }

                                exit;
                            }
                        } else {
                            $isCoupon = ($rewardID == 1 || $rewardID == "1") ? false : true;
                            if ($isCoupon) {
                                //Check if the available coupon is greater than or match with the quantity avail by the player.
                                $availableCoupon = $raffleCouponsModel->getAvailableCoupons($rewardItemID, $quantity);
                                if (count($availableCoupon) == $quantity && $quantity <= 99999) {
                                    //redemption process for coupon
                                    $offerEndDate = $rewardItemsModel->getOfferEndDate($rewardItemID);
                                    $redeemedDate = $offerEndDate['ItemCurrentDate'];

                                    //check if the date availed is greater than the end date of the reward offer
                                    if ($redeemedDate <= $offerEndDate['OfferEndDate']) {
                                        $toBeCurrentPoints = (int) $currentPoints - (int) $totalItemPoints;
                                        if ($toBeCurrentPoints < 0) {
                                            $transMsg = "Transaction failed. Card has insufficient points.";
                                            $errorCode = 24;
                                            Utilities::log("ReturnMessage: " . $transMsg . " ErrorCode: " . $errorCode);
                                            $data = CommonController::retMsgRedemption($module, $redemption, $errorCode, $transMsg);
                                            $message = "[" . $module . "] Output: " . CJSON::encode($data);
                                            $appLogger->log($appLogger->logdate, "[response]", $message);
                                            $this->_sendResponse(200, CJSON::encode($data));
                                            $logMessage = 'Transaction failed. Card has insufficient points.';
                                            $logger->log($logger->logdate, "[REDEEMITEMS ERROR]: MID " . $MID . " || ", $logMessage);
                                            $apiDetails = 'REDEEMITEMS-Failed: Card has insufficient points. CurrentPoints = ' . $currentPoints;
                                            $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, $refID, $apiDetails, '', 2);
                                            if ($isInserted == 0) {
                                                $logMessage = "Failed to insert to APILogs.";
                                                $logger->log($logger->logdate, "[REDEEMITEMS ERROR]: MID " . $MID . " || ", $logMessage);
                                            }
                                            $result = $auditTrailModel->logEvent(AuditTrailModel::API_REDEEM_ITEMS, 'ReturnMessage: ' . $transMsg, array('MID' => $MID, 'SessionID' => $mpSessionID));
                                            if ($result > 0) {
                                                $quantity = '';
                                                $totalItemPoints = '';
                                            } else {
                                                $quantity = '';
                                                $totalItemPoints = '';
                                                $logMessage = "Failed to log event on Audit Trail.";

                                                $errorCode = 25;
                                                $logger->log($logger->logdate, "[COUPON REDEMPTION ERROR]: MID " . $MID . " || ", $logMessage);
                                            }

                                            exit;
                                        } else {
                                            $pendingRedemption = $pendingRedemptionModel->checkPendingRedemption($MID);

                                            //check if there is pending redemption, if there is, throw an error message
                                            if ($pendingRedemption) {
                                                $transMsg = 'Transaction failed. Card has a pending redemption.';
                                                $logMessage = 'Transaction failed. Card has a pending redemption.';
                                                $logger->log($logger->logdate, "[REDEEMITEMS ERROR]: MID " . $MID . " || ", $logMessage);
                                                $apiDetails = 'REDEEMITEMS-Failed: Card has a pending redemption. MID = ' . $MID;
                                                $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, $refID, $apiDetails, '', 2);
                                                if ($isInserted == 0) {
                                                    $logMessage = "Failed to insert to APILogs.";
                                                    $logger->log($logger->logdate, "[REDEEMITEMS ERROR]: MID " . $MID . " || ", $logMessage);
                                                }
                                                $result = $auditTrailModel->logEvent(AuditTrailModel::API_REDEEM_ITEMS, 'ReturnMessage: ' . $transMsg, array('MID' => $MID, 'SessionID' => $mpSessionID));

                                                if ($result > 0) {
                                                    $quantity = '';
                                                    $totalItemPoints = '';
                                                } else {
                                                    $quantity = '';
                                                    $totalItemPoints = '';
                                                    $logMessage = "Failed to log event on Audit Trail.";
                                                    $transMsg = "Failed to log event on Audit Trail. " . " [COUPON REDEMPTION ERROR]";
                                                    $errorCode = 25;
                                                    $logger->log($logger->logdate, "[COUPON REDEMPTION ERROR]: MID " . $MID . " || ", $logMessage);
                                                }
                                                $errorCode = 26;
                                                Utilities::log("ReturnMessage: " . $transMsg . " ErrorCode: " . $errorCode);
                                                $data = CommonController::retMsgRedemption($module, $redemption, $errorCode, $transMsg);
                                                $message = "[" . $module . "] Output: " . CJSON::encode($data);
                                                $appLogger->log($appLogger->logdate, "[response]", $message);
                                                $this->_sendResponse(200, CJSON::encode($data));
                                                $logMessage = 'Transaction failed. Card has a pending redemption.';
                                                $logger->log($logger->logdate, "[REDEEMITEMS ERROR]: MID " . $MID . " || ", $logMessage);
                                                $apiDetails = 'REDEEMITEMS-Failed: Card has a pending redemption. MID = ' . $MID;
                                                $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, $refID, $apiDetails, '', 2);
                                                if ($isInserted == 0) {
                                                    $logMessage = "Failed to insert to APILogs.";
                                                    $logger->log($logger->logdate, "[REDEEMITEMS ERROR]: MID " . $MID . " || ", $logMessage);
                                                }

                                                exit;
                                            } else {
                                                //process coupon redemption
                                                $resultArray = $process->processCouponRedemption($MID, $rewardItemID, $quantity, $totalItemPoints, $cardNumber, 3, $redeemedDate);

                                                if ($resultArray['IsSuccess']) {
                                                    $oldCurrentPoints = number_format($resultArray['OldCP']);
                                                    $redeemedPoints = number_format($totalItemPoints);
                                                    $rewardItem = $rewardItemsModel->getItemDetails($rewardItemID);
                                                    $itemName = $rewardItem['ItemName'];
                                                    $message = "CP: " . $oldCurrentPoints . ", Item: " . $itemName . ", RP: " . $redeemedPoints . ", Series: " . $resultArray['CouponSeries'];
                                                } else {
                                                    $transMsg = $resultArray['Message'];
                                                    $logMessage = 'Transaction failed.';
                                                    $logger->log($logger->logdate, "[REDEEMITEMS ERROR]: MID " . $MID . " || ", $logMessage);
                                                    $apiDetails = 'REDEEMITEMS-Failed: Processing of coupon redemption failed. MID = ' . $MID . '. RewardItemID = ' . $rewardItemID . '. TotalItemPoints = ' . $totalItemPoints . '. CardNumber = ' . $cardNumber;
                                                    $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, $refID, $apiDetails, '', 2);
                                                    if ($isInserted == 0) {
                                                        $logMessage = "Failed to insert to APILogs.";
                                                        $logger->log($logger->logdate, "[REDEEMITEMS ERROR]: MID " . $MID . " || ", $logMessage);
                                                    }
                                                }
                                                $isLogged = $auditTrailModel->logEvent(AuditTrailModel::API_REDEEM_ITEMS, 'ReturnMessage: ' . $message, array('MID' => $MID, 'SessionID' => $mpSessionID));
                                                if ($isLogged == 0) {
                                                    $logMessage = "Failed to log event on Audit Trail.";
                                                    $logger->log($logger->logdate, "[COUPON REDEMPTION ERROR]: MID " . $MID . " || ", $logMessage);
                                                }

                                                $quantity = '';
                                                $totalItemPoints = '';

                                                if (!$resultArray['IsSuccess']) {
                                                    $errorCode = 56;
                                                    Utilities::log("ReturnMessage: " . $transMsg . " ErrorCode: " . $errorCode);
                                                    $data = CommonController::retMsgRedemption($module, $redemption, $errorCode, $transMsg);
                                                    $message = "[" . $module . "] Output: " . CJSON::encode($data);
                                                    $appLogger->log($appLogger->logdate, "[response]", $message);
                                                    $this->_sendResponse(200, CJSON::encode($data));
                                                } else {
                                                    $errorCode = 0;
                                                    $transMsg = 'Redemption successful.';

                                                    //send SMS alert to player
                                                    $smsResult = $this->_sendSMS(SMSRequestLogsModel::COUPON_REDEMPTION, $mobileNumber, $redeemedDate, $resultArray['SerialNumber'], $quantity, "SMSC", $resultArray['LastInsertedID'], '', $resultArray['CouponSeries']);
                                                    if ($smsResult == 0) {
                                                        $smsFailed = 'Invalid Mobile Number.';
                                                        $errorCode = 97;
                                                        Utilities::log("ReturnMessage: " . $transMsg . " ErrorCode: " . $errorCode);
                                                        $this->_sendResponse(200, CJSON::encode(CommonController::retMsgRedemption($module, $redemption, $errorCode, $smsFailed)));
                                                    }
                                                    $showcouponredemptionwindow = true;
                                                    $showitemredemptionwindow = false;

                                                    //if coupon, display appropriate reward offer transaction printable copy and send to legit player email
                                                    if ($showcouponredemptionwindow == true) {
                                                        //get reward item details
                                                        $rewardOffers = $rewardItemsModel->getRewardItemDetails($rewardItemID);

                                                        $birthdate = $memberDetails['Birthdate'];
                                                        $playerName = $memberDetails['FirstName'] . ' ' . $memberDetails['LastName'];
                                                        $address = $memberDetails['Address1'];
                                                        $email = $memberDetails['Email'];
                                                        $contactNo = $memberDetails['MobileNumber'];

                                                        //for($itr = 0; $itr < count($rewardOffers); $itr++) {
                                                        //    $eCouponImage[$itr] = $rewardOffers[$itr]["ECouponImage"];
                                                        //    $partnerName[$itr] = $rewardOffers[$itr]["PartnerName"];
                                                        //}

                                                        if (isset($rewardOffers['ECouponImage'])) {
                                                            $eCouponImage = $rewardOffers["ECouponImage"];
                                                        }

                                                        if (isset($rewardOffers['About'])) {
                                                            $about = $rewardOffers['About'];
                                                            $terms = $rewardOffers['Terms'];
                                                            $promoName = $rewardOffers['PromoName'];
                                                            $promoCode = $rewardOffers['PromoCode'];
                                                        }

                                                        if (isset($resultArray['CouponSeries'])) {
                                                            $startYear = date('Y', strtotime($rewardOffers['StartDate']));
                                                            $endYear = date('Y', strtotime($rewardOffers['EndDate']));
                                                            if ($startYear == $endYear) {
                                                                $sDate = new DateTime(date($rewardOffers['StartDate']));
                                                                $startDate = $sDate->format("F j");
                                                                $eDate = new DateTime(date($rewardOffers['EndDate']));
                                                                $endDate = $eDate->format("F j, Y");
                                                                $promoPeriod = $startDate . " to " . $endDate;
                                                            } else {
                                                                $sDate = new DateTime(date($rewardOffers['StartDate']));
                                                                $startDate = $sDate->format("F j, Y");
                                                                $eDate = new DateTime(date($rewardOffers['EndDate']));
                                                                $endDate = $eDate->format("F j, Y");
                                                                $promoPeriod = $startDate . " to " . $endDate;
                                                            }
                                                        } else {
                                                            $sDate = new DateTime(date($rewardOffers["StartDate"]));
                                                            $startDate = $sDate->format("F j, Y");
                                                            $eDate = new DateTime(date($rewardOffers["EndDate"]));
                                                            $endDate = $eDate->format("F j, Y");
                                                            $promoPeriod = $startDate . " to " . $endDate;
                                                        }

                                                        if ($rewardOffers['IsMystery'] == 1 && $rewardOffers['AvailableItemCount'] > 0)
                                                            $itemName = $rewardOffers['MysteryName'];
                                                        else
                                                            $itemName = $rewardOffers['ItemName'];

                                                        //for coupon only : set draw date format.
                                                        if ($rewardOffers['DrawDate'] != '' && $rewardOffers['DrawDate'] != null) {
                                                            $dDate = new DateTime(date($rewardOffers['DrawDate']));
                                                            $drawDate = $dDate->format("F j, Y, gA");
                                                        }
                                                        else
                                                            $drawDate = '';

                                                        $newHeader = Yii::app()->params['extra_imagepath'] . 'extra_images/newheader.jpg';
                                                        $newFooter = Yii::app()->params['extra_imagepath'] . 'extra_images/newfooter.jpg';
                                                        $itemImage = Yii::app()->params['rewarditem_imagepath'] . $eCouponImage;
                                                        $importantReminder = Yii::app()->params['extra_imagepath'] . "important_reminders.jpg";

                                                        $redemptionDate = $resultArray['RedemptionDate'];
                                                        $rDate = new DateTime(date($redemptionDate));
                                                        $redemptionDate = $rDate->format("F j, Y, g:i a");

                                                        $fBirthdate = date("F j, Y", strtotime($birthdate));
                                                        $siteCode = 'Website';
                                                        
                                                        $raCheckSum = $resultArray['CheckSum'];
                                                        $serialNumber = $resultArray['SerialNumber'];                                                  
                                                        $couponSeries = $resultArray['CouponSeries'];
                                                        $raQuantity = $resultArray["Quantity"];
  
                                                        $helpers->sendEmailCouponRedemption($playerName, $address, $siteCode, $cardNumber, $fBirthdate, $email, $contactNo, '', '', $newHeader, $newFooter, $itemImage, $couponSeries, $raQuantity, $raCheckSum, $serialNumber, $redemptionDate, $promoCode, $promoName, $promoPeriod, $drawDate, $about, $terms);
                                                    }

                                                    $couponRedemptionArray = array('ItemImage' => $itemImage, 'ItemName' => $itemName, 'PartnerName' => $partnerName, 'PlayerName' => $playerName, 'CardNumber' => $cardNumber, 'RedemptionDate' => $redemptionDate, 'SerialNumber' => $serialNumber, 'SecurityCode' => $couponSeries, 'ValidityDate' => '', 'CompanyAddress' => $companyAddress, 'CompanyPhone' => $companyPhone, 'CompanyWebsite' => $companyWebsite, 'Quantity' => $raQuantity, 'SiteCode' => $siteCode, 'PromoCode' => $promoCode, 'PromoTitle' => $promoName, 'PromoPeriod' => $promoPeriod, 'DrawDate' => $drawDate, 'Address' => $address, 'Birthdate' => $birthdate, 'EmailAddress' => $email, 'ContactNo' => $contactNo, 'CheckSum' => $raCheckSum, 'About' => $about, 'Terms' => $terms);
                                                    Utilities::log("ReturnMessage: " . $transMsg . " ErrorCode: " . $errorCode);
                                                    $this->_sendResponse(200, CJSON::encode(CommonController::retMsgCouponRedemptionSuccess($module, $couponRedemptionArray, $errorCode, $transMsg)));
                                                    $logMessage = $transMsg;
                                                    $logger->log($logger->logdate, "[REDEEMITEMS SUCCESSFUL]: MID " . $MID . " || ", $logMessage);
                                                    $apiDetails = $transMsg;
                                                    $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, $refID, $apiDetails, $resultArray['LastInsertedID'], 1);
                                                    if ($isInserted == 0) {
                                                        $logMessage = "Failed to insert to APILogs.";
                                                        $logger->log($logger->logdate, "[REDEEMITEMS ERROR]: MID " . $MID . " || ", $logMessage);
                                                    }
                                                }
                                            }
                                        }
                                    } else {
                                        $message = "Player Redemption: Transaction Failed. Reward Offer has already ended.";
                                        $isLogged = $auditTrailModel->logEvent(AuditTrailModel::API_REDEEM_ITEMS, 'ReturnMessage: ' . $message, array('MID' => $MID, 'SessionID' => $mpSessionID));
                                        if ($isLogged > 0) {
                                            $quantity = '';
                                            $totalItemPoints = '';
                                        } else {
                                            $logMessage = "Failed to log event on Audit Trail.";
                                            $quantity = '';
                                            $totalItemPoints = '';
                                            $logger->log($logger->logdate, "[COUPON REDEMPTION ERROR]: MID " . $MID . " || ", $logMessage);
                                        }
                                        $transMsg = "Player Redemption: Transaction Failed. Reward Offer has already ended.";
                                        $errorCode = 49;
                                        Utilities::log("ReturnMessage: " . $transMsg . " ErrorCode: " . $errorCode);
                                        $data = CommonController::retMsgRedemption($module, $redemption, $errorCode, $transMsg);
                                        $message = "[" . $module . "] Output: " . CJSON::encode($data);
                                        $appLogger->log($appLogger->logdate, "[response]", $message);
                                        $this->_sendResponse(200, CJSON::encode($data));
                                        $logMessage = 'Transaction failed. Reward offer has already ended';
                                        $logger->log($logger->logdate, "[REDEEMITEMS ERROR]: MID " . $MID . " || ", $logMessage);
                                        $apiDetails = 'REDEEMITEMS-Failed: Reward offer has already ended. RewardItemID = ' . $rewardItemID . '.' . ', ItemCurrentDate = ' . $redeemedDate;
                                        $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, $refID, $apiDetails, '', 2);
                                        if ($isInserted == 0) {
                                            $logMessage = "Failed to insert to APILogs.";
                                            $logger->log($logger->logdate, "[REDEEMITEMS ERROR]: MID " . $MID . " || ", $logMessage);
                                        }

                                        exit;
                                    }
                                } else {
                                    if ($quantity > 99999) {
                                        $message = 'Transaction failed. Max number of coupons redeemable is 99999';
                                        $errorCode = 65;
                                    } else {
                                        $message = "Transaction Failed. Raffle Coupon is either insufficient or unavailable.";
                                        $errorCode = 47;
                                    }

                                    $isLogged = $auditTrailModel->logEvent(AuditTrailModel::API_REDEEM_ITEMS, 'ReturnMessage: ' . $message, array('MID' => $MID, 'SessionID' => $mpSessionID));
                                    if ($isLogged > 0) {
                                        $quantity = '';
                                        $totalItemPoints = '';
                                    } else {
                                        $logMessage = "Failed to log event on Audit Trail.";
                                        $quantity = '';
                                        $totalItemPoints = '';
                                        $logger->log($logger->logdate, "[COUPON REDEMPTION ERROR]: MID " . $MID . " || ", $logMessage);
                                    }
                                    $transMsg = $message;
                                    Utilities::log("ReturnMessage: " . $transMsg . " ErrorCode: " . $errorCode);
                                    $data = CommonController::retMsgRedemption($module, $redemption, $errorCode, $transMsg);
                                    $message = "[" . $module . "] Output: " . CJSON::encode($data);
                                    $appLogger->log($appLogger->logdate, "[response]", $message);
                                    $this->_sendResponse(200, CJSON::encode($data));
                                    $logMessage = 'Transaction failed. Raffle coupon is either insufficient or unavailable.';
                                    $logger->log($logger->logdate, "[REDEEMITEMS ERROR]: MID " . $MID . " || ", $logMessage);
                                    $apiDetails = 'REDEEMITEMS-Failed: Processing of coupon redemption failed. MID = ' . $MID . '. RewardItemID = ' . $rewardItemID . '. TotalItemPoints = ' . $totalItemPoints . '. CardNumber = ' . $cardNumber;
                                    $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, $refID, $apiDetails, '', 2);
                                    if ($isInserted == 0) {
                                        $logMessage = "Failed to insert to APILogs.";
                                        $logger->log($logger->logdate, "[REDEEMITEMS ERROR]: MID " . $MID . " || ", $logMessage);
                                    }

                                    exit;
                                }
                            } else {
                                //check if the available item is greater than or equal to the quantity availed by the player.
                                $availableItemCount = $rewardItemsModel->getAvailableItemCount($rewardItemID);

                                if ($availableItemCount['AvailableItemCount'] >= $quantity) {
                                    $availableSerialCode = $itemSerialCodesModel->getAvailableSerialCodeCount($rewardItemID, $quantity);

                                    if (count($availableSerialCode) >= $quantity && $quantity <= 5) {
                                        //redemption process for item
                                        $offerEndDate = $rewardItemsModel->getOfferEndDate($rewardItemID);
                                        $redeemedDate = $offerEndDate['ItemCurrentDate'];
                                        $currentDate = $offerEndDate['CurrentDate'];

                                        //check if the avail date is greater than the end date of the reward offer
                                        if ($redeemedDate <= $offerEndDate['OfferEndDate']) {
                                            $toBeCurrentPoints = (int) $currentPoints - (int) $totalItemPoints;
                                            if ($toBeCurrentPoints < 0) {
                                                $transMsg = "Transaction Failed. Card has insufficient points.";
                                                $isLogged = $auditTrailModel->logEvent(AuditTrailModel::API_REDEEM_ITEMS, 'ReturnMessage: ' . $transMsg, array('MID' => $MID, 'SessionID' => $mpSessionID));
                                                if ($isLogged > 0) {
                                                    $quantity = '';
                                                    $totalItemPoints = '';
                                                } else {
                                                    $logMessage = "Failed to log event on database";
                                                    $quantity = '';
                                                    $totalItemPoints = '';
                                                    $logger->log($logger->logdate, "[REDEEMITEMS ERROR]: MID " . $MID . " || ", $logMessage);
                                                }

                                                $errorCode = 24;
                                                Utilities::log("ReturnMessage: " . $transMsg . " ErrorCode: " . $errorCode);
                                                $data = CommonController::retMsgRedemption($module, $redemption, $errorCode, $transMsg);
                                                $message = "[" . $module . "] Output: " . CJSON::encode($data);
                                                $appLogger->log($appLogger->logdate, "[response]", $message);
                                                $this->_sendResponse(200, CJSON::encode($data));
                                                $logMessage = 'Transaction failed. Card has insufficient points.';
                                                $logger->log($logger->logdate, "[REDEEMITEMS ERROR]: MID " . $MID . " || ", $logMessage);
                                                $apiDetails = 'REDEEMITEMS-Failed: Card has insufficient points. CurrentPoints = ' . $currentPoints;
                                                $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, $refID, $apiDetails, '', 2);
                                                if ($isInserted == 0) {
                                                    $logMessage = "Failed to insert to APILogs.";
                                                    $logger->log($logger->logdate, "[REDEEMITEMS ERROR]: MID " . $MID . " || ", $logMessage);
                                                }

                                                exit;
                                            } else {
                                                $pendingRedemption = $pendingRedemptionModel->checkPendingRedemption($MID);

                                                //check if there is a pending redemption for this player
                                                if ($pendingRedemption) {
                                                    $transMsg = "Transaction failed. Card has a pending redemption.";
                                                    $isLogged = $auditTrailModel->logEvent(AuditTrailModel::API_REDEEM_ITEMS, 'ReturnMessage: ' . $transMsg, array('MID' => $MID, 'SessionID' => $mpSessionID));
                                                    if ($isLogged > 0) {
                                                        $quantity = '';
                                                        $totalItemPoints = '';
                                                    } else {
                                                        $logMessage = "Failed to log event on Audit Trail.";
                                                        $quantity = '';
                                                        $totalItemPoints = '';
                                                        $logger->log($logger->logdate, "[REDEEMITEMS ERROR]: MID " . $MID . " || ", $logMessage);
                                                    }
                                                    $errorCode = 26;
                                                    Utilities::log("ReturnMessage: " . $transMsg . " ErrorCode: " . $errorCode);
                                                    $data = CommonController::retMsgRedemption($module, $redemption, $errorCode, $transMsg);
                                                    $message = "[" . $module . "] Output: " . CJSON::encode($data);
                                                    $appLogger->log($appLogger->logdate, "[response]", $message);
                                                    $this->_sendResponse(200, CJSON::encode($data));
                                                    $logMessage = 'Transaction failed. Card has a pending redemption.';
                                                    $logger->log($logger->logdate, "[REDEEMITEMS ERROR]: MID " . $MID . " || ", $logMessage);
                                                    $apiDetails = 'REDEEMITEMS-Failed: Card has a pending redemption. MID = ' . $MID;
                                                    $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, $refID, $apiDetails, '', 2);
                                                    if ($isInserted == 0) {
                                                        $logMessage = "Failed to insert to APILogs.";
                                                        $logger->log($logger->logdate, "[REDEEMITEMS ERROR]: MID " . $MID . " || ", $logMessage);
                                                    }

                                                    exit;
                                                } else {
                                                    //process item redemption
                                                    $resultsArray = $process->processItemRedemption($MID, $rewardItemID, $quantity, $totalItemPoints, $cardNumber, 3, $redeemedDate);

                                                    if ($resultsArray['IsSuccess']) {
                                                        $oldCurrentPoints = number_format($resultsArray['OldCP']);
                                                        $redeemedPoints = number_format($totalItemPoints);
                                                        $rewardItem = $rewardItemsModel->getItemDetails($rewardItemID);
                                                        $itemName = $rewardItem['ItemName'];
                                                        $transMsg = "CP: " . $oldCurrentPoints . ", Item: " . $itemName . ", RP: " . $redeemedPoints;
                                                        $logMessage = $transMsg;
                                                        $logger->log($logger->logdate, "[REDEEMITEMS SUCCESSFUL]: MID " . $MID . " || ", $logMessage);
                                                        $apiDetails = $transMsg;
                                                        $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, $refID, $apiDetails, '', 1);
                                                        if ($isInserted == 0) {
                                                            $logMessage = "Failed to insert to APILogs.";
                                                            $logger->log($logger->logdate, "[REDEEMITEMS ERROR]: MID " . $MID . " || ", $logMessage);
                                                        }
                                                    } else {
                                                        $transMsg = $resultsArray['Message'];
                                                        $logMessage = $transMsg;
                                                        $logger->log($logger->logdate, "[REDEEMITEMS ERROR]: MID " . $MID . " || ", $logMessage);
                                                        $apiDetails = $transMsg;
                                                        $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, $refID, $apiDetails, '', 2);
                                                        if ($isInserted == 0) {
                                                            $logMessage = "Failed to insert to APILogs.";
                                                            $logger->log($logger->logdate, "[REDEEMITEMS ERROR]: MID " . $MID . " || ", $logMessage);
                                                        }
                                                    }

                                                    $isLogged = $auditTrailModel->logEvent(AuditTrailModel::API_REDEEM_ITEMS, 'ReturnMessage: ' . $transMsg, array('MID' => $MID, 'SessionID' => $mpSessionID));

                                                    if ($isLogged == 0) {
                                                        $logMessage = "Failed to log event on Audit Trail.";
                                                        $logger->log($logger->logdate, "[REDEEMITEMS ERROR]: MID " . $MID . " || ", $logMessage);
                                                    }

                                                    if (!$resultsArray['IsSuccess']) {
                                                        $errorCode = 56;
                                                        Utilities::log("ReturnMessage: " . $transMsg . " ErrorCode: " . $errorCode);
                                                        $data = CommonController::retMsgRedemption($module, $redemption, $errorCode, $transMsg);
                                                        $message = "[" . $module . "] Output: " . CJSON::encode($data);
                                                        $appLogger->log($appLogger->logdate, "[response]", $message);
                                                        $this->_sendResponse(200, CJSON::encode($data));
                                                        $logMessage = $transMsg;
                                                        $logger->log($logger->logdate, "[REDEEMITEMS ERROR]: MID " . $MID . " || ", $logMessage);
                                                        $apiDetails = $transMsg;
                                                        $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, $refID, $apiDetails, '', 2);
                                                        if ($isInserted == 0) {
                                                            $logMessage = "Failed to insert to APILogs.";
                                                            $logger->log($logger->logdate, "[REDEEMITEMS ERROR]: MID " . $MID . " || ", $logMessage);
                                                        }
                                                    } else {
                                                        $errorCode = 0;
                                                        $transMsg = 'Redemption successful.';
                                                        if ($itemDetail['IsMystery'] == 1)
                                                            $itemName = $itemDetail['MysteryName'];

                                                        $partnerName = $itemDetail['PartnerID'];
                                                        $partner = $refPartnersModel->getPartnerNameUsingPartnerID($partnerName);
                                                        $partnerName = $partner['PartnerName'];

                                                        //get partner details
                                                        $partnerSD = $refPartnersModel->getPartnerDetailsUsingPartnerName($partnerName);
                                                        if (isset($partnerSD) || $partnerSD != null || $partnerSD != '') {
                                                            $companyAddress = $partnerSD['CompanyAddress'];
                                                            $companyPhone = $partnerSD['CompanyPhone'];
                                                            $companyWebsite = $partnerSD['CompanyWebsite'];
                                                        } else {
                                                            $companyAddress = '';
                                                            $companyPhone = '';
                                                            $companyWebsite = '';
                                                        }

                                                        $eCouponImage = $itemDetail['ECouponImage'];
                                                        $itemImage = $eCouponImage;
                                                        $playerName = $memberDetails['FirstName'] . ' ' . $memberDetails['LastName'];
                                                        $redemptionDate = $resultsArray['RedemptionDate'];
                                                        $rDate = new DateTime(date($redemptionDate));
                                                        $redemptionDate = $rDate->format("F j, Y, g:i a");

                                                        if (isset($itemDetail['About'])) {
                                                            $about = $itemDetail['About'];
                                                            $terms = $itemDetail['Terms'];
                                                        }
                                                        
                                                        $email = $memberDetails['Email'];
                                                        for($i=0;$i<$quantity;$i++){
                                                            $sessionSerialCode[] = $resultsArray['SessionSerialCode'][$i];
                                                            $sessionSecurityCode[] = $resultsArray['SessionSecurityCode'][$i];
                                                        }
                                                        $sessionValidUntil = $resultsArray['ValidUntil'][0];

                                                        $itemRedemptionArray = array('ItemImage' => $itemImage, 'ItemName' => $itemName, 'PartnerName' => $partnerName, 'PlayerName' => $playerName, 'CardNumber' => $cardNumber, 'RedemptionDate' => $redemptionDate, 'SerialNumber' => $sessionSerialCode, 'SecurityCode' => $sessionSecurityCode, 'ValidityDate' => $sessionValidUntil, 'CompanyAddress' => $companyAddress, 'CompanyPhone' => $companyPhone, 'CompanyWebsite' => $companyWebsite, 'Quantity' => $quantity, 'SiteCode' => $siteCode, 'PromoCode' => $promoCode, 'PromoTitle' => $promoTitle,
                                                            'PromoPeriod' => $promoPeriod, 'DrawDate' => $drawDate, 'Address' => $address, 'Birthdate' => $birthdate, 'EmailAddress' => $email, 'ContactNo' => $contactNo, 'CheckSum' => $checkSum, 'About' => $about, 'Terms' => $terms);

                                                        Utilities::log("ReturnMessage: " . $transMsg . " ErrorCode: " . $errorCode);
                                                        $this->_sendResponse(200, CJSON::encode(CommonController::retMsgItemRedemptionSuccess($module, $itemRedemptionArray, $errorCode, $transMsg)));
                                                        $logMessage = $transMsg;
                                                        $logger->log($logger->logdate, "[REDEEMITEMS SUCCESSFUL]: MID " . $MID . " || ", $logMessage);
                                                        $apiDetails = $transMsg;
                                                        $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, $refID, $apiDetails, $resultsArray['LastInsertedID'], 1);
                                                        if ($isInserted == 0) {
                                                            $logMessage = "Failed to insert to APILogs.";
                                                            $logger->log($logger->logdate, "[REDEEMITEMS ERROR]: MID " . $MID . " || ", $logMessage);
                                                        }

                                                        $ctr = count($resultsArray['SessionSerialCode']);

                                                        $totalPoints = $totalItemPoints / $quantity;

                                                        for ($itr = 0; $itr < $ctr; $itr++) {
                                                            $smsResult = $this->_sendSMS(SMSRequestLogsModel::ITEM_REDEMPTION, $mobileNumber, $redeemedDate, $resultsArray['SessionSerialCode'][$itr], 1, "SMSI", $resultsArray['LastInsertedID'][$itr], $totalPoints);
                                                            if ($smsResult == 0) {
                                                                $smsFailed = 'Invalid Mobile Number.';
                                                                $errorCode = 97;
                                                                Utilities::log("ReturnMessage: " . $transMsg . " ErrorCode: " . $errorCode);
                                                                $this->_sendResponse(200, CJSON::encode(CommonController::retMsgRedemption($module, $redemption, $errorCode, $smsFailed)));
                                                            }
                                                        }
                                                        $showcouponredemptionwindow = true;
                                                        $showitemredemptionwindow = true;

                                                        $ctr = count($resultsArray['SessionSerialCode']);
                                                        $itemImage = Yii::app()->params['rewarditem_imagepath'] . $eCouponImage;
                                                        $newHeader = Yii::app()->params['extra_imagepath'] . 'extra_images/newheader.jpg';
                                                        $newFooter = Yii::app()->params['extra_imagepath'] . 'extra_images/newfooter.jpg';
                                                        $importantReminder = Yii::app()->params['extra_imagepath'] . "important_reminders.jpg";

                                                        for ($itr = 0; $itr < $ctr; $itr++) {
                                                            $helpers->sendEmailItemRedemption($email, $newHeader, $itemImage, $itemName, $partnerName, $playerName, $cardNumber, $redemptionDate, $resultsArray['SessionSerialCode'][$itr], $resultsArray['SessionSecurityCode'][$itr], $resultsArray['ValidUntil'][$itr], $companyAddress, $companyPhone, $companyWebsite, $importantReminder, $about, $terms, $newFooter);

                                                            if ($itemDetail['IsMystery'] == 1) {
                                                                $rdDate = new DateTime(date($resultsArray['RedemptionDate']));
                                                                $redeemedDate = $rdDate->format('m-d-Y');
                                                                $redeemedTime = $rdDate->format('G:i A');
                                                                $sender = Yii::app()->params['MarketingEmail'];
                                                                if ($itemDetail['IsVIP'] == 0)
                                                                    $statusValue = 'Regular';
                                                                else
                                                                    $statusValue = 'VIP';

                                                                $modeOfRedemption = 'via cashier';
                                                                $helpers->sendMysteryRewardEmail($redeemedDate, $redeemedTime, $resultsArray['SerialNumber'][$itr], $resultsArray['SecurityCode'][$itr], $itemDetail['MysteryName'], $itemDetail['ItemName'], $cardNumber, $playerName, $statusValue, $modeOfRedemption, $sender);
                                                            }
                                                        }

                                                        exit;
                                                    }
                                                }
                                            }
                                        }
                                        else {
                                            $transMsg = 'Player Redemption: Transacation failed. Reward offer has already ended.';
                                            $isLogged = $auditTrailModel->logEvent(AuditTrailModel::API_REDEEM_ITEMS, 'ReturnMessage: ' . $transMsg, array('MID' => $MID, 'SessionID' => $mpSessionID));
                                            if ($isLogged > 0) {
                                                $quantity = '';
                                                $totalItemPoints = '';
                                            } else {
                                                $logMessage = "Failed to log event on Audit Trail.";
                                                $quantity = '';
                                                $totalItemPoints = '';
                                                $logger->log($logger->logdate, "[REDEEMITEMS ERROR]: MID " . $MID . " || ", $logMessage);
                                            }

                                            $errorCode = 49;
                                            Utilities::log("ReturnMessage: " . $transMsg . " ErrorCode: " . $errorCode);
                                            $data = CommonController::retMsgRedemption($module, $redemption, $errorCode, $transMsg);
                                            $message = "[" . $module . "] Output: " . CJSON::encode($data);
                                            $appLogger->log($appLogger->logdate, "[response]", $message);
                                            $this->_sendResponse(200, CJSON::encode($data));
                                            $logMessage = $transMsg;
                                            $logger->log($logger->logdate, "[REDEEMITEMS ERROR]: MID " . $MID . " || ", $logMessage);
                                            $apiDetails = $transMsg;
                                            $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, $refID, $apiDetails, '', 2);
                                            if ($isInserted == 0) {
                                                $logMessage = "Failed to insert to APILogs.";
                                                $logger->log($logger->logdate, "[REDEEMITEMS ERROR]: MID " . $MID . " || ", $logMessage);
                                            }

                                            exit;
                                        }
                                    } else {
                                        if ($quantity > 5) {
                                            $transMsg = 'Transaction failed. Max number of items regular items redeemable is 5.';
                                            $errorCode = 66;
                                        } else {
                                            $transMsg = 'Transaction failed. Serial code is unavailable.';
                                            $errorCode = 57;
                                        }
                                        $isLogged = $auditTrailModel->logEvent(AuditTrailModel::API_REDEEM_ITEMS, 'ReturnMessage: ' . $transMsg, array('MID' => $MID, 'SessionID' => $mpSessionID));
                                        if ($isLogged > 0) {
                                            $quantity = '';
                                            $totalItemPoints = '';
                                        } else {
                                            $logMessage = "Failed to log event on Audit Trail.";
                                            $quantity = '';
                                            $totalItemPoints = '';
                                            $logger->log($logger->logdate, "[REDEEMITEMS ERROR]: MID " . $MID . " || ", $logMessage);
                                        }

                                        Utilities::log("ReturnMessage: " . $transMsg . " ErrorCode: " . $errorCode);
                                        $data = CommonController::retMsgRedemption($module, $redemption, $errorCode, $transMsg);
                                        $message = "[" . $module . "] Output: " . CJSON::encode($data);
                                        $appLogger->log($appLogger->logdate, "[response]", $message);
                                        $this->_sendResponse(200, CJSON::encode($data));
                                        $logMessage = $transMsg;
                                        $logger->log($logger->logdate, "[REDEEMITEMS ERROR]: MID " . $MID . " || ", $logMessage);
                                        $apiDetails = $transMsg;
                                        $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, $refID, $apiDetails, '', 2);
                                        if ($isInserted == 0) {
                                            $logMessage = "Failed to insert to APILogs.";
                                            $logger->log($logger->logdate, "[REDEEMITEMS ERROR]: MID " . $MID . " || ", $logMessage);
                                        }

                                        exit;
                                    }
                                } else {
                                    $transMsg = 'Transaction failed. Number of available item is insufficient.';
                                    $isLogged = $auditTrailModel->logEvent(AuditTrailModel::API_REDEEM_ITEMS, 'ReturnMessage: ' . $transMsg, array('MID' => $MID, 'SessionID' => $mpSessionID));
                                    if ($isLogged > 0) {
                                        $quantity = '';
                                        $totalItemPoints = '';
                                    } else {
                                        $logMessage = "Failed to log event on Audit Trail.";
                                        $quantity = '';
                                        $totalItemPoints = '';
                                        $logger->log($logger->logdate, "[REDEEMITEMS ERROR]: MID " . $MID . " || ", $logMessage);
                                    }

                                    $errorCode = 58;
                                    Utilities::log("ReturnMessage: " . $transMsg . " ErrorCode: " . $errorCode);
                                    $data = CommonController::retMsgRedemption($module, $redemption, $errorCode, $transMsg);
                                    $message = "[" . $module . "] Output: " . CJSON::encode($data);
                                    $appLogger->log($appLogger->logdate, "[response]", $message);
                                    $this->_sendResponse(200, CJSON::encode($data));
                                    $logMessage = $transMsg;
                                    $logger->log($logger->logdate, "[REDEEMITEMS ERROR]: MID " . $MID . " || ", $logMessage);
                                    $apiDetails = $transMsg;
                                    $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, $refID, $apiDetails, '', 2);
                                    if ($isInserted == 0) {
                                        $logMessage = "Failed to insert to APILogs.";
                                        $logger->log($logger->logdate, "[REDEEMITEMS ERROR]: MID " . $MID . " || ", $logMessage);
                                    }

                                    exit;
                                }
                            }
                        }
                    } else {
                        $transMsg = 'Transaction failed. Invalid Item or Coupon Quantity.';
                        if ($rewardID == 1) {
                            $isLogged = $auditTrailModel->logEvent(AuditTrailModel::API_REDEEM_ITEMS, 'ReturnMessage: ' . $transMsg, array('MID' => $MID, 'SessionID' => $mpSessionID));
                        } else {
                            $isLogged = $auditTrailModel->logEvent(AuditTrailModel::API_REDEEM_ITEMS, 'ReturnMessage: ' . $transMsg, array('MID' => $MID, 'SessionID' => $mpSessionID));
                        }

                        if ($isLogged > 0) {
                            $quantity = '';
                            $totalItemPoints = '';
                        } else {
                            $logMessage = "Failed to log event on Audit Trail.";
                            $quantity = '';
                            $totalItemPoints = '';
                            $logger->log($logger->logdate, "[REDEEMITEMS ERROR]: MID " . $MID . " || ", $logMessage);
                        }
                        $errorCode = 59;
                        Utilities::log("ReturnMessage: " . $transMsg . " ErrorCode: " . $errorCode);
                        $data = CommonController::retMsgRedemption($module, $redemption, $errorCode, $transMsg);
                        $message = "[" . $module . "] Output: " . CJSON::encode($data);
                        $appLogger->log($appLogger->logdate, "[response]", $message);
                        $this->_sendResponse(200, CJSON::encode($data));
                        $logMessage = $transMsg;
                        $logger->log($logger->logdate, "[REDEEMITEMS ERROR]: MID " . $MID . " || ", $logMessage);
                        $apiDetails = $transMsg;
                        $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, $refID, $apiDetails, '', 2);
                        if ($isInserted == 0) {
                            $logMessage = "Failed to insert to APILogs.";
                            $logger->log($logger->logdate, "[REDEEMITEMS ERROR]: MID " . $MID . " || ", $logMessage);
                        }

                        exit;
                    }
                } else {
                    $transMsg = "MPSessionID does not exist.";
                    $errorCode = 13;
                    Utilities::log("ReturnMessage: " . $transMsg . " ErrorCode: " . $errorCode);
                    $data = CommonController::retMsgRedemption($module, $redemption, $errorCode, $transMsg);
                    $message = "[" . $module . "] Output: " . CJSON::encode($data);
                    $appLogger->log($appLogger->logdate, "[response]", $message);
                    $this->_sendResponse(200, CJSON::encode($data));
                    $logMessage = 'MPSessionID does not exist.';
                    $logger->log($logger->logdate, "[REDEEMITEMS ERROR]: MID " . $MID . " || ", $logMessage);
                    $apiDetails = 'REDEEMITEMS-Failed: There is no active session.';
                    $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, '', $apiDetails, '', 2);
                    if ($isInserted == 0) {
                        $logMessage = "Failed to insert to APILogs.";
                        $logger->log($logger->logdate, "[REDEEMITEMS ERROR]: MID " . $MID . " || ", $logMessage);
                    }

                    exit;
                }
            }
        } else {
            $transMsg = "One or more fields is not set or is blank.";
            $errorCode = 1;
            Utilities::log("ReturnMessage: " . $transMsg . " ErrorCode: " . $errorCode);
            $data = CommonController::retMsgRedemption($module, $redemption, $errorCode, $transMsg);
            $message = "[" . $module . "] Output: " . CJSON::encode($data);
            $appLogger->log($appLogger->logdate, "[response]", $message);
            $this->_sendResponse(200, CJSON::encode($data));
            $logMessage = 'One or more fields is not set or is blank.';
            $logger->log($logger->logdate, "[REDEEMITEMS ERROR]: MID " . $MID . " || ", $logMessage);
            $apiDetails = 'REDEEMITEMS-Failed: Invalid input parameters.';
            $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, '', $apiDetails, '', 2);
            if ($isInserted == 0) {
                $logMessage = "Failed to insert to APILogs.";
                $logger->log($logger->logdate, "[REDEEMITEMS ERROR]: MID " . $MID . " || ", $logMessage);
            }

            exit;
        }
    }

    public function actionGetProfile() {
        $request = $this->_readJsonRequest();

        $transMsg = '';
        $errorCode = '';
        $module = 'GetProfile';
        $apiMethod = 5;
        $MID = 0;

        $appLogger = new AppLogger();

        $paramval = CJSON::encode($request);
        $message = "[" . $module . "] Input: " . $paramval;
        $appLogger->log($appLogger->logdate, "[request]", $message);

        $logger = new ErrorLogger();
        $apiLogsModel = new APILogsModel();
        $memberSessionsModel = new MemberSessionsModel();
        $result = $memberSessionsModel->getMID($request['MPSessionID']);
        $MID = $result['MID'];

        $firstname = '';
        $middlename = '';
        $lastname = '';
        $nickname = '';
        $permanentAddress = '';
        $mobileNumber = '';
        $alternateMobileNumber = '';
        $emailAddress = '';
        $alternateEmail = '';
        $gender = '';
        $idPresented = '';
        $idNumber = '';
        $nationality = '';
        $occupation = '';
        $isSmoker = '';
        $birthDate = '';
        $age = '';
        $currentPoints = '';
        $bonusPoints = '';
        $redeemedPoints = '';
        $lifetimePoints = '';
        $cardNumber = '';

        $profile = array('FirstName' => $firstname, 'MiddleName' => $middlename,
            'LastName' => $lastname, 'NickName' => $nickname,
            'PermanentAdd' => $permanentAddress, 'MobileNo' => $mobileNumber,
            'AlternateMobileNo' => $alternateMobileNumber,
            'EmailAddress' => $emailAddress, 'AlternateEmail' => $alternateEmail,
            'Gender' => $gender, 'IDPresented' => $idPresented,
            'IDNumber' => $idNumber, 'Nationality' => $nationality,
            'Occupation' => $occupation, 'IsSmoker' => $isSmoker,
            'Birthdate' => $birthDate, 'Age' => $age, 'CurrentPoints' => $currentPoints,
            'BonusPoints' => $bonusPoints, 'RedeemedPoints' => $redeemedPoints, 'LifetimePoints' => $lifetimePoints, 'CardNumber' => $cardNumber);

        $isValid = $this->_validateMPSession($request['MPSessionID']);
        if (isset($isValid) && !$isValid) {
            $transMsg = "MPSessionID is already expired. Please login again.";
            $errorCode = 91;
            Utilities::log("ReturnMessage: " . $transMsg . " ErrorCode: " . $errorCode);
            $data = CommonController::retMsgGetProfile($module, $profile, $errorCode, $transMsg);
            $message = "[" . $module . "] Output: " . CJSON::encode($data);
            $appLogger->log($appLogger->logdate, "[response]", $message);
            $this->_sendResponse(200, CJSON::encode($data));
            $logMessage = 'MPSessionID is already expired. Please login again.';
            $logger->log($logger->logdate, "[GETPROFILE ERROR]: MID " . $MID . " || ", $logMessage);
            $apiDetails = 'GETPROFILE-Failed: MPSessionID is already expired. Please login again.. MID = ' . $MID;
            $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, '', $apiDetails, '', 2);
            if ($isInserted == 0) {
                $logMessage = "Failed to insert to APILogs.";
                $logger->log($logger->logdate, "[GETPROFILE ERROR]: MID " . $MID . " || ", $logMessage);
            }

            exit;
        }

        if (isset($result)) {
            $isUpdated = $memberSessionsModel->updateTransactionDate($MID);
            if ($isUpdated == 0) {
                $logMessage = 'Failed to update transaction date in membersessions table WHERE MID = ' . $MID . ' AND SessionID = ' . $request['MPSessionID'];
                $transMsg = 'Transaction failed.';
                $errorCode = 4;
                Utilities::log("ReturnMessage: " . $transMsg . " ErrorCode: " . $errorCode);
                $data = CommonController::retMsgGetProfile($module, $profile, $errorCode, $transMsg);
                $message = "[" . $module . "] Output: " . CJSON::encode($data);
                $appLogger->log($appLogger->logdate, "[response]", $message);
                $this->_sendResponse(200, CJSON::encode($data));
                $logger->log($logger->logdate, "[GETPROFILE ERROR]: MID " . $MID . " || ", $logMessage);
                $apiDetails = 'GETPROFILE-UpdateTransDate-Failed: ' . 'MID = ' . $MID . ' SessionID = ' . $request['MPSessionID'];
                $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, '', $apiDetails, '', 2);
                if ($isInserted == 0) {
                    $logMessage = "Failed to insert to APILogs.";
                    $logger->log($logger->logdate, "[GETPROFILE ERROR]: MID " . $MID . " || ", $logMessage);
                }

                exit;
            }
        }

        if (isset($request['CardNumber']) && isset($request['MPSessionID'])) {
            if ($request['CardNumber'] == '' || $request['MPSessionID'] == '') {
                $transMsg = "One or more fields is not set or is blank.";
                $errorCode = 1;
                Utilities::log("ReturnMessage: " . $transMsg . " ErrorCode: " . $errorCode);
                $data = CommonController::retMsgGetProfile($module, $profile, $errorCode, $transMsg);
                $message = "[" . $module . "] Output: " . CJSON::encode($data);
                $appLogger->log($appLogger->logdate, "[response]", $message);
                $this->_sendResponse(200, CJSON::encode($data));
                $logMessage = 'One or more fields is not set or is blank.';
                $logger->log($logger->logdate, "[GETPROFILE ERROR]: MID " . $MID . " || ", $logMessage);
                $apiDetails = 'GETPROFILE-Failed: Invalid input parameters.';
                $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, '', $apiDetails, '', 2);
                if ($isInserted == 0) {
                    $logMessage = "Failed to insert to APILogs.";
                    $logger->log($logger->logdate, "[GETPROFILE ERROR]: MID " . $MID . " || ", $logMessage);
                }

                exit;
            } else {
                $cardNumber = trim($request['CardNumber']);
                $mpSessionID = trim($request['MPSessionID']);

                //start of declaration of models to be used
                $memberCardsModel = new MemberCardsModel();
                $memberInfoModel = new MemberInfoModel();
                $memberSessionsModel = new MemberSessionsModel();
                $cardsModel = new CardsModel();
                $auditTrailModel = new AuditTrailModel();
                $pcwsWrapper = new PcwsWrapper();

                $memberExist = $memberCardsModel->getMIDUsingCard($cardNumber);
                if ($memberExist)
                    $MID = $memberExist['MID'];
                else {
                    $transMsg = "Card number does not exist.";
                    $errorCode = 61;
                    Utilities::log("ReturnMessage: " . $transMsg . " ErrorCode: " . $errorCode);
                    $data = CommonController::retMsgGetProfile($module, $profile, $errorCode, $transMsg);
                    $message = "[" . $module . "] Output: " . CJSON::encode($data);
                    $appLogger->log($appLogger->logdate, "[response]", $message);
                    $this->_sendResponse(200, CJSON::encode($data));
                    $logMessage = 'MPSessionID does not exist.';
                    $logger->log($logger->logdate, "[GETPROFILE ERROR]: MID " . $MID . " || ", $logMessage);
                    $apiDetails = 'GETPROFILE-Failed: There is no active session. MID = ' . $MID;
                    $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, '', $apiDetails, '', 2);
                    if ($isInserted == 0) {
                        $logMessage = "Failed to insert to APILogs.";
                        $logger->log($logger->logdate, "[GETPROFILE ERROR]: MID " . $MID . " || ", $logMessage);
                    }

                    exit;
                }

                $isExist = $memberSessionsModel->checkIfSessionExist($MID, $mpSessionID);

                if ($isExist['COUNT(*)'] > 0) {
                    $refID = $cardNumber;
                    if ($MID == '' || $MID == null) {
                        $transMsg = "Account is Banned.";
                        $errorCode = 40;
                        Utilities::log("ReturnMessage: " . $transMsg . " ErrorCode: " . $errorCode);
                        $data = CommonController::retMsgGetProfile($module, $profile, $errorCode, $transMsg);
                        $message = "[" . $module . "] Output: " . CJSON::encode($data);
                        $appLogger->log($appLogger->logdate, "[response]", $message);
                        $this->_sendResponse(200, CJSON::encode($data));
                        $logMessage = 'Account is banned.';
                        $logger->log($logger->logdate, "[GETPROFILE ERROR]: MID " . $MID . " || ", $logMessage);
                        $apiDetails = 'GETPROFILE-Failed: Account is banned.';
                        $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, $refID, $apiDetails, '', 2);
                        if ($isInserted == 0) {
                            $logMessage = "Failed to insert to APILogs.";
                            $logger->log($logger->logdate, "[GETPROFILE ERROR]: MID " . $MID . " || ", $logMessage);
                        }

                        exit;
                    } else {
                        $memberDetails = $cardsModel->getMemberInfoUsingCardNumber($cardNumber);

                        if ($memberDetails) {
                            $firstname = $memberDetails['FirstName'];
                            $middlename = $memberDetails['MiddleName'];
                            if ($middlename == null)
                                $middlename = '';
                            $lastname = $memberDetails['LastName'];
                            $nickname = $memberDetails['NickName'];
                            if ($nickname == null)
                                $nickname = '';
                            $permanentAddress = $memberDetails['Address1'];
                            $mobileNumber = $memberDetails['MobileNumber'];
                            $alternateMobileNumber = $memberDetails['AlternateMobileNumber'];
                            if ($alternateMobileNumber == null)
                                $alternateMobileNumber = '';
                            $emailAddress = $memberDetails['Email'];
                            $alternateEmail = $memberDetails['AlternateEmail'];
                            if ($alternateEmail == null)
                                $alternateEmail = '';
                            $gender = $memberDetails['Gender'];
                            if ($gender == null)
                                $gender = '';
                            $idPresented = $memberDetails['IdentificationID'];
                            $idNumber = $memberDetails['IdentificationNumber'];
                            $nationality = $memberDetails['NationalityID'];
                            if ($nationality == null)
                                $nationality = '';
                            $occupation = $memberDetails['OccupationID'];
                            if ($occupation == null)
                                $occupation = '';
                            $isSmoker = $memberDetails['IsSmoker'];
                            if ($isSmoker == null)
                                $isSmoker = '';
                            $birthDate = $memberDetails['Birthdate'];
                            $age = number_format((abs(strtotime($birthDate) - strtotime(date('Y-m-d'))) / 60 / 60 / 24 / 365), 0);
                            $currentPoints = $memberDetails['CurrentPoints'];
                            $bonusPoints = $memberDetails['BonusPoints'];
                            $redeemedPoints = $memberDetails['RedeemedPoints'];
                            $lifetimePoints = $memberDetails['LifetimePoints'];

//                            $result = $pcwsWrapper->getCompPoints($cardNumber, 1);
//                            if ($result) {
//                                $currentPoints = $result['GetCompPoints']['CompBalance'];
//                                $bonusPoints = 0;
//                            } else {
//                                $transMsg = "Cannot access PCWS API.";
//                                $errorCode = 120;
//                                Utilities::log("ReturnMessage: " . $transMsg . " ErrorCode: " . $errorCode);
//                                $data = CommonController::retMsgGetProfile($module, $profile, $errorCode, $transMsg);
//                                $message = "[" . $module . "] Output: " . CJSON::encode($data);
//                                $appLogger->log($appLogger->logdate, "[response]", $message);
//                                $this->_sendResponse(200, CJSON::encode($data));
//                                $logMessage = 'Cannot access PCWS API.';
//                                $logger->log($logger->logdate, "[GETPROFILE ERROR]: MID " . $MID . " || ", $logMessage);
//                                $apiDetails = 'GETPROFILE-Failed: Cannot access PCWS API. Card Number = ' . $cardNumber;
//                                $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, $refID, $apiDetails, '', 2);
//                                if ($isInserted == 0) {
//                                    $logMessage = "Failed to insert to APILogs.";
//                                    $logger->log($logger->logdate, "[GETPROFILE ERROR]: MID " . $MID . " || ", $logMessage);
//                                }
//
//                                exit;
//                            }

                            $result = $auditTrailModel->logEvent(AuditTrailModel::API_GET_PROFILE, 'CardNumber: ' . $cardNumber, array('MID' => $MID, 'SessionID' => $mpSessionID));
                            if ($result == 0) {
                                $logMessage = "Failed to insert to Audittrail.";
                                $logger->log($logger->logdate, "[GETPROFILE ERROR]: MID " . $MID . " || ", $logMessage);
                            }

                            $profile = array('FirstName' => $firstname, 'MiddleName' => $middlename,
                                'LastName' => $lastname, 'NickName' => $nickname,
                                'PermanentAdd' => $permanentAddress, 'MobileNo' => $mobileNumber,
                                'AlternateMobileNo' => $alternateMobileNumber,
                                'EmailAddress' => $emailAddress, 'AlternateEmail' => $alternateEmail,
                                'Gender' => $gender, 'IDPresented' => $idPresented,
                                'IDNumber' => $idNumber, 'Nationality' => $nationality,
                                'Occupation' => $occupation, 'IsSmoker' => $isSmoker,
                                'Birthdate' => $birthDate, 'Age' => $age, 'CurrentPoints' => $currentPoints,
                                'BonusPoints' => $bonusPoints, 'RedeemedPoints' => $redeemedPoints, 'LifetimePoints' => $lifetimePoints, 'CardNumber' => $cardNumber);

                            $transMsg = 'No Error, Transaction successful.';
                            $errorCode = 0;
                            $data = CommonController::retMsgGetProfile($module, $profile, $errorCode, $transMsg);
                            $message = "[" . $module . "] Output: " . CJSON::encode($data);
                            $appLogger->log($appLogger->logdate, "[response]", $message);
                            $this->_sendResponse(200, CJSON::encode($data));
                            $logMessage = 'Get member profile is successful.';
                            $logger->log($logger->logdate, "[GETPROFILE SUCCESSFUL]: MID " . $MID . " || ", $logMessage);
                            $apiDetails = 'GETPROFILE-Success: Get member profile is successful. MID = ' . $MID;
                            $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, $refID, $apiDetails, '', 1);
                            if ($isInserted == 0) {
                                $logMessage = "Failed to insert to APILogs.";
                                $logger->log($logger->logdate, "[GETPROFILE ERROR]: MID " . $MID . " || ", $logMessage);
                            }

                            exit;
                        } else {
                            $transMsg = "Account is Banned.";
                            $errorCode = 40;
                            Utilities::log("ReturnMessage: " . $transMsg . " ErrorCode: " . $errorCode);
                            $data = CommonController::retMsgGetProfile($module, $profile, $errorCode, $transMsg);
                            $message = "[" . $module . "] Output: " . CJSON::encode($data);
                            $appLogger->log($appLogger->logdate, "[response]", $message);
                            $this->_sendResponse(200, CJSON::encode($data));
                            $logMessage = 'Account is banned.';
                            $logger->log($logger->logdate, "[GETPROFILE ERROR]: MID " . $MID . " || ", $logMessage);
                            $apiDetails = 'GETPROFILE-Failed: Account is banned.';
                            $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, $refID, $apiDetails, '', 2);
                            if ($isInserted == 0) {
                                $logMessage = "Failed to insert to APILogs.";
                                $logger->log($logger->logdate, "[GETPROFILE ERROR]: MID " . $MID . " || ", $logMessage);
                            }

                            exit;
                        }
                    }
                } else {
                    $transMsg = "MPSessionID does not exist.";
                    $errorCode = 13;
                    Utilities::log("ReturnMessage: " . $transMsg . " ErrorCode: " . $errorCode);
                    $data = CommonController::retMsgGetProfile($module, $profile, $errorCode, $transMsg);
                    $message = "[" . $module . "] Output: " . CJSON::encode($data);
                    $appLogger->log($appLogger->logdate, "[response]", $message);
                    $this->_sendResponse(200, CJSON::encode($data));
                    $logMessage = 'MPSessionID does not exist.';
                    $logger->log($logger->logdate, "[GETPROFILE ERROR]: MID " . $MID . " || ", $logMessage);
                    ;
                    $apiDetails = 'GETPROFILE-Failed: There is no active session. MID = ' . $MID;
                    $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, '', $apiDetails, '', 2);
                    if ($isInserted == 0) {
                        $logMessage = "Failed to insert to APILogs.";
                        $logger->log($logger->logdate, "[GETPROFILE ERROR]: MID " . $MID . " || ", $logMessage);
                    }

                    exit;
                }
            }
        } else {
            $transMsg = "One or more fields is not set or is blank.";
            $errorCode = 1;
            Utilities::log("ReturnMessage: " . $transMsg . " ErrorCode: " . $errorCode);
            $data = CommonController::retMsgGetProfile($module, $profile, $errorCode, $transMsg);
            $message = "[" . $module . "] Output: " . CJSON::encode($data);
            $appLogger->log($appLogger->logdate, "[response]", $message);
            $this->_sendResponse(200, CJSON::encode($data));
            $logMessage = 'One or more fields is not set or is blank.';
            $logger->log($logger->logdate, "[GETPROFILE ERROR]: MID " . $MID . " || ", $logMessage);
            $apiDetails = 'GETPROFILE-Failed: Invalid input parameters.';
            $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, '', $apiDetails, '', 2);
            if ($isInserted == 0) {
                $logMessage = "Failed to insert to APILogs.";
                $logger->log($logger->logdate, "[GETPROFILE ERROR]: MID " . $MID . " || ", $logMessage);
            }

            exit;
        }
    }

    //@date 07-11-2014
    //@purpose sending SMS alert to player's mobile number upon registration or redemption
    private function _sendSMS($methodID, $mobileNumber, $redeemedDate, $serialNumber, $quantity, $prefixTrackingID, $lastInsertedID, $redeemedPoints, $couponSeries = '') {
        $smsRequestLogsModel = new SMSRequestLogsModel();
        $ref_SmsAPIMethodsModel = new Ref_SMSApiMethodsModel();
        $logger = new ErrorLogger();
        $apiLogsModel = new APILogsModel();

        $logType = '';

        if ($methodID == 3) {
            $apiMethod = 3;
            $apiModule = 'REGISTERMEMBER';
        } else {
            $apiMethod = 8;
            $apiModule = 'REDEEMITEMS';
        }

        //match to 09 or 639 in mobile number
        $match = substr($mobileNumber, 0, 3);
        if ($match == "639") {
            $mnCount = count($mobileNumber);
            if (!$mnCount == 12) {
                $idType = $methodID == 1 ? "CouponRedemptionLogID: " : ($methodID == 2 ? "ItemRedemptionLogID: " : "");
                $logType = $methodID == 1 ? "[COUPON REDEMPTION ERROR] " : ($methodID == 2 ? "[ITEM REDEMPTION ERROR] " : "");
                $message = "Failed to send SMS: Invalid Mobile Number [" . $idType . " $lastInsertedID].";
                $logger->log($logger->logdate, $mobileNumber . " - " . $logType, $message);
                $apiDetails = $apiModule . '-Failed: SendSMS-Invalid Mobile Number. MobileNo = ' . $mobileNumber;
                $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, '', $apiDetails, '', 2);
                if ($isInserted == 0) {
                    $logMessage = "Failed to insert to APILogs.";
                    $logger->log($logger->logdate, " [" . $apiModule . " ERROR] " . $mobileNumber . " - ", $logMessage);
                }

                exit;
            } else {
                $templateID = $ref_SmsAPIMethodsModel->getSMSMethodTemplateID($methodID);
                $templateID = $templateID['SMSTemplateID'];
                $smsLastInsertedID = $smsRequestLogsModel->insertSMSRequestLogs($methodID, $mobileNumber, $redeemedDate, $couponSeries, $serialNumber, $quantity);
                if ($smsLastInsertedID != 0 && $smsLastInsertedID != '') {
                    $trackingID = $prefixTrackingID . $smsLastInsertedID;
                    $apiURL = Yii::app()->params['SMSURI'];
                    $appID = Yii::app()->params['app_id'];
                    $membershipSmsAPI = new MembershipSmsAPI($apiURL, $appID);
                    if ($couponSeries != '' && $methodID == 1)
                        $smsResult = $membershipSmsAPI->sendCouponRedemption($mobileNumber, $templateID, $couponSeries, $serialNumber, $quantity, $trackingID);
                    else
                        $smsResult = $membershipSmsAPI->sendItemRedemption($mobileNumber, $templateID, $serialNumber, $trackingID, $redeemedPoints);

                    if ($smsResult['status'] != 1) {
                        $idType = $methodID == 1 ? "CouponRedemptionLogID: " : ($methodID == 2 ? "ItemRedemptionLogID: " : "");
                        $logType = $methodID == 1 ? "[COUPON REDEMPTION ERROR] " : ($methodID == 2 ? "[ITEM REDEMPTION ERROR] " : "");
                        $message = "Failed to send SMS [" . $idType . " $lastInsertedID].";
                        $logger->log($logger->logdate, $mobileNumber . " - " . $logType, $message);
                        $apiDetails = $apiModule . '-Failed: SendSMS.';
                        $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, '', $apiDetails, '', 2);
                        if ($isInserted == 0) {
                            $logMessage = "Failed to insert to APILogs.";
                            $logger->log($logger->logdate, " [" . $apiModule . " ERROR] " . $mobileNumber . " - ", $logMessage);
                        }

                        exit;
                    }
                } else {
                    $idType = $methodID == 1 ? "CouponRedemptionLogID: " : ($methodID == 2 ? "ItemRedemptionLogID: " : "");
                    $logType = $methodID == 1 ? "[COUPON REDEMPTION ERROR] " : ($methodID == 2 ? "[ITEM REDEMPTION ERROR] " : "");
                    $message = "Failed to send SMS: Failed to log event on database [" . $idType . " $lastInsertedID].";
                    $logger->log($logger->logdate, $mobileNumber . " - " . $logType, $message);
                    $apiDetails = $apiModule . '-Failed: SendSMS-Failed to insert to smsrequestlogs.';
                    $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, '', $apiDetails, '', 2);
                    if ($isInserted == 0) {
                        $logMessage = "Failed to insert to APILogs.";
                        $logger->log($logger->logdate, " [" . $apiModule . " ERROR] " . $mobileNumber . " - ", $logMessage);
                    }

                    exit;
                }
            }
        } else {
            $match = substr($mobileNumber, 0, 2);
            if ($match == "09") {
                $mnCount = count($mobileNumber);
                if (!$mnCount == 11) {
                    $idType = $methodID == 1 ? "CouponRedemptionLogID: " : ($methodID == 2 ? "ItemRedemptionLogID: " : "");
                    $logType = $methodID == 1 ? "[COUPON REDEMPTION ERROR] " : ($methodID == 2 ? "[ITEM REDEMPTION ERROR] " : "");
                    $message = "Failed to send SMS: Invalid Mobile Number [" . $idType . " $lastInsertedID].";
                    $logger->log($logger->logdate, $mobileNumber . " - " . $logType, $message);
                    $apiDetails = $apiModule . '-Failed: SendSMS-Invalid Mobile Number. MobileNo = ' . $mobileNumber;
                    $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, '', $apiDetails, '', 2);
                    if ($isInserted == 0) {
                        $logMessage = "Failed to insert to APILogs.";
                        $logger->log($logger->logdate, " [" . $apiModule . " ERROR] " . $mobileNumber . " - ", $logMessage);
                    }

                    exit;
                } else {
                    $mobileNumber = str_replace("09", "639", $mobileNumber);
                    $templateID = $ref_SmsAPIMethodsModel->getSMSMethodTemplateID($methodID);
                    $templateID = $templateID['SMSTemplateID'];
                    $smsLastInsertedID = $smsRequestLogsModel->insertSMSRequestLogs($methodID, $mobileNumber, $redeemedDate, $couponSeries, $serialNumber, $quantity);
                    if ($smsLastInsertedID != 0 && $smsLastInsertedID != '') {
                        $trackingID = $prefixTrackingID . $smsLastInsertedID;
                        $apiURL = Yii::app()->params['SMSURI'];
                        $appID = Yii::app()->params['app_id'];
                        $membershipSmsAPI = new MembershipSmsAPI($apiURL, $appID);
                        if ($couponSeries != '' && $methodID == 1) {
                            $smsResult = $membershipSmsAPI->sendCouponRedemption($mobileNumber, $templateID, $couponSeries, $serialNumber, $quantity, $trackingID);
                        } else {
                            $smsResult = $membershipSmsAPI->sendItemRedemption($mobileNumber, $templateID, $serialNumber, $trackingID, $redeemedPoints);
                        }

                        if ($smsResult['status'] != 1) {
                            $idType = $methodID == 1 ? "CouponRedemptionLogID: " : ($methodID == 2 ? "ItemRedemptionLogID: " : "");
                            $logType = $methodID == 1 ? "[COUPON REDEMPTION ERROR] " : ($methodID == 2 ? "[ITEM REDEMPTION ERROR] " : "");
                            $message = "Failed to send SMS [" . $idType . " $lastInsertedID].";
                            $logger->log($logger->logdate, $mobileNumber . " - " . $logType, $message);
                            $apiDetails = $apiModule . '-Failed: SendSMS.';
                            $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, '', $apiDetails, '', 2);
                            if ($isInserted == 0) {
                                $logMessage = "Failed to insert to APILogs.";
                                $logger->log($logger->logdate, " [" . $apiModule . " ERROR] " . $mobileNumber . " - ", $logMessage);
                            }

                            exit;
                        }
                    } else {
                        $idType = $methodID == 1 ? "CouponRedemptionLogID: " : ($methodID == 2 ? "ItemRedemptionLogID: " : "");
                        $logType = $methodID == 1 ? "[COUPON REDEMPTION ERROR] " : ($methodID == 2 ? "[ITEM REDEMPTION ERROR] " : "");
                        $message = "Failed to send SMS: Error on logging event in database [" . $idType . " $lastInsertedID].";
                        $logger->log($logger->logdate, $mobileNumber . " - " . $logType, $message);
                        $apiDetails = $apiModule . '-Failed: SendSMS-Failed to insert to smsrequestlogs.';
                        $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, '', $apiDetails, '', 2);
                        if ($isInserted == 0) {
                            $logMessage = "Failed to insert to APILogs.";
                            $logger->log($logger->logdate, " [" . $apiModule . " ERROR] " . $mobileNumber . " - ", $logMessage);
                        }

                        exit;
                    }
                }
            } else {
                $idType = $methodID == 1 ? "CouponRedemptionLogID: " : ($methodID == 2 ? "ItemRedemptionLogID: " : "");
                $logType = $methodID == 1 ? "[COUPON REDEMPTION ERROR] " : ($methodID == 2 ? "[ITEM REDEMPTION ERROR] " : "");
                $message = "Failed to send SMS: Invalid Mobile Number [" . $idType . " $lastInsertedID].";
                $logger->log($logger->logdate, $mobileNumber . " - " . $logType, $message);
                $apiDetails = $apiModule . '-Failed: SendSMS-Invalid mobile number. MobileNo = ' . $mobileNumber;
                $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, '', $apiDetails, '', 2);
                if ($isInserted == 0) {
                    $logMessage = "Failed to insert to APILogs.";
                    $logger->log($logger->logdate, " [" . $apiModule . " ERROR] " . $mobileNumber . " - ", $logMessage);
                }

                exit;
            }
        }

        return $smsResult['status'];
    }

    //@date 07-04-2014
    //@purpose retrieves genders available for selection
    public function actionGetGender() {
        $module = 'GetGender';

        $gender = array(array('GenderID' => 1, 'GenderDescription' => 'Male'), array('GenderID' => 2, 'GenderDescription' => 'Female'));
        $this->_sendResponse(200, CJSON::encode(CommonController::retMsgGetGender($module, $gender)));
    }

    //@pupose retrieves the ID types available for selection
    public function actionGetIDPresented() {
        $module = 'GetIDPresented';

        $refIdentifications = new Ref_IdentificationsModel();

        $idPresented = $refIdentifications->getIDPresentedList();

        for ($itr = 0; $itr < count($idPresented); $itr++) {
            $identificationID[$itr] = $idPresented[$itr]['IdentificationID'];
            $identificationName[$itr] = $idPresented[$itr]['IdentificationName'];
            $idPresented[$itr] = array('PresentedID' => $identificationID[$itr], 'PresentedIDDescription' => $identificationName[$itr]);
        }

        $this->_sendResponse(200, CJSON::encode(CommonController::retMsgGetIDPresented($module, $idPresented)));
    }

    //@purpose retrieves the nationalities available for selection
    public function actionGetNationality() {
        $module = 'GetNationality';

        $refNationality = new Ref_NationalityModel();

        $nationality = $refNationality->getNationalityList();

        for ($itr = 0; $itr < count($nationality); $itr++) {
            $nationalityID[$itr] = $nationality[$itr]['NationalityID'];
            $nationalityName[$itr] = $nationality[$itr]['Name'];
            $nationality[$itr] = array('NationalityID' => $nationalityID[$itr], 'NationalityDescription' => $nationalityName[$itr]);
        }

        $this->_sendResponse(200, CJSON::encode(CommonController::retMsgGetNationality($module, $nationality)));
    }

    //@purpose retrieves the occupations available for selection
    public function actionGetOccupation() {
        $module = 'GetOccupation';

        $refOccupations = new Ref_OccupationsModel();

        $occupation = $refOccupations->getOccupationList();

        for ($itr = 0; $itr < count($occupation); $itr++) {
            $occupationID[$itr] = $occupation[$itr]['OccupationID'];
            $occupationName[$itr] = $occupation[$itr]['Name'];
            $occupation[$itr] = array('OccupationID' => $occupationID[$itr], 'OccupationDescription' => $occupationName[$itr]);
        }

        $this->_sendResponse(200, CJSON::encode(CommonController::retMsgGetOccupation($module, $occupation)));
    }

    //@purpose retrieves member classification if smoker or non-smoker
    public function actionGetIsSmoker() {
        $module = 'GetIsSmoker';

        $isSmoker = array(array('IsSmokerID' => 1, 'IsSmokerDescription' => 'Smoker'), array('IsSmokerID' => 2, 'IsSmokerDescription' => 'Non-smoker'));
        $this->_sendResponse(200, CJSON::encode(CommonController::retMsgGetIsSmoker($module, $isSmoker)));
    }

    //@date 08-08-2014
    //@purpose retrieves referrer list
    public function actionGetReferrer() {
        $module = 'GetReferrer';

        $refReferrer = new Ref_ReferrerModel();

        $referrer = $refReferrer->getReferrerList();

        for ($itr = 0; $itr < count($referrer); $itr++) {
            $referrerID[$itr] = $referrer[$itr]['ReferrerID'];
            $referrerName[$itr] = $referrer[$itr]['Name'];
            $referrer[$itr] = array('ReferrerID' => $referrerID[$itr], 'ReferrerDescription' => $referrerName[$itr]);
        }

        $this->_sendResponse(200, CJSON::encode(CommonController::retMsgGetReferrer($module, $referrer)));
    }

    //@purpose retrieves region list
    public function actionGetRegion() {
        $module = 'GetRegion';

        $refRegions = new Ref_RegionsModel();

        $region = $refRegions->getRegionList();

        for ($itr = 0; $itr < count($region); $itr++) {
            $regionID[$itr] = $region[$itr]['RegionID'];
            $regionName[$itr] = $region[$itr]['RegionName'];
            $region[$itr] = array('RegionID' => $regionID[$itr], 'RegionDescription' => $regionName[$itr]);
        }

        $this->_sendResponse(200, CJSON::encode(CommonController::retMsgGetRegion($module, $region)));
    }

    //@purpose retrieves region list
    public function actionGetCity() {
        $module = 'GetCity';

        $refCities = new Ref_CitiesModel();

        $city = $refCities->getCityList();

        for ($itr = 0; $itr < count($city); $itr++) {
            $cityID[$itr] = $city[$itr]['CityID'];
            $cityName[$itr] = $city[$itr]['CityName'];
            $city[$itr] = array('CityID' => $cityID[$itr], 'CityDescription' => $cityName[$itr]);
        }

        $this->_sendResponse(200, CJSON::encode(CommonController::retMsgGetRegion($module, $city)));
    }

    public function actionLogout() {
        $request = $this->_readJsonRequest();

        $transMsg = '';
        $errorCode = '';
        $module = 'Logout';
        $apiMethod = 14;

        $appLogger = new AppLogger();

        $paramval = CJSON::encode($request);
        $message = "[" . $module . "] Input: " . $paramval;
        $appLogger->log($appLogger->logdate, "[request]", $message);

        $memberSessionsModel = new MemberSessionsModel();
        $logger = new ErrorLogger();
        $apiLogsModel = new APILogsModel();
        $auditTrailModel = new AuditTrailModel();
        $membersModel = new MembersModel();

        if (isset($request['MPSessionID'])) {
            if ($request['MPSessionID'] == '') {
                $logMessage = 'One or more fields is not set or is blank.';
                $transMsg = 'One or more fields is not set or is blank.';
                $errorCode = 1;
                Utilities::log("ReturnMessage: " . $transMsg . " ErrorCode: " . $errorCode);
                $data = CommonController::retMsg($module, $errorCode, $transMsg);
                $message = "[" . $module . "] Output: " . CJSON::encode($data);
                $appLogger->log($appLogger->logdate, "[response]", $message);
                $this->_sendResponse(200, CJSON::encode($data));
                $logger->log($logger->logdate, "[LOGOUT ERROR]: " . $request['MPSessionID'] . " || ", $logMessage);
                $apiDetails = 'LOGOUT-Failed: Invalid input parameter.';
                $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, '', $apiDetails, '', 2);
                if ($isInserted == 0) {
                    $logMessage = "Failed to insert to APILogs.";
                    $logger->log($logger->logdate, "[LOGOUT ERROR]: " . $request['MPSessionID'] . " || ", $logMessage);
                }

                exit;
            } else {
                $mpSessionID = trim($request['MPSessionID']);
                $memberSession = $memberSessionsModel->getMID($mpSessionID);
                if ($memberSession) {
                    $MID = $memberSession['MID'];
                    $refID = $MID;
                    $memberDetails = $membersModel->getMemberDetailsByMID($MID);
                    $username = $memberDetails['UserName'];
                } else {
                    $transMsg = "Session does not exist.";
                    $errorCode = 2;
                    $logMessage = 'Session does not exist.';
                    Utilities::log("ReturnMessage: " . $transMsg . " ErrorCode: " . $errorCode);
                    $data = CommonController::retMsg($module, $errorCode, $transMsg);
                    $message = "[" . $module . "] Output: " . CJSON::encode($data);
                    $appLogger->log($appLogger->logdate, "[response]", $message);
                    $this->_sendResponse(200, CJSON::encode($data));
                    $logger->log($logger->logdate, "[LOGOUT ERROR]: " . $request['MPSessionID'] . " || ", $logMessage);
                    $apiDetails = 'LOGOUT-Failed: There is no active session for this account.';
                    $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, '', $apiDetails, '', 2);
                    if ($isInserted == 0) {
                        $logMessage = "Failed to insert to APILogs.";
                        $logger->log($logger->logdate, "[LOGOUT ERROR]: " . $request['MPSessionID'] . " || ", $logMessage);
                    }

                    exit;
                }
                $isDeleted = $memberSessionsModel->deleteSession($MID, $mpSessionID);
                if ($isDeleted == 0) {
                    $transMsg = "Failed to delete session.";
                    $errorCode = 3;
                    $logMessage = 'Failed to delete session.';
                    Utilities::log("ReturnMessage: " . $transMsg . " ErrorCode: " . $errorCode);
                    $data = CommonController::retMsg($module, $errorCode, $transMsg);
                    $message = "[" . $module . "] Output: " . CJSON::encode($data);
                    $appLogger->log($appLogger->logdate, "[response]", $message);
                    $this->_sendResponse(200, CJSON::encode($data));
                    $logger->log($logger->logdate, "[LOGOUT ERROR]: " . $MID . " || ", $logMessage);
                    $apiDetails = 'LOGOUT-DeleteSession-Failed: Error in deleting session from membersessions table. [MID] = ' . $MID . ' [MPSessionID] = ' . $mpSessionID;
                    $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, $refID, $apiDetails, '', 2);
                    if ($isInserted == 0) {
                        $logMessage = "Failed to insert to APILogs.";
                        $logger->log($logger->logdate, "[LOGOUT ERROR]: " . $MID . " || ", $logMessage);
                    }

                    exit;
                }

                $transMsg = "No error, transaction successful.";
                $logMessage = 'No error, transaction successful.';
                $errorCode = 0;
                Utilities::log("ReturnMessage: " . $transMsg . " ErrorCode: " . $errorCode);
                $data = CommonController::retMsg($module, $errorCode, $transMsg);
                $message = "[" . $module . "] Output: " . CJSON::encode($data);
                $appLogger->log($appLogger->logdate, "[response]", $message);
                $this->_sendResponse(200, CJSON::encode($data));
                $logger->log($logger->logdate, "[LOGOUT SUCCESSFUL]: " . $MID . " || ", $logMessage);

                $apiDetails = 'Successful.';
                $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, $refID, $apiDetails, '', 1);
                if ($isInserted == 0) {
                    $logMessage = "Failed to insert to APILogs.";
                    $logger->log($logger->logdate, "[LOGOUT ERROR]: " . $MID . " || ", $logMessage);
                }
                $auditTrailModel->logEvent(AuditTrailModel::API_LOGOUT, 'Logout: ' . $username, array('MID' => $MID, 'SessionID' => $mpSessionID));
                exit;
            }
        } else {
            $logMessage = 'One or more fields is not set or is blank.';
            $transMsg = 'One or more fields is not set or is blank.';
            $errorCode = 1;
            Utilities::log("ReturnMessage: " . $transMsg . " ErrorCode: " . $errorCode);
            $data = CommonController::retMsg($module, $errorCode, $transMsg);
            $message = "[" . $module . "] Output: " . CJSON::encode($data);
            $appLogger->log($appLogger->logdate, "[response]", $message);
            $this->_sendResponse(200, CJSON::encode($data));
            $logger->log($logger->logdate, "[LOGOUT ERROR]: " . $request['MPSessionID'] . " || ", $logMessage);
            $apiDetails = 'LOGOUT-Failed: Invalid input parameter.';
            $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, '', $apiDetails, '', 2);
            if ($isInserted == 0) {
                $logMessage = "Failed to insert to APILogs.";
                $logger->log($logger->logdate, "[LOGOUT ERROR]: " . $request['MPSessionID'] . " || ", $logMessage);
            }

            exit;
        }
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

    //@date 09-15-2014
    //@purpose member registration for Bar Tour
    public function actionRegisterMemberBT() {
        $request = $this->_readJsonRequest();
        $transMsg = '';
        $errorCode = '';
        $module = 'RegisterMemberBT';
        $apiMethod = 18;

        $appLogger = new AppLogger();

        $paramval = CJSON::encode($request);
        $message = "[" . $module . "] Input: " . $paramval;
        $appLogger->log($appLogger->logdate, "[request]", $message);

        $logger = new ErrorLogger();
        $apiLogsModel = new APILogsModel();

        if (isset($request['FirstName']) && isset($request['LastName']) && isset($request['MobileNo']) && isset($request['EmailAddress']) && isset($request['Birthdate'])) {
            if (($request['FirstName'] == '') || ($request['LastName'] == '') || ($request['MobileNo'] == '') || ($request['EmailAddress'] == '') || ($request['Birthdate'] == '')) {
                $transMsg = "One or more fields is not set or is blank.";
                $errorCode = 1;
                Utilities::log("ReturnMessage: " . $transMsg . " ErrorCode: " . $errorCode);
                $data = CommonController::retMsgRegisterMemberBT($module, '', '', $errorCode, $transMsg);
                $message = "[" . $module . "] Output: " . CJSON::encode($data);
                $appLogger->log($appLogger->logdate, "[response]", $message);
                $this->_sendResponse(200, CJSON::encode($data));
                $logMessage = 'One or more fields is not set or is blank.';
                $logger->log($logger->logdate, "[REGISTERMEMBERBT ERROR]: " . $request['FirstName'] . " || " . $request['LastName'] . " || " . $request['MobileNo'] . " || " . $request['EmailAddress'] . " || " . $request['Birthdate'] . " || ", $logMessage);
                $apiDetails = 'REGISTERMEMBERBT-Failed: Invalid input parameters. ';
                $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, '', $apiDetails, '', 2);
                if ($isInserted == 0) {
                    $logMessage = "Failed to insert to APILogs.";
                    $logger->log($logger->logdate, "[REGISTERMEMBERBT ERROR]: " . $request['FirstName'] . " || " . $request['LastName'] . " || " . $request['MobileNo'] . " || " . $request['EmailAddress'] . " || " . $request['Birthdate'] . " || ", $logMessage);
                }

                exit;
            } else if (strlen($request['FirstName']) < 2 || strlen($request['LastName']) < 2) {
                $transMsg = "Name should not be less than 2 characters long.";
                $errorCode = 14;
                Utilities::log("ReturnMessage: " . $transMsg . " ErrorCode: " . $errorCode);
                $data = CommonController::retMsgRegisterMemberBT($module, '', '', $errorCode, $transMsg);
                $message = "[" . $module . "] Output: " . CJSON::encode($data);
                $appLogger->log($appLogger->logdate, "[response]", $message);
                $this->_sendResponse(200, CJSON::encode($data));
                $logMessage = 'Name should not be less than 2 characters long.';
                $logger->log($logger->logdate, "[REGISTERMEMBERBT ERROR]: " . $request['FirstName'] . " || " . $request['LastName'] . " || ", $logMessage);
                $apiDetails = 'REGISTERMEMBERBT-Failed: Invalid input parameters. ';
                $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, '', $apiDetails, '', 2);
                if ($isInserted == 0) {
                    $logMessage = "Failed to insert to APILogs.";
                    $logger->log($logger->logdate, "[REGISTERMEMBERBT ERROR]: " . $request['FirstName'] . " || " . $request['LastName'] . " || ", $logMessage);
                }

                exit;
            } else if (preg_match("/^[A-Za-z\s]+$/", trim($request['FirstName'])) == 0 || preg_match("/^[A-Za-z\s]+$/", trim($request['LastName'])) == 0) {
                $transMsg = "Name should consist of letters and spaces only.";
                $errorCode = 17;
                Utilities::log("ReturnMessage: " . $transMsg . " ErrorCode: " . $errorCode);
                $data = CommonController::retMsgRegisterMemberBT($module, '', '', $errorCode, $transMsg);
                $message = "[" . $module . "] Output: " . CJSON::encode($data);
                $appLogger->log($appLogger->logdate, "[response]", $message);
                $this->_sendResponse(200, CJSON::encode($data));
                $logMessage = 'Name should consist of letters only.';
                $logger->log($logger->logdate, "[REGISTERMEMBERBT ERROR]: " . $request['FirstName'] . " || " . $request['LastName'] . " || ", $logMessage);
                $apiDetails = 'REGISTERMEMBERBT-Failed: Invalid input parameters. ';
                $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, '', $apiDetails, '', 2);
                if ($isInserted == 0) {
                    $logMessage = "Failed to insert to APILogs.";
                    $logger->log($logger->logdate, "[REGISTERMEMBERBT ERROR]: " . $request['FirstName'] . " || " . $request['LastName'] . " || ", $logMessage);
                }

                exit;
            } else if ((substr($request['MobileNo'], 0, 3) == "639" && strlen($request['MobileNo']) != 12) || (substr($request['MobileNo'], 0, 2) == "09" && strlen($request['MobileNo']) != 11)) {
                $transMsg = "Invalid Mobile Number.";
                $errorCode = 97;
                Utilities::log("ReturnMessage: " . $transMsg . " ErrorCode: " . $errorCode);
                $data = CommonController::retMsgRegisterMemberBT($module, '', '', $errorCode, $transMsg);
                $message = "[" . $module . "] Output: " . CJSON::encode($data);
                $appLogger->log($appLogger->logdate, "[response]", $message);
                $this->_sendResponse(200, CJSON::encode($data));
                $logMessage = 'Invalid Mobile Number.';
                $logger->log($logger->logdate, "[REGISTERMEMBERBT ERROR]: " . $request['FirstName'] . " || " . $request['LastName'] . " || " . $request['MobileNo'] . " || ", $logMessage);
                $apiDetails = 'REGISTERMEMBERBT-Failed: Invalid input parameters. ';
                $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, '', $apiDetails, '', 2);
                if ($isInserted == 0) {
                    $logMessage = "Failed to insert to APILogs.";
                    $logger->log($logger->logdate, "[REGISTERMEMBERBT ERROR]: " . $request['FirstName'] . " || " . $request['LastName'] . " || " . $request['MobileNo'] . " || ", $logMessage);
                }

                exit;
            } else if ((substr($request['MobileNo'], 0, 2) != '09' && substr($request['MobileNo'], 0, 3) != '639')) {
                $transMsg = "Mobile number should begin with either '09' or '639'.";
                $errorCode = 69;
                Utilities::log("ReturnMessage: " . $transMsg . " ErrorCode: " . $errorCode);
                $data = CommonController::retMsgRegisterMemberBT($module, '', '', $errorCode, $transMsg);
                $message = "[" . $module . "] Output: " . CJSON::encode($data);
                $appLogger->log($appLogger->logdate, "[response]", $message);
                $this->_sendResponse(200, CJSON::encode($data));
                $logMessage = "Mobile number should begin with either '09' or '639'.";
                $logger->log($logger->logdate, "[REGISTERMEMBERBT ERROR]: " . $request['FirstName'] . " || " . $request['LastName'] . " || " . $request['MobileNo'] . " || ", $logMessage);
                $apiDetails = 'REGISTERMEMBERBT-Failed: Invalid input parameters. ';
                $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, '', $apiDetails, '', 2);
                if ($isInserted == 0) {
                    $logMessage = "Failed to insert to APILogs.";
                    $logger->log($logger->logdate, "[REGISTERMEMBERBT ERROR]: " . $request['FirstName'] . " || " . $request['LastName'] . " || " . $request['MobileNo'] . " || ", $logMessage);
                }

                exit;
            } else if (!is_numeric($request['MobileNo'])) {
                $transMsg = "Mobile number should consist of numbers only.";
                $errorCode = 16;
                Utilities::log("ReturnMessage: " . $transMsg . " ErrorCode: " . $errorCode);
                $data = CommonController::retMsgRegisterMemberBT($module, '', '', $errorCode, $transMsg);
                $message = "[" . $module . "] Output: " . CJSON::encode($data);
                $appLogger->log($appLogger->logdate, "[response]", $message);
                $this->_sendResponse(200, CJSON::encode($data));
                $logMessage = 'Mobile number should consist of numbers only.';
                $logger->log($logger->logdate, "[REGISTERMEMBERBT ERROR]: " . $request['FirstName'] . " || " . $request['LastName'] . " || " . $request['MobileNo'] . " || ", $logMessage);
                $apiDetails = 'REGISTERMEMBERBT-Failed: Invalid input parameters. ';
                $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, '', $apiDetails, '', 2);
                if ($isInserted == 0) {
                    $logMessage = "Failed to insert to APILogs.";
                    $logger->log($logger->logdate, "[REGISTERMEMBERBT ERROR]: " . $request['FirstName'] . " || " . $request['LastName'] . " || " . $request['MobileNo'] . " || ", $logMessage);
                }

                exit;
            } else if (!Utilities::validateEmail($request['EmailAddress'])) {
                $transMsg = "Invalid Email Address.";
                $errorCode = 5;
                Utilities::log("ReturnMessage: " . $transMsg . " ErrorCode: " . $errorCode);
                $data = CommonController::retMsgRegisterMemberBT($module, '', '', $errorCode, $transMsg);
                $message = "[" . $module . "] Output: " . CJSON::encode($data);
                $appLogger->log($appLogger->logdate, "[response]", $message);
                $this->_sendResponse(200, CJSON::encode($data));
                $logMessage = 'Invalid Email Address.';
                $logger->log($logger->logdate, "[REGISTERMEMBERBT ERROR]: " . $request['FirstName'] . " || " . $request['LastName'] . " || " . $request['EmailAddress'] . " || ", $logMessage);
                $apiDetails = 'REGISTERMEMBERBT-Failed: Invalid input parameters. ';
                $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, '', $apiDetails, '', 2);
                if ($isInserted == 0) {
                    $logMessage = "Failed to insert to APILogs.";
                    $logger->log($logger->logdate, "[REGISTERMEMBERBT ERROR]: " . $request['FirstName'] . " || " . $request['LastName'] . " || " . $request['EmailAddress'] . " || ", $logMessage);
                }

                exit;
            } else if ($this->_validateDate($request['Birthdate']) == FALSE) {
                $transMsg = "Please input a valid Date (yyyy-mm-dd).";
                $errorCode = 80;
                Utilities::log("ReturnMessage: " . $transMsg . " ErrorCode: " . $errorCode);
                $data = CommonController::retMsgRegisterMemberBT($module, '', '', $errorCode, $transMsg);
                $message = "[" . $module . "] Output: " . CJSON::encode($data);
                $appLogger->log($appLogger->logdate, "[response]", $message);
                $this->_sendResponse(200, CJSON::encode($data));
                $logMessage = 'Please input a valid Date.';
                $logger->log($logger->logdate, "[REGISTERMEMBERBT ERROR]: " . $request['FirstName'] . " || " . $request['LastName'] . " || " . $request['Birthdate'] . " || ", $logMessage);
                $apiDetails = 'REGISTERMEMBERBT-Failed: Invalid input parameters. ';
                $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, '', $apiDetails, '', 2);
                if ($isInserted == 0) {
                    $logMessage = "Failed to insert to APILogs.";
                    $logger->log($logger->logdate, "[REGISTERMEMBERBT ERROR]: " . $request['FirstName'] . " || " . $request['LastName'] . " || " . $request['Birthdate'] . " || ", $logMessage);
                }

                exit;
            } else {
                //start of declaration of models to be used
                $memberInfoModel = new MemberInfoModel();
                $memberCardsModel = new MemberCardsModel();
                $memberSessionsModel = new MemberSessionsModel();
                $membershipTempModel = new MembershipTempModel();
                $membersModel = new MembersModel();
                $auditTrailModel = new AuditTrailModel();
                $smsRequestLogsModel = new SMSRequestLogsModel();
                $ref_SMSApiMethodsModel = new Ref_SMSApiMethodsModel();
                $blackListsModel = new BlackListsModel();
                $couponsModel = new CouponsModel();

                $emailAddress = trim($request['EmailAddress']);
                $firstname = trim($request['FirstName']);
                $lastname = trim($request['LastName']);
                $mobileNumber = trim($request['MobileNo']);
                $birthdate = trim($request['Birthdate']);
                $tz = new DateTimeZone("Asia/Taipei");
                $age = DateTime::createFromFormat('Y-m-d', $birthdate, $tz)->diff(new DateTime('now', $tz))->y;
                $refID = $firstname . ' ' . $lastname;

                //check if member is blacklisted
                $isBlackListed = $blackListsModel->checkIfBlackListed($firstname, $lastname, $birthdate, 3);
                //check if email is active and existing in live membership db
                $activeEmail = $membershipTempModel->checkIfActiveVerifiedEmail($emailAddress);

                if ($activeEmail['COUNT(MID)'] > 0) {
                    $transMsg = "Sorry, " . $emailAddress . " already belongs to an existing account. Please enter another email address.";
                    $errorCode = 21;
                    Utilities::log("ReturnMessage: " . $transMsg . " ErrorCode: " . $errorCode);
                    $data = CommonController::retMsgRegisterMemberBT($module, '', '', $errorCode, $transMsg);
                    $message = "[" . $module . "] Output: " . CJSON::encode($data);
                    $appLogger->log($appLogger->logdate, "[response]", $message);
                    $this->_sendResponse(200, CJSON::encode($data));
                    $logMessage = 'Sorry, ' . $emailAddress . ' already belongs to an existing account. Please enter another email address.';
                    $logger->log($logger->logdate, "[REGISTERMEMBERBT ERROR]: " . $refID . " || ", $logMessage);
                    $apiDetails = 'REGISTERMEMBERBT-Failed: Email is already used. EmailAddress = ' . $emailAddress;
                    $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, $refID, $apiDetails, '', 2);
                    if ($isInserted == 0) {
                        $logMessage = "Failed to insert to APILogs.";
                        $logger->log($logger->logdate, "[REGISTERMEMBERBT ERROR]: " . $refID . " || ", $logMessage);
                    }

                    exit;
                } else if ($isBlackListed['Count'] > 0) {
                    $transMsg = "Registration cannot proceed. Please contact Customer Service.";
                    $errorCode = 22;
                    Utilities::log("ReturnMessage: " . $transMsg . " ErrorCode: " . $errorCode);
                    $data = CommonController::retMsgRegisterMemberBT($module, '', '', $errorCode, $transMsg);
                    $message = "[" . $module . "] Output: " . CJSON::encode($data);
                    $appLogger->log($appLogger->logdate, "[response]", $message);
                    $this->_sendResponse(200, CJSON::encode($data));
                    $logMessage = 'Registration cannot proceed. Please contact Customer Service.';
                    $logger->log($logger->logdate, "[REGISTERMEMBERBT ERROR]: " . $refID . " || ", $logMessage);
                    $apiDetails = 'REGISTERMEMBERBT-Failed: Player is blacklisted. Name = ' . $firstname . ' ' . $lastname . ', Birthdate = ' . $birthdate;
                    $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, $refID, $apiDetails, '', 2);
                    if ($isInserted == 0) {
                        $logMessage = "Failed to insert to APILogs.";
                        $logger->log($logger->logdate, "[REGISTERMEMBERBT ERROR]: " . $refID . " || ", $logMessage);
                    }

                    exit;
                } else if ($age < 21) {
                    $transMsg = "Must be at least 21 years old to register.";
                    $errorCode = 89;
                    Utilities::log("ReturnMessage: " . $transMsg . " ErrorCode: " . $errorCode);
                    $data = CommonController::retMsgRegisterMemberBT($module, '', '', $errorCode, $transMsg);
                    $message = "[" . $module . "] Output: " . CJSON::encode($data);
                    $appLogger->log($appLogger->logdate, "[response]", $message);
                    $this->_sendResponse(200, CJSON::encode($data));
                    $logMessage = 'Must be at least 21 years old to register.';
                    $logger->log($logger->logdate, "[REGISTERMEMBERBT ERROR]: " . $refID . " || ", $logMessage);
                    $apiDetails = 'REGISTERMEMBERBT-Failed: Player is under 21. Name = ' . $firstname . ' ' . $lastname . ', Birthdate = ' . $birthdate;
                    $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, $refID, $apiDetails, '', 2);
                    if ($isInserted == 0) {
                        $logMessage = "Failed to insert to APILogs.";
                        $logger->log($logger->logdate, "[REGISTERMEMBERBT ERROR]: " . $refID . " || ", $logMessage);
                    }

                    exit;
                } else {
                    //check if email is already verified in temp table
                    $tempEmail = $membershipTempModel->checkTempVerifiedEmail($emailAddress);

                    if ($tempEmail['COUNT(a.MID)'] > 0) {

                        $transMsg = "Email is already verified. Please choose a different email address.";
                        $errorCode = 52;
                        Utilities::log("ReturnMessage: " . $transMsg . " ErrorCode: " . $errorCode);
                        $data = CommonController::retMsgRegisterMemberBT($module, '', '', $errorCode, $transMsg);
                        $message = "[" . $module . "] Output: " . CJSON::encode($data);
                        $appLogger->log($appLogger->logdate, "[response]", $message);
                        $this->_sendResponse(200, CJSON::encode($data));
                        $logMessage = 'Email is already verified. Please choose a different email address.';
                        $logger->log($logger->logdate, "[REGISTERMEMBERBT ERROR]: " . $emailAddress . " || ", $logMessage);
                        $apiDetails = 'REGISTERMEMBERBT-Failed: Email is already verified. Email = ' . $emailAddress;
                        $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, $refID, $apiDetails, '', 2);
                        if ($isInserted == 0) {
                            $logMessage = "Failed to insert to APILogs.";
                            $logger->log($logger->logdate, "[REGISTERMEMBERBT ERROR]: " . $emailAddress . " || ", $logMessage);
                        }

                        exit;
                    } else {
                        $lastInsertedMID = $membershipTempModel->registerBT($emailAddress, $firstname, $lastname, $mobileNumber, $birthdate); // ,$password, $idPresented, $idNumber);

                        if ($lastInsertedMID > 0) {
                            $lastInsertedMID = $lastInsertedMID['MID'];
                            $MID = $lastInsertedMID;
                            $mpSessionID = '';

                            $memberInfos = $membershipTempModel->getTempMemberInfoForSMS($lastInsertedMID);

                            //match to 09 or 639 in mobile number
                            $match = substr($memberInfos['MobileNumber'], 0, 3);
                            if ($match == "639") {
                                $mncount = count($memberInfos["MobileNumber"]);
                                if (!$mncount == 12) {
                                    $message = "Failed to send SMS. Invalid Mobile Number.";
                                    $logger->log($logger->logdate, "[REGISTERMEMBERBT ERROR]: MID " . $MID . " || " . $mobileNumber . " || ", $message);
                                    $apiDetails = "REGISTERMEMBERBT-Failed: Failed to send SMS. Invalid Mobile Number [MID = $lastInsertedMID].";
                                    $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, $refID, $apiDetails, '', 2);
                                    if ($isInserted == 0) {
                                        $logMessage = "Failed to insert to APILogs.";
                                        $logger->log($logger->logdate, "[REGISTERMEMBERBT ERROR]: MID " . $MID . " || " . $mobileNumber . " || ", $logMessage);
                                    }
                                } else {
                                    $coupons = $couponsModel->getCoupon();

                                    $couponNumber = $coupons['CouponCode'];
                                    $expiryDate = $coupons['ValidToDate'];
                                    $templateid1 = $ref_SMSApiMethodsModel->getSMSMethodTemplateID(Ref_SMSApiMethodsModel::PLAYER_REGISTRATION_1OF2);
                                    $templateid2 = $ref_SMSApiMethodsModel->getSMSMethodTemplateID(Ref_SMSApiMethodsModel::PLAYER_REGISTRATION_2OF2);

                                    $methodid1 = Ref_SMSApiMethodsModel::PLAYER_REGISTRATION_1OF2;
                                    $methodid2 = Ref_SMSApiMethodsModel::PLAYER_REGISTRATION_2OF2;

                                    $mobileno = $memberInfos["MobileNumber"];
                                    if ($coupons) {
                                        $templateidbt = $ref_SMSApiMethodsModel->getSMSMethodTemplateID(Ref_SMSApiMethodsModel::PLAYER_REGISTRATION_BT);
                                        $templateidbt = $templateidbt['SMSTemplateID'];
                                        $methodidbt = Ref_SMSApiMethodsModel::PLAYER_REGISTRATION_BT;
                                        $smslastinsertedidbt = $smsRequestLogsModel->insertSMSRequestLogs($methodidbt, $mobileno, $memberInfos["DateCreated"]);
                                    } else {
                                        $smslastinsertedidbt = 0;
                                    }

                                    $smslastinsertedid1 = $smsRequestLogsModel->insertSMSRequestLogs($methodid1, $mobileno, $memberInfos["DateCreated"]);
                                    $smslastinsertedid2 = $smsRequestLogsModel->insertSMSRequestLogs($methodid2, $mobileno, $memberInfos["DateCreated"]);

                                    if (($smslastinsertedid1 != 0 && $smslastinsertedid1 != '') && ($smslastinsertedid2 != 0 && $smslastinsertedid2 != '') && ($smslastinsertedidbt != 0 && $smslastinsertedidbt != '')) {
                                        $trackingid1 = "SMSR" . $smslastinsertedid1;
                                        $trackingid2 = "SMSR" . $smslastinsertedid2;
                                        $trackingidbt = "SMSR" . $smslastinsertedidbt;
                                        $apiURL = Yii::app()->params["SMSURI"];
                                        $app_id = Yii::app()->params["app_id"];
                                        $membershipSMSApi = new MembershipSmsAPI($apiURL, $app_id);
                                        $smsresult1 = $membershipSMSApi->sendRegistration1($mobileno, $templateid1['SMSTemplateID'], $memberInfos["DateCreated"], $memberInfos["TemporaryAccountCode"], $trackingid1);
                                        $smsresult2 = $membershipSMSApi->sendRegistration2($mobileno, $templateid2['SMSTemplateID'], $memberInfos["DateCreated"], $memberInfos["TemporaryAccountCode"], $trackingid2);
                                        $smsresult3 = $membershipSMSApi->sendRegistrationBT($mobileno, $templateidbt['SMSTemplateID'], $expiryDate, $couponNumber, $trackingidbt);

                                        if (isset($smsresult1['status']) && isset($smsresult2['status']) && isset($smsresult3['status'])) {
                                            if ($smsresult1['status'] != 1 && $smsresult2['status'] != 1 && $smsresult3['status'] != 1) {
                                                $transMsg = 'Failed to get response from membershipsms api.';
                                                $errorCode = 90;
                                                Utilities::log("ReturnMessage: " . $transMsg . " ErrorCode: " . $errorCode);
                                                $data = CommonController::retMsgRegisterMemberBT($module, '', '', $errorCode, $transMsg);
                                                $message = "[" . $module . "] Output: " . CJSON::encode($data);
                                                $appLogger->log($appLogger->logdate, "[response]", $message);
                                                $this->_sendResponse(200, CJSON::encode($data));
                                                $logMessage = 'Failed to get response from membershipsms api.';
                                                $logger->log($logger->logdate, "[REGISTERMEMBERBT ERROR]: MID " . $MID . " || " . $mobileNumber . " || ", $logMessage);
                                                $apiDetails = 'REGISTERMEMBERBT-Failed: Failed to get response from membershipsms api. MID = ' . $lastInsertedMID;
                                                $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, $refID, $apiDetails, '', 2);
                                                if ($isInserted == 0) {
                                                    $logMessage = "Failed to insert to APILogs.";
                                                    $logger->log($logger->logdate, "[REGISTERMEMBERBT ERROR]: MID " . $MID . " || " . $mobileNumber . " || ", $logMessage);
                                                }
                                            } else {
                                                $isUpdated = $coupons->updateCouponStatus($couponNumber, $MID);
                                                if (!$isUpdated) {
                                                    $logMessage = "Failed to update coupon status.";
                                                    $logger->log($logger->logdate, "[REGISTERMEMBERBT ERROR]: MID " . $MID . " || " . $mobileNumber . " || ", $logMessage);
                                                    exit;
                                                }
                                                $helpers = new Helpers();
                                                $helpers->sendEmailBT($emailAddress, $firstname . ' ' . $lastname, $couponNumber, $expiryDate);
                                                $transMsg = "You have successfully registered! An active Temporary Account will be sent to your email address or mobile number, which can be used to start session or credit points in the absence of Membership Card. Please note that your Registered Account and Temporary Account will be activated only after 24 hours.";
                                                $errorCode = 0;
                                                Utilities::log("ReturnMessage: " . $transMsg . " ErrorCode: " . $errorCode);
                                                $data = CommonController::retMsgRegisterMemberBT($module, $couponNumber, $expiryDate, $errorCode, $transMsg);
                                                $message = "[" . $module . "] Output: " . CJSON::encode($data);
                                                $appLogger->log($appLogger->logdate, "[response]", $message);
                                                $this->_sendResponse(200, CJSON::encode($data));
                                                $logMessage = 'Registration is successful.';
                                                $logger->log($logger->logdate, "[REGISTERMEMBERBT SUCCESSFUL]: MID " . $MID . " || " . $mobileNumber . " || ", $logMessage);
                                                $apiDetails = 'REGISTERMEMBERBT-Success: Registration is successful. MID = ' . $lastInsertedMID;
                                                $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, $refID, $apiDetails, '', 1);
                                                if ($isInserted == 0) {
                                                    $logMessage = "Failed to insert to APILogs.";
                                                    $logger->log($logger->logdate, "[REGISTERMEMBERBT ERROR]: MID " . $MID . " || " . $mobileNumber . " || ", $logMessage);
                                                }
                                            }
                                        } else {
                                            $transMsg = 'Failed to get response from membershipsms api.';
                                            $errorCode = 90;
                                            Utilities::log("ReturnMessage: " . $transMsg . " ErrorCode: " . $errorCode);
                                            $data = CommonController::retMsgRegisterMemberBT($module, '', '', $errorCode, $transMsg);
                                            $message = "[" . $module . "] Output: " . CJSON::encode($data);
                                            $appLogger->log($appLogger->logdate, "[response]", $message);
                                            $this->_sendResponse(200, CJSON::encode($data));
                                            $logMessage = 'Failed to get response from membershipsms api.';
                                            $logger->log($logger->logdate, "[REGISTERMEMBERBT ERROR]: MID " . $MID . " || " . $mobileNumber . " || ", $logMessage);
                                            $apiDetails = 'REGISTERMEMBERBT-Failed: Failed to get response from membershipsms api. MID = ' . $lastInsertedMID;
                                            $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, $refID, $apiDetails, '', 2);
                                            if ($isInserted == 0) {
                                                $logMessage = "Failed to insert to APILogs.";
                                                $logger->log($logger->logdate, "[REGISTERMEMBERBT ERROR]: MID " . $MID . " || " . $mobileNumber . " || ", $logMessage);
                                            }
                                        }
                                    } else {
                                        $message = "Failed to send SMS: Error on logging event in database.";
                                        $logger->log($logger->logdate, "[REGISTERMEMBERBT ERROR]: MID " . $MID . " || " . $mobileNumber . " || ", $message);
                                        $apiDetails = "REGISTERMEMBERBT-Failed: Failed to send SMS. Error on logging event in database. [MID = $lastInsertedMID].";
                                        $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, $refID, $apiDetails, '', 2);
                                        if ($isInserted == 0) {
                                            $logMessage = "Failed to insert to APILogs.";
                                            $logger->log($logger->logdate, "[REGISTERMEMBERBT ERROR]: MID " . $MID . " || " . $mobileNumber . " || ", $logMessage);
                                        }
                                        $errorCode = 88;
                                        Utilities::log("ReturnMessage: " . $message . " ErrorCode: " . $errorCode);
                                        $this->_sendResponse(200, CJSON::encode(CommonController::retMsgRegisterMemberBT($module, '', '', $errorCode, $message)));
                                    }
                                }
                            } else {
                                $match = substr($memberInfos["MobileNumber"], 0, 2);
                                if ($match == "09") {
                                    $mncount = count($memberInfos["MobileNumber"]);

                                    if (!$mncount == 11) {
                                        $message = "Failed to send SMS: Invalid Mobile Number.";
                                        $logger->log($logger->logdate, "[REGISTERMEMBERBT ERROR]: MID " . $MID . " || " . $mobileNumber . " || ", $message);
                                        $apiDetails = "REGISTERMEMBERBT-Failed: Failed to send SMS. Invalid Mobile Number [MID = $lastInsertedMID].";
                                        $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, $refID, $apiDetails, '', 2);
                                        if ($isInserted == 0) {
                                            $logMessage = "Failed to insert to APILogs.";
                                            $logger->log($logger->logdate, "[REGISTERMEMBERBT ERROR]: MID " . $MID . " || " . $mobileNumber . " || ", $logMessage);
                                        }
                                    } else {
                                        $coupons = $couponsModel->getCoupon();

                                        $couponNumber = $coupons['CouponCode'];
                                        $expiryDate = $coupons['ValidToDate'];
                                        $expiryDate = date("Y-m-d", strtotime($expiryDate));
                                        $cpNumber = $memberInfos["MobileNumber"];
                                        $mobileno = $this->formatMobileNumber($cpNumber);
                                        $templateid1 = $ref_SMSApiMethodsModel->getSMSMethodTemplateID(Ref_SMSApiMethodsModel::PLAYER_REGISTRATION_1OF2);
                                        $templateid2 = $ref_SMSApiMethodsModel->getSMSMethodTemplateID(Ref_SMSApiMethodsModel::PLAYER_REGISTRATION_2OF2);
                                        $templateid1 = $templateid1['SMSTemplateID'];
                                        $templateid2 = $templateid2['SMSTemplateID'];

                                        if ($coupons) {
                                            $templateidbt = $ref_SMSApiMethodsModel->getSMSMethodTemplateID(Ref_SMSApiMethodsModel::PLAYER_REGISTRATION_BT);
                                            $templateidbt = $templateidbt['SMSTemplateID'];
                                            $methodidbt = Ref_SMSApiMethodsModel::PLAYER_REGISTRATION_BT;
                                            $smslastinsertedidbt = $smsRequestLogsModel->insertSMSRequestLogs($methodidbt, $mobileno, $memberInfos["DateCreated"]);
                                        } else {

                                            $smslastinsertedidbt = 0;
                                        }

                                        $methodid1 = Ref_SMSApiMethodsModel::PLAYER_REGISTRATION_1OF2;
                                        $methodid2 = Ref_SMSApiMethodsModel::PLAYER_REGISTRATION_2OF2;

                                        $smslastinsertedid1 = $smsRequestLogsModel->insertSMSRequestLogs($methodid1, $mobileno, $memberInfos["DateCreated"]);
                                        $smslastinsertedid2 = $smsRequestLogsModel->insertSMSRequestLogs($methodid2, $mobileno, $memberInfos["DateCreated"]);

                                        if (($smslastinsertedid1 != 0 && $smslastinsertedid1 != '') && ($smslastinsertedidbt != 0 && $smslastinsertedidbt != '') && ($smslastinsertedid2 != 0 && $smslastinsertedid2 != '')) {
                                            $trackingid1 = "SMSR" . $smslastinsertedid1;
                                            $trackingid2 = "SMSR" . $smslastinsertedid2;
                                            $trackingidbt = "SMSR" . $smslastinsertedidbt;
                                            $apiURL = Yii::app()->params['SMSURI'];
                                            $app_id = Yii::app()->params['app_id'];
                                            $membershipSMSApi = new MembershipSmsAPI($apiURL, $app_id);

                                            $smsresult1 = $membershipSMSApi->sendRegistration1($mobileno, $templateid1, $memberInfos["DateCreated"], $memberInfos["TemporaryAccountCode"], $trackingid1);
                                            $smsresult2 = $membershipSMSApi->sendRegistration2($mobileno, $templateid2, $memberInfos["DateCreated"], $memberInfos["TemporaryAccountCode"], $trackingid2);
                                            $smsresult3 = $membershipSMSApi->sendRegistrationBT($mobileno, $templateidbt, $expiryDate, $couponNumber, $trackingidbt);

                                            if (isset($smsresult1['status']) && isset($smsresult2['status']) && isset($smsresult3['status'])) {
                                                if ($smsresult1['status'] != 1 && $smsresult2['status'] != 1 && $smsresult3['status'] != 1) {
                                                    $transMsg = 'Failed to get response from membershipsms api.';
                                                    $errorCode = 90;
                                                    Utilities::log("ReturnMessage: " . $transMsg . " ErrorCode: " . $errorCode);
                                                    $data = CommonController::retMsgRegisterMemberBT($module, '', '', $errorCode, $transMsg);
                                                    $message = "[" . $module . "] Output: " . CJSON::encode($data);
                                                    $appLogger->log($appLogger->logdate, "[response]", $message);
                                                    $this->_sendResponse(200, CJSON::encode($data));
                                                    $logMessage = 'Failed to get response from membershipsms api.';
                                                    $logger->log($logger->logdate, "[REGISTERMEMBERBT ERROR]: MID " . $MID . " || " . $mobileNumber . " || ", $logMessage);
                                                    $apiDetails = 'REGISTERMEMBERBT-Failed: Failed to get response from membershipsms api. MID = ' . $lastInsertedMID;
                                                    $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, $refID, $apiDetails, '', 2);
                                                    if ($isInserted == 0) {
                                                        $logMessage = "Failed to insert to APILogs.";
                                                        $logger->log($logger->logdate, "[REGISTERMEMBERBT ERROR]: MID " . $MID . " || " . $mobileNumber . " || ", $logMessage);
                                                    }
                                                } else {
                                                    $isUpdated = $couponsModel->updateCouponStatus($couponNumber, $MID);
                                                    if (!$isUpdated) {
                                                        $logMessage = "Failed to update coupon status.";
                                                        $logger->log($logger->logdate, "[REGISTERMEMBERBT ERROR]: MID " . $MID . " || " . $mobileNumber . " || ", $logMessage);
                                                        exit;
                                                    }
                                                    $helpers = new Helpers();
                                                    $helpers->sendEmailBT($emailAddress, $firstname . ' ' . $lastname, $couponNumber, $expiryDate);

                                                    $transMsg = "You have successfully registered! An active Temporary Account will be sent to your email address or mobile number, which can be used to start session or credit points in the absence of Membership Card. Please note that your Registered Account and Temporary Account will be activated only after 24 hours.";
                                                    $errorCode = 0;
                                                    Utilities::log("ReturnMessage: " . $transMsg . " ErrorCode: " . $errorCode);
                                                    $data = CommonController::retMsgRegisterMemberBT($module, $couponNumber, $expiryDate, $errorCode, $transMsg);
                                                    $message = "[" . $module . "] Output: " . CJSON::encode($data);
                                                    $appLogger->log($appLogger->logdate, "[response]", $message);
                                                    $this->_sendResponse(200, CJSON::encode($data));
                                                    $logMessage = 'Registration is successful.';
                                                    $logger->log($logger->logdate, "[REGISTERMEMBERBT SUCCESSFUL]: MID " . $MID . " || " . $mobileNumber . " || ", $logMessage);
                                                    $apiDetails = 'REGISTERMEMBERBT-Success: Registration is successful. MID = ' . $lastInsertedMID;
                                                    $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, $refID, $apiDetails, '', 1);

                                                    if ($isInserted == 0) {
                                                        $logMessage = "Failed to insert to APILogs.";
                                                        $logger->log($logger->logdate, "[REGISTERMEMBERBT ERROR]: MID " . $MID . " || " . $mobileNumber . " || ", $logMessage);
                                                    }
                                                }
                                            } else {
                                                $transMsg = 'Failed to get response from membershipsms api.';
                                                $errorCode = 90;
                                                Utilities::log("ReturnMessage: " . $transMsg . " ErrorCode: " . $errorCode);
                                                $data = CommonController::retMsgRegisterMemberBT($module, '', '', $errorCode, $transMsg);
                                                $message = "[" . $module . "] Output: " . CJSON::encode($data);
                                                $appLogger->log($appLogger->logdate, "[response]", $message);
                                                $this->_sendResponse(200, CJSON::encode($data));
                                                $logMessage = 'Failed to get response from membershipsms api.';
                                                $logger->log($logger->logdate, "[REGISTERMEMBERBT ERROR]: MID " . $MID . " || " . $mobileNumber . " || ", $logMessage);
                                                $apiDetails = 'REGISTERMEMBERBT-Failed: Failed to get response from membershipsms api. MID = ' . $lastInsertedMID;
                                                $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, $refID, $apiDetails, '', 2);
                                                if ($isInserted == 0) {
                                                    $logMessage = "Failed to insert to APILogs.";
                                                    $logger->log($logger->logdate, "[REGISTERMEMBERBT ERROR]: MID " . $MID . " || " . $mobileNumber . " || ", $logMessage);
                                                }
                                            }
                                        } else {
                                            $message = "Failed to send SMS: Error on logging event in database.";
                                            $logger->log($logger->logdate, "[REGISTERMEMBERBT ERROR]: MID " . $MID . " || " . $mobileNumber . " || ", $message);
                                            $apiDetails = "REGISTERMEMBERBT-Failed: Failed to send SMS. Error on logging event in database [MID = $lastInsertedMID].";
                                            $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, $refID, $apiDetails, '', 2);
                                            if ($isInserted == 0) {
                                                $logMessage = "Failed to insert to APILogs.";
                                                $logger->log($logger->logdate, "[REGISTERMEMBERBT ERROR]: MID " . $MID . " || " . $mobileNumber . " || ", $logMessage);
                                            }
                                            $errorCode = 88;
                                            Utilities::log("ReturnMessage: " . $message . " ErrorCode: " . $errorCode);
                                            $data = CommonController::retMsgRegisterMemberBT($module, '', '', $errorCode, $transMsg);
                                            $message = "[" . $module . "] Output: " . CJSON::encode($data);
                                            $appLogger->log($appLogger->logdate, "[response]", $message);
                                            $this->_sendResponse(200, CJSON::encode($data));
                                        }
                                    }
                                } else {
                                    $message = "Failed to send SMS: Invalid Mobile Number.";
                                    $logger->log($logger->logdate, "[REGISTERMEMBERBT ERROR]: MID " . $MID . " || " . $mobileNumber . " || ", $message);
                                    $apiDetails = "REGISTERMEMBERBT-Failed: Failed to send SMS. Invalid Mobile Number [MID = $lastInsertedMID].";
                                    $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, $refID, $apiDetails, '', 2);
                                    if ($isInserted == 0) {
                                        $logMessage = "Failed to insert to APILogs.";
                                        $logger->log($logger->logdate, "[REGISTERMEMBERBT ERROR]: MID " . $MID . " || " . $mobileNumber . " || ", $logMessage);
                                    }
                                }
                            }

                            $auditTrailModel->logEvent(AuditTrailModel::API_REGISTER_MEMBER_BT, 'Email: ' . $emailAddress, array('MID' => $lastInsertedMID, 'SessionID' => $mpSessionID));
                        } else {
                            //check if email is already verified in temp table
                            $tempEmail = $membershipTempModel->checkTempVerifiedEmail($emailAddress);
                            if ($tempEmail['COUNT(a.MID)'] > 0) {
                                $transMsg = "Email is already verified. Please choose a different email address.";
                                $errorCode = 52;
                                Utilities::log("ReturnMessage: " . $transMsg . " ErrorCode: " . $errorCode);
                                $data = CommonController::retMsgRegisterMemberBT($module, '', '', $errorCode, $transMsg);
                                $message = "[" . $module . "] Output: " . CJSON::encode($data);
                                $appLogger->log($appLogger->logdate, "[response]", $message);
                                $this->_sendResponse(200, CJSON::encode($data));
                                $logMessage = 'Email is already verified. Please choose a different email address.';
                                $logger->log($logger->logdate, "[REGISTERMEMBERBT ERROR]: MID " . $MID . " || " . $mobileNumber . " || ", $logMessage);
                                $apiDetails = 'REGISTERMEMBERBT-Failed: Email is already verified. Email = ' . $emailAddress;
                                $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, $refID, $apiDetails, '', 2);
                                if ($isInserted == 0) {
                                    $logMessage = "Failed to insert to APILogs.";
                                    $logger->log($logger->logdate, "[REGISTERMEMBERBT ERROR]: MID " . $MID . " || " . $mobileNumber . " || ", $logMessage);
                                }

                                exit;
                            } else {
                                $lastInsertedMID = $membershipTempModel->registerBT($emailAddress, $firstname, $lastname, $mobileNumber, $birthdate);

                                if ($lastInsertedMID > 0) {
                                    $SFID = $lastInsertedMID['SFID'];
                                    $lastInsertedMID = $lastInsertedMID['MID'];
                                    $ID = 0;
                                    $mpSessionID = '';

                                    $memberInfos = $membershipTempModel->getTempMemberInfoForSMS($lastInsertedMID);

                                    //match to 09 or 639 in mobile number
                                    $match = substr($memberInfos['MobileNumber'], 0, 3);
                                    if ($match == "639") {
                                        $mncount = count($memberInfos["MobileNumber"]);
                                        if (!$mncount == 12) {
                                            $message = "Failed to send SMS. Invalid Mobile Number.";
                                            $logger->log($logger->logdate, "[REGISTERMEMBERBT ERROR]: MID " . $MID . " || " . $mobileNumber . " || ", $message);
                                            $apiDetails = "REGISTERMEMBERBT-Failed: Failed to send SMS. Invalid Mobile Number [MID = $lastInsertedMID].";
                                            $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, $refID, $apiDetails, '', 2);
                                            if ($isInserted == 0) {
                                                $logMessage = "Failed to insert to APILogs.";
                                                $logger->log($logger->logdate, "[REGISTERMEMBERBT ERROR]: MID " . $MID . " || " . $mobileNumber . " || ", $logMessage);
                                            }
                                        } else {
                                            $coupons = $couponsModel->getCoupon();

                                            $couponNumber = $coupons['CouponCode'];
                                            $expiryDate = $coupons['ValidToDate'];
                                            $expiryDate = date("Y-m-d", strtotime($expiryDate));
                                            $templateid1 = $ref_SMSApiMethodsModel->getSMSMethodTemplateID(Ref_SMSApiMethodsModel::PLAYER_REGISTRATION_1OF2);
                                            $templateid2 = $ref_SMSApiMethodsModel->getSMSMethodTemplateID(Ref_SMSApiMethodsModel::PLAYER_REGISTRATION_2OF2);

                                            $methodid1 = Ref_SMSApiMethodsModel::PLAYER_REGISTRATION_1OF2;
                                            $methodid2 = Ref_SMSApiMethodsModel::PLAYER_REGISTRATION_2OF2;

                                            $mobileno = $memberInfos["MobileNumber"];
                                            if ($coupons) {
                                                $templateidbt = $ref_SMSApiMethodsModel->getSMSMethodTemplateID(Ref_SMSApiMethodsModel::PLAYER_REGISTRATION_BT);
                                                $templateidbt = $templateidbt['SMSTemplateID'];
                                                $methodidbt = Ref_SMSApiMethodsModel::PLAYER_REGISTRATION_BT;
                                                $smslastinsertedidbt = $smsRequestLogsModel->insertSMSRequestLogs($methodidbt, $mobileno, $memberInfos["DateCreated"]);
                                            } else {

                                                $smslastinsertedidbt = 0;
                                            }
                                            $smslastinsertedid1 = $smsRequestLogsModel->insertSMSRequestLogs($methodid1, $mobileno, $memberInfos["DateCreated"]);
                                            $smslastinsertedid2 = $smsRequestLogsModel->insertSMSRequestLogs($methodid2, $mobileno, $memberInfos["DateCreated"]);

                                            if (($smslastinsertedid1 != 0 && $smslastinsertedid1 != '') && ($smslastinsertedidbt != 0 && $smslastinsertedidbt != '') && ($smslastinsertedid2 != 0 && $smslastinsertedid2 != '')) {
                                                $trackingid1 = "SMSR" . $smslastinsertedid1;
                                                $trackingid2 = "SMSR" . $smslastinsertedid2;
                                                $trackingidbt = "SMSR" . $smslastinsertedidbt;
                                                $apiURL = Yii::app()->params["SMSURI"];
                                                $app_id = Yii::app()->params["app_id"];
                                                $membershipSMSApi = new MembershipSmsAPI($apiURL, $app_id);
                                                $smsresult1 = $membershipSMSApi->sendRegistration1($mobileno, $templateid1['SMSTemplateID'], $memberInfos["DateCreated"], $memberInfos["TemporaryAccountCode"], $trackingid1);
                                                $smsresult2 = $membershipSMSApi->sendRegistration2($mobileno, $templateid2['SMSTemplateID'], $memberInfos["DateCreated"], $memberInfos["TemporaryAccountCode"], $trackingid2);
                                                $smsresult3 = $membershipSMSApi->sendRegistrationBT($mobileno, $templateidbt['SMSTemplateID'], $expiryDate, $couponNumber, $trackingidbt);
                                                if (isset($smsresult1['status']) && isset($smsresult2['status']) && isset($smsresult3['status'])) {
                                                    if ($smsresult1['status'] != 1 && $smsresult2['status'] != 1 && $smsresult3['status'] != 1) {
                                                        $transMsg = 'Failed to get response from membershipsms api.';
                                                        $errorCode = 90;
                                                        Utilities::log("ReturnMessage: " . $transMsg . " ErrorCode: " . $errorCode);
                                                        $data = CommonController::retMsgRegisterMemberBT($module, '', '', $errorCode, $transMsg);
                                                        $message = "[" . $module . "] Output: " . CJSON::encode($data);
                                                        $appLogger->log($appLogger->logdate, "[response]", $message);
                                                        $this->_sendResponse(200, CJSON::encode($data));
                                                        $logMessage = 'Failed to get response from membershipsms api.';
                                                        $logger->log($logger->logdate, "[REGISTERMEMBERBT ERROR]: MID " . $MID . " || " . $mobileNumber . " || ", $logMessage);
                                                        $apiDetails = 'REGISTERMEMBERBT-Failed: Failed to get response from membershipsms api. MID = ' . $lastInsertedMID;
                                                        $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, $refID, $apiDetails, '', 2);
                                                        if ($isInserted == 0) {
                                                            $logMessage = "Failed to insert to APILogs.";
                                                            $logger->log($logger->logdate, "[REGISTERMEMBERBT ERROR]: MID " . $MID . " || " . $mobileNumber . " || ", $logMessage);
                                                        }
                                                    } else {
                                                        $isUpdated = $couponsModel->updateCouponStatus($couponNumber, $MID);
                                                        if (!$isUpdated) {
                                                            $logMessage = "Failed to update coupon status.";
                                                            $logger->log($logger->logdate, "[REGISTERMEMBERBT ERROR]: MID " . $MID . " || " . $mobileNumber . " || ", $logMessage);
                                                            exit;
                                                        }
                                                        $helpers = new Helpers();
                                                        $helpers->sendEmailBT($emailAddress, $firstname . ' ' . $lastname, $couponNumber, $expiryDate);

                                                        $transMsg = "You have successfully registered! An active Temporary Account will be sent to your email address or mobile number, which can be used to start session or credit points in the absence of Membership Card. Please note that your Registered Account and Temporary Account will be activated only after 24 hours.";
                                                        $errorCode = 0;
                                                        Utilities::log("ReturnMessage: " . $transMsg . " ErrorCode: " . $errorCode);
                                                        $data = CommonController::retMsgRegisterMemberBT($module, $errorCode, $transMsg);
                                                        $message = "[" . $module . "] Output: " . CJSON::encode($data);
                                                        $appLogger->log($appLogger->logdate, "[response]", $message);
                                                        $this->_sendResponse(200, CJSON::encode($data));
                                                        $logMessage = 'Registration is successful.';
                                                        $logger->log($logger->logdate, "[REGISTERMEMBERBT SUCCESSFUL]: MID " . $MID . " || " . $mobileNumber . " || ", $logMessage);
                                                        $apiDetails = 'REGISTERMEMBERBT-Success: Registration is successful. MID = ' . $lastInsertedMID;
                                                        $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, $refID, $apiDetails, '', 1);
                                                        if ($isInserted == 0) {
                                                            $logMessage = "Failed to insert to APILogs.";
                                                            $logger->log($logger->logdate, "[REGISTERMEMBERBT ERROR]: MID " . $MID . " || " . $mobileNumber . " || ", $logMessage);
                                                        }
                                                    }
                                                } else {
                                                    $transMsg = 'Failed to get response from membershipsms api.';
                                                    $errorCode = 90;
                                                    Utilities::log("ReturnMessage: " . $transMsg . " ErrorCode: " . $errorCode);
                                                    $data = CommonController::retMsgRegisterMemberBT($module, '', '', $errorCode, $transMsg);
                                                    $message = "[" . $module . "] Output: " . CJSON::encode($data);
                                                    $appLogger->log($appLogger->logdate, "[response]", $message);
                                                    $this->_sendResponse(200, CJSON::encode($data));
                                                    $logMessage = 'Failed to get response from membershipsms api.';
                                                    $logger->log($logger->logdate, "[REGISTERMEMBERBT ERROR]: MID " . $MID . " || " . $mobileNumber . " || ", $logMessage);
                                                    $apiDetails = 'REGISTERMEMBERBT-Failed: Failed to get response from membershipsms api. MID = ' . $lastInsertedMID;
                                                    $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, $refID, $apiDetails, '', 2);
                                                    if ($isInserted == 0) {
                                                        $logMessage = "Failed to insert to APILogs.";
                                                        $logger->log($logger->logdate, "[REGISTERMEMBERBT ERROR]: MID " . $MID . " || " . $mobileNumber . " || ", $logMessage);
                                                    }
                                                }
                                            } else {
                                                $message = "Failed to send SMS: Error on logging event in database.";
                                                $logger->log($logger->logdate, "[REGISTERMEMBERBT ERROR]: MID " . $MID . " || " . $mobileNumber . " || ", $message);
                                                $apiDetails = "REGISTERMEMBERBT-Failed: Failed to send SMS. Error on logging event in database. [MID = $lastInsertedMID].";
                                                $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, $refID, $apiDetails, '', 2);
                                                if ($isInserted == 0) {
                                                    $logMessage = "Failed to insert to APILogs.";
                                                    $logger->log($logger->logdate, "[REGISTERMEMBERBT ERROR]: MID " . $MID . " || " . $mobileNumber . " || ", $logMessage);
                                                }
                                                $errorCode = 88;
                                                Utilities::log("ReturnMessage: " . $message . " ErrorCode: " . $errorCode);
                                                $data = CommonController::retMsgRegisterMemberBT($module, '', '', $errorCode, $transMsg);
                                                $message = "[" . $module . "] Output: " . CJSON::encode($data);
                                                $appLogger->log($appLogger->logdate, "[response]", $message);
                                                $this->_sendResponse(200, CJSON::encode($data));
                                            }
                                        }
                                    } else {
                                        $match = substr($memberInfos["MobileNumber"], 0, 2);
                                        if ($match == "09") {
                                            $mncount = count($memberInfos["MobileNumber"]);
                                            if (!$mncount == 11) {
                                                $message = "Failed to send SMS: Invalid Mobile Number.";
                                                $logger->log($logger->logdate, "[REGISTERMEMBERBT ERROR]: MID " . $MID . " || " . $mobileNumber . " || ", $message);
                                                $apiDetails = "REGISTERMEMBERBT-Failed: Failed to send SMS. Invalid Mobile Number [MID = $lastInsertedMID].";
                                                $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, $refID, $apiDetails, '', 2);
                                                if ($isInserted == 0) {
                                                    $logMessage = "Failed to insert to APILogs.";
                                                    $logger->log($logger->logdate, "[REGISTERMEMBERBT ERROR]: MID " . $MID . " || " . $mobileNumber . " || ", $logMessage);
                                                }
                                            } else {
                                                $coupons = $couponsModel->getCoupon();

                                                $couponNumber = $coupons['CouponCode'];
                                                $expiryDate = $coupons['ValidToDate'];
                                                $expiryDate = date("Y-m-d", strtotime($expiryDate));
                                                $mobileno = str_replace("09", "639", $memberInfos["MobileNumber"]);
                                                $templateid1 = $ref_SMSApiMethodsModel->getSMSMethodTemplateID(Ref_SMSApiMethodsModel::PLAYER_REGISTRATION_1OF2);
                                                $templateid2 = $ref_SMSApiMethodsModel->getSMSMethodTemplateID(Ref_SMSApiMethodsModel::PLAYER_REGISTRATION_2OF2);

                                                $methodid1 = Ref_SMSApiMethodsModel::PLAYER_REGISTRATION_1OF2;
                                                $methodid2 = Ref_SMSApiMethodsModel::PLAYER_REGISTRATION_2OF2;

                                                $smslastinsertedid1 = $smsRequestLogsModel->insertSMSRequestLogs($methodid1, $mobileno, $memberInfos["DateCreated"]);
                                                $smslastinsertedid2 = $smsRequestLogsModel->insertSMSRequestLogs($methodid2, $mobileno, $memberInfos["DateCreated"]);
                                                if ($coupons) {
                                                    $templateidbt = $ref_SMSApiMethodsModel->getSMSMethodTemplateID(Ref_SMSApiMethodsModel::PLAYER_REGISTRATION_BT);
                                                    $templateidbt = $templateidbt['SMSTemplateID'];
                                                    $methodidbt = Ref_SMSApiMethodsModel::PLAYER_REGISTRATION_BT;
                                                    $smslastinsertedidbt = $smsRequestLogsModel->insertSMSRequestLogs($methodidbt, $mobileno, $memberInfos["DateCreated"]);
                                                } else {
                                                    $smslastinsertedidbt = 0;
                                                }
                                                if (($smslastinsertedid1 != 0 && $smslastinsertedid1 != '') && ($smslastinsertedidbt != 0 && $smslastinsertedidbt != '') && ($smslastinsertedid2 != 0 && $smslastinsertedid2 != '')) {
                                                    $trackingid1 = "SMSR" . $smslastinsertedid1;
                                                    $trackingid2 = "SMSR" . $smslastinsertedid2;
                                                    $trackingidbt = "SMSR" . $smslastinsertedidbt;
                                                    $apiURL = Yii::app()->params['SMSURI'];
                                                    $app_id = Yii::app()->params['app_id'];
                                                    $membershipSMSApi = new MembershipSmsAPI($apiURL, $app_id);
                                                    $smsresult1 = $membershipSMSApi->sendRegistration1($mobileno, $templateid1['SMSTemplateID'], $memberInfos["DateCreated"], $memberInfos["TemporaryAccountCode"], $trackingid1);
                                                    $smsresult2 = $membershipSMSApi->sendRegistration2($mobileno, $templateid2['SMSTemplateID'], $memberInfos["DateCreated"], $memberInfos["TemporaryAccountCode"], $trackingid2);
                                                    $smsresult3 = $membershipSMSApi->sendRegistrationBT($mobileno, $templateidbt['SMSTemplateID'], $expiryDate, $couponNumber, $trackingidbt);

                                                    if (isset($smsresult1['status']) && isset($smsresult2['status']) && isset($smsresult3['status'])) {
                                                        if ($smsresult1['status'] != 1 && $smsresult2['status'] != 1 && $smsresult3['status'] != 1) {
                                                            $transMsg = 'Failed to get response from membershipsms api.';
                                                            $errorCode = 90;
                                                            Utilities::log("ReturnMessage: " . $transMsg . " ErrorCode: " . $errorCode);
                                                            $data = CommonController::retMsgRegisterMemberBT($module, '', '', $errorCode, $transMsg);
                                                            $message = "[" . $module . "] Output: " . CJSON::encode($data);
                                                            $appLogger->log($appLogger->logdate, "[response]", $message);
                                                            $this->_sendResponse(200, CJSON::encode($data));
                                                            $logMessage = 'Failed to get response from membershipsms api.';
                                                            $logger->log($logger->logdate, "[REGISTERMEMBERBT ERROR]: MID " . $MID . " || " . $mobileNumber . " || ", $logMessage);
                                                            $apiDetails = 'REGISTERMEMBERBT-Failed: Failed to get response from membershipsms api. MID = ' . $lastInsertedMID;
                                                            $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, $refID, $apiDetails, '', 2);
                                                            if ($isInserted == 0) {
                                                                $logMessage = "Failed to insert to APILogs.";
                                                                $logger->log($logger->logdate, "[REGISTERMEMBERBT ERROR]: MID " . $MID . " || " . $mobileNumber . " || ", $logMessage);
                                                            }
                                                        } else {
                                                            $isUpdated = $couponsModel->updateCouponStatus($couponNumber, $MID);
                                                            if (!$isUpdated) {
                                                                $logMessage = "Failed to update coupon status.";
                                                                $logger->log($logger->logdate, "[REGISTERMEMBERBT ERROR]: MID " . $MID . " || " . $mobileNumber . " || ", $logMessage);
                                                                exit;
                                                            }
                                                            $helpers = new Helpers();
                                                            $helpers->sendEmailBT($emailAddress, $firstname . ' ' . $lastname, $couponNumber, $expiryDate);
                                                            $transMsg = "You have successfully registered! An active Temporary Account will be sent to your email address or mobile number, which can be used to start session or credit points in the absence of Membership Card. Please note that your Registered Account and Temporary Account will be activated only after 24 hours.";
                                                            $errorCode = 0;
                                                            Utilities::log("ReturnMessage: " . $transMsg . " ErrorCode: " . $errorCode);
                                                            $data = CommonController::retMsgRegisterMemberBT($module, $errorCode, $transMsg);
                                                            $message = "[" . $module . "] Output: " . CJSON::encode($data);
                                                            $appLogger->log($appLogger->logdate, "[response]", $message);
                                                            $this->_sendResponse(200, CJSON::encode($data));
                                                            $logMessage = 'Registration is successful.';
                                                            $logger->log($logger->logdate, "[REGISTERMEMBERBT SUCCESSFUL]: MID " . $MID . " || " . $mobileNumber . " || ", $logMessage);
                                                            $apiDetails = 'REGISTERMEMBERBT-Success: Registration is successful. MID = ' . $lastInsertedMID;
                                                            $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, $refID, $apiDetails, '', 1);
                                                            if ($isInserted == 0) {
                                                                $logMessage = "Failed to insert to APILogs.";
                                                                $logger->log($logger->logdate, "[REGISTERMEMBERBT ERROR]: MID " . $MID . " || " . $mobileNumber . " || ", $logMessage);
                                                            }
                                                        }
                                                    } else {
                                                        $transMsg = 'Failed to get response from membershipsms api.';
                                                        $errorCode = 90;
                                                        Utilities::log("ReturnMessage: " . $transMsg . " ErrorCode: " . $errorCode);
                                                        $data = CommonController::retMsgRegisterMemberBT($module, '', '', $errorCode, $transMsg);
                                                        $message = "[" . $module . "] Output: " . CJSON::encode($data);
                                                        $appLogger->log($appLogger->logdate, "[response]", $message);
                                                        $this->_sendResponse(200, CJSON::encode($data));
                                                        $logMessage = 'Failed to get response from membershipsms api.';
                                                        $logger->log($logger->logdate, "[REGISTERMEMBERBT ERROR]: MID " . $MID . " || " . $mobileNumber . " || ", $logMessage);
                                                        $apiDetails = 'REGISTERMEMBERBT-Failed: Failed to get response from membershipsms api. MID = ' . $lastInsertedMID;
                                                        $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, $refID, $apiDetails, '', 2);
                                                        if ($isInserted == 0) {
                                                            $logMessage = "Failed to insert to APILogs.";
                                                            $logger->log($logger->logdate, "[REGISTERMEMBERBT ERROR]: MID " . $MID . " || " . $mobileNumber . " || ", $logMessage);
                                                        }
                                                    }
                                                } else {
                                                    $message = "Failed to send SMS: Error on logging event in database.";
                                                    $logger->log($logger->logdate, "[REGISTERMEMBERBT ERROR]: MID " . $MID . " || " . $mobileNumber . " || ", $message);
                                                    $apiDetails = "REGISTERMEMBERBT-Failed: Failed to send SMS. Error on logging event in database [MID = $lastInsertedMID].";
                                                    $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, $refID, $apiDetails, '', 2);
                                                    if ($isInserted == 0) {
                                                        $logMessage = "Failed to insert to APILogs.";
                                                        $logger->log($logger->logdate, "[REGISTERMEMBERBT ERROR]: MID " . $MID . " || " . $mobileNumber . " || ", $logMessage);
                                                    }
                                                    $errorCode = 88;
                                                    Utilities::log("ReturnMessage: " . $message . " ErrorCode: " . $errorCode);
                                                    $data = CommonController::retMsgRegisterMemberBT($module, '', '', $errorCode, $transMsg);
                                                    $message = "[" . $module . "] Output: " . CJSON::encode($data);
                                                    $appLogger->log($appLogger->logdate, "[response]", $message);
                                                    $this->_sendResponse(200, CJSON::encode($data));
                                                }
                                            }
                                        } else {
                                            $message = "Failed to send SMS: Invalid Mobile Number.";
                                            $logger->log($logger->logdate, "[REGISTERMEMBERBT ERROR]: MID " . $MID . " || " . $mobileNumber . " || ", $message);
                                            $apiDetails = "REGISTERMEMBERBT-Failed: Failed to send SMS. Invalid Mobile Number [MID = $lastInsertedMID].";
                                            $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, $refID, $apiDetails, '', 2);
                                            if ($isInserted == 0) {
                                                $logMessage = "Failed to insert to APILogs.";
                                                $logger->log($logger->logdate, "[REGISTERMEMBERBT ERROR]: MID " . $MID . " || " . $mobileNumber . " || ", $logMessage);
                                            }
                                        }
                                    }

                                    $auditTrailModel->logEvent(AuditTrailModel::API_REGISTER_MEMBER_BT, 'Email: ' . $emailAddress, array('ID' => $ID));
                                } else {
                                    if (strpos($lastInsertedMID['MID'], " Integrity constraint violation: 1062 Duplicate entry") > 0) {
                                        $transMsg = "Sorry, " . $emailAddress . "already belongs to an existing account. Please enter another email address.";
                                        $errorCode = 21;
                                        Utilities::log("ReturnMessage: " . $transMsg . " ErrorCode: " . $errorCode);
                                        $data = CommonController::retMsgRegisterMemberBT($module, '', '', $errorCode, $transMsg);
                                        $message = "[" . $module . "] Output: " . CJSON::encode($data);
                                        $appLogger->log($appLogger->logdate, "[response]", $message);
                                        $this->_sendResponse(200, CJSON::encode($data));
                                        $logMessage = "Sorry, " . $emailAddress . "already belongs to an existing account. Please enter another email address.";
                                        $logger->log($logger->logdate, "[REGISTERMEMBERBT ERROR]: MID " . $MID . " || " . $mobileNumber . " || ", $logMessage);
                                        $apiDetails = 'REGISTERMEMBERBT-Failed: Email already exists. Please choose a different email address. Email = ' . $emailAddress;
                                        $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, $refID, $apiDetails, '', 2);
                                        if ($isInserted == 0) {
                                            $logMessage = "Failed to insert to APILogs.";
                                            $logger->log($logger->logdate, "[REGISTERMEMBERBT ERROR]: MID " . $MID . " || " . $mobileNumber . " || ", $logMessage);
                                        }

                                        exit;
                                    } else {
                                        $transMsg = "Registration failed.";
                                        $errorCode = 53;
                                        Utilities::log("ReturnMessage: " . $transMsg . " ErrorCode: " . $errorCode);
                                        $data = CommonController::retMsgRegisterMemberBT($module, '', '', $errorCode, $transMsg);
                                        $message = "[" . $module . "] Output: " . CJSON::encode($data);
                                        $appLogger->log($appLogger->logdate, "[response]", $message);
                                        $this->_sendResponse(200, CJSON::encode($data));
                                        $logMessage = "Registration failed.";
                                        $logger->log($logger->logdate, "[REGISTERMEMBERBT ERROR]: MID " . $MID . " || " . $mobileNumber . " || ", $logMessage);
                                        $apiDetails = 'REGISTERMEMBERBT-Failed: Registration failed.';
                                        $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, $refID, $apiDetails, '', 2);
                                        if ($isInserted == 0) {
                                            $logMessage = "Failed to insert to APILogs.";
                                            $logger->log($logger->logdate, "[REGISTERMEMBERBT ERROR]: MID " . $MID . " || " . $mobileNumber . " || ", $logMessage);
                                        }

                                        exit;
                                    }
                                }
                            }
                        }
                    }
                }
            }
        } else {
            $transMsg = "One or more fields is not set or is blank.";
            $errorCode = 1;
            Utilities::log("ReturnMessage: " . $transMsg . " ErrorCode: " . $errorCode);
            $data = CommonController::retMsgRegisterMemberBT($module, '', '', $errorCode, $transMsg);
            $message = "[" . $module . "] Output: " . CJSON::encode($data);
            $appLogger->log($appLogger->logdate, "[response]", $message);
            $this->_sendResponse(200, CJSON::encode($data));
            $logMessage = 'One or more fields is not set or is blank.';
            $logger->log($logger->logdate, "[REGISTERMEMBERBT ERROR]: " . $request['FirstName'] . " || " . $request['LastName'] . " || " . $request['MobileNo'] . " || " . $request['EmailAddress'] . " || " . $request['Birthdate'] . " || ", $logMessage);
            $apiDetails = 'REGISTERMEMBERBT-Failed: Invalid input parameters.';
            $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, '', $apiDetails, '', 2);
            if ($isInserted == 0) {
                $logMessage = "Failed to insert to APILogs.";
                $logger->log($logger->logdate, "[REGISTERMEMBERBT ERROR]: " . $request['FirstName'] . " || " . $request['LastName'] . " || " . $request['MobileNo'] . " || " . $request['EmailAddress'] . " || " . $request['Birthdate'] . " || ", $logMessage);
            }

            exit;
        }
    }

    //@date 10-08-2014
    //purpose checking of member session
    private function _getActiveMemberSession($mpSessionID) {
        $memberSessionsModel = new MemberSessionsModel();
        $logger = new ErrorLogger();
        $valid = false;

        $isActiveSession = $memberSessionsModel->getActiveSession($mpSessionID);
        if (isset($isActiveSession['Count']) && $isActiveSession['Count'] != 0) {
            $resultMPSessionID = $isActiveSession['SessionID'];
            $resultMID = $isActiveSession['MID'];
            $memberSessionID = $isActiveSession['MemberSessionID'];
            $sessionDateTime = strtotime($isActiveSession['TransactionDate']);
            $currentDateTime = strtotime(date('Y-m-d H:i:s'));
            $timeInterval = round(abs($currentDateTime - $sessionDateTime) / 60, 2);
            $maxTime = Yii::app()->params["SessionTimeOut"];

            if ($timeInterval < $maxTime) {
                $logMessage = 'GetActiveMemberSession is successful.';
                $logger->log($logger->logdate, "[GETACTIVEMEMBERSESSION SUCCESSFUL]: " . $resultMID . " || ", $logMessage);
                $valid = true;
            } else {
                $logMessage = 'Session is expired. Please login again.';
                $logger->log($logger->logdate, "[GETACTIVEMEMBERSESSION ERROR]: " . $resultMID . " || ", $logMessage);
                $valid = false;
            }
        } else {
            $logMessage = 'No active session for this member.';
            $logger->log($logger->logdate, "[GETACTIVEMEMBERSESSION ERROR]: " . $isActiveSession['MID'] . " || ", $logMessage);
            $valid = false;
        }
        return $valid;
    }

    //@purpose used to validate mp session
    private function _validateMPSession($mpSessionID) {
        date_default_timezone_set('Asia/Manila');
        $valid = false;

        $memberSessionsModel = new MemberSessionsModel();
        $logger = new ErrorLogger();
        $queryResult = $memberSessionsModel->validateMPSessionID(trim($mpSessionID));

        $count = $queryResult['Count'];
        $MID = $queryResult['MID'];

        if (isset($count) && $count == 1) {
            $sessionDateTime = strtotime($queryResult['TransactionDate']);
            $currentDateTime = strtotime(date('Y-m-d H:i:s'));
            $timeInterval = round(abs($currentDateTime - $sessionDateTime) / 60, 2);
            $maxTime = Yii::app()->params["SessionTimeOut"];

            if ($timeInterval < $maxTime) {
                $logMessage = 'Validate MPSession is successful.';
                $logger->log($logger->logdate, "[VALIDATEMPSESSION SUCCESSFUL]: MID " . $MID . " || ", $logMessage);
                $valid = true;
            } else {
                $logMessage = 'MPSessionID is already expired. Please login again.';
                $logger->log($logger->logdate, "[VALIDATEMPSESSION ERROR]: MID " . $MID . " || ", $logMessage);
                $valid = false;
            }
        } else {
            $logMessage = 'Invalid MPSessionID.';
            $logger->log($logger->logdate, "[VALIDATEMPSESSION ERROR]: MID " . $MID . " || ", $logMessage);
            $valid = false;
        }
        return $valid;
    }

    private function _validateDate($date) {
        $d = DateTime::createFromFormat('Y-m-d', $date);
        return $d && $d->format('Y-m-d') == $date;
    }
    //@date added 05-07-2015
    public function actionGetBalance() {
        $request = $this->_readJsonRequest();

        $mpSessionID = trim($request['MPSessionID']);
        $cardNumber = trim($request['CardNumber']);
        $transMsg = '';
        $errorCode = '';
        $module = 'GetBalance';
        $apiMethod = 21;

        $appLogger = new AppLogger();

        $paramval = CJSON::encode($request);
        $message = "[" . $module . "] Input: " . $paramval;
        $appLogger->log($appLogger->logdate, "[request]", $message);

        $logger = new ErrorLogger();
        $apiLogsModel = new APILogsModel();
        $memberSessionsModel = new MemberSessionsModel();

        $result = $memberSessionsModel->getMID($mpSessionID);
        $MID = $result['MID'];

        $isValid = $this->_validateMPSession($mpSessionID);
        if (isset($isValid) && !$isValid) {
            $transMsg = "MPSessionID is already expired. Please login again.";
            $errorCode = 91;
            Utilities::log("ReturnMessage: " . $transMsg . " ErrorCode: " . $errorCode);
            $data = CommonController::retMsgGetBalance($module, '', '', '', '', $errorCode, $transMsg);
            $message = "[" . $module . "] Output: " . CJSON::encode($data);
            $appLogger->log($appLogger->logdate, "[response]", $message);
            $this->_sendResponse(200, CJSON::encode($data));
            $logMessage = 'MPSessionID is already expired. Please login again.';
            $logger->log($logger->logdate, "[GETBALANCE ERROR]: MID " . $MID . " || ", $logMessage);
            $apiDetails = 'GETBALANCE-Failed: MPSessionID is already expired. Please login again.. MID = ' . $MID;
            $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, '', $apiDetails, '', 2);
            if ($isInserted == 0) {
                $logMessage = "Failed to insert to APILogs.";
                $logger->log($logger->logdate, "[GETBALANCE ERROR]: MID " . $MID . " || ", $logMessage);
            }

            exit;
        }

        if (isset($result)) {
            $isUpdated = $memberSessionsModel->updateTransactionDate($MID);
            if ($isUpdated == 0) {
                $logMessage = 'Failed to update transaction date in membersessions table WHERE MID = ' . $MID . ' AND SessionID = ' . $mpSessionID;
                $transMsg = 'Transaction failed.';
                $errorCode = 4;
                Utilities::log("ReturnMessage: " . $transMsg . " ErrorCode: " . $errorCode);
                $data = CommonController::retMsgGetBalance($module, '', '', '', '', $errorCode, $transMsg);
                $message = "[" . $module . "] Output: " . CJSON::encode($data);
                $appLogger->log($appLogger->logdate, "[response]", $message);
                $this->_sendResponse(200, CJSON::encode($data));
                $logger->log($logger->logdate, "[GETBALANCE ERROR]: MID " . $MID . " || ", $logMessage);
                $apiDetails = 'GETBALANCE-UpdateTransDate-Failed: ' . 'MID = ' . $MID . ' SessionID = ' . $mpSessionID;
                $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, '', $apiDetails, '', 2);
                if ($isInserted == 0) {
                    $logMessage = "Failed to insert to APILogs.";
                    $logger->log($logger->logdate, "[GETBALANCE ERROR]: MID " . $MID . " || ", $logMessage);
                }

                exit;
            }
        }

        //check if mpsessionid and cardnumber is inputted
        if (isset($mpSessionID) && isset($cardNumber)) {
            if (($mpSessionID == '') || ($cardNumber == '')) {
                $logMessage = 'One or more fields is not set or is blank';
                $transMsg = "One or more fields is not set or is blank.";
                $errorCode = 1;
                Utilities::log("ReturnMessage: " . $transMsg . " ErrorCode: " . $errorCode);
                $this->_sendResponse(200, CJSON::encode(CommonController::retMsgGetBalance($module, '', '', '', '', $errorCode, $transMsg)));
                $logger->log($logger->logdate, " [GET BALANCE ERROR] ", $logMessage);
                $apiDetails = 'GETBALANCE-Failed: Invalid login parameters.';
                $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, '', $apiDetails, '', 2);
                if ($isInserted == 0) {
                    $logMessage = "Failed to insert to APILogs.";
                    $logger->log($logger->logdate, " [GET BALANCE ERROR] ", $logMessage);
                }

                exit;
            } else {
                $memberCardsModel = new MemberCardsModel();
                $auditTrailModel = new AuditTrailModel();
                $pcwsWrapper = new PcwsWrapper();

                $result = $pcwsWrapper->getBalance($cardNumber, 1);
                if ($result) {
                    $playableBalance = number_format($result['GetBalance']['PlayableBalance'], 2, '.', ',');
                    $bonusBalance = number_format($result['GetBalance']['BonusBalance'], 2, '.', ',');
                    $playthroughBalance = number_format($result['GetBalance']['PlayThroughBalance'], 2, '.', ',');
                    $withdrawableBalance = number_format($result['GetBalance']['WithdrawableBalance'], 2, '.', ',');

                    $memberDetails = $memberCardsModel->getMemberDetailsByCard($cardNumber);

                    if (!empty($memberDetails))
                        $MID = $memberDetails['MID'];
                    else
                        $MID = 0;

                    if (!empty($memberDetails)) {
                        $status = $memberDetails['Status'];

                        switch ($status) {
                            case 0: $message = 'Card is Inactive';
                                break;
                            case 1: $message = 'No error, Transaction successful.';
                                break;
                            case 5: $message = 'No error, Transaction successful.';
                                break;
                            case 2: $message = 'Card is Deactivated';
                                break;
                            case 7: $message = 'Card is Newly Migrated.';
                                break;
                            case 8: $message = 'Card is Temporarily Migrated';
                                break;
                            case 9: $message = 'Card is Banned';
                                break;
                            default: $message = 'Card is Invalid';
                                break;
                        }

                        $transMsg = $message;
                        if ($status == 1 || $status == 5)
                            $errorCode = 0;
                        else if ($status == 0)
                            $errorCode = 6;
                        else if ($status == 2)
                            $errorCode = 11;
                        else if ($status == 7)
                            $errorCode = 7;
                        else if ($status == 8)
                            $errorCode = 8;
                        else if ($status == 9)
                            $errorCode = 9;
                        else
                            $errorCode = 10;

                        $isSuccessful = $auditTrailModel->logEvent(AuditTrailModel::API_GET_BALANCE, 'CardNumber: ' . $cardNumber, array('MID' => $MID, 'SessionID' => $mpSessionID));
                        if ($isSuccessful == 0) {
                            $logMessage = "Failed to insert to Audittrail.";
                            $logger->log($logger->logdate, "[GETBALANCE ERROR]: " . $cardNumber . " || ", $logMessage);
                        }

                        $data = CommonController::retMsgGetBalance($module, $withdrawableBalance, $playableBalance, $bonusBalance, $playthroughBalance, $errorCode, $transMsg);
                        $message = "[" . $module . "] Output: " . CJSON::encode($data);
                        $appLogger->log($appLogger->logdate, "[response]", $message);
                        $this->_sendResponse(200, CJSON::encode($data));

                        $logMessage = 'Get Balance is successful.';
                        $logger->log($logger->logdate, "[GETBALANCE SUCCESSFUL]: " . $cardNumber . " || ", $logMessage);
                        $apiDetails = 'GETBALANCE-Successful: MID = ' . $MID;
                        $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, '', $apiDetails, '', 1);
                        if ($isInserted == 0) {
                            $logMessage = "Failed to insert to APILogs.";
                            $logger->log($logger->logdate, "[GETBALANCE ERROR]: " . $cardNumber . " || ", $logMessage);
                        }

                        exit;
                    } else {
                        $transMsg = "Card is Invalid.";
                        $errorCode = 10;
                        Utilities::log("ReturnMessage: " . $transMsg . " ErrorCode: " . $errorCode);
                        $data = CommonController::retMsgGetBalance($module, '', '', '', '', $errorCode, $transMsg);
                        $message = "[" . $module . "] Output: " . CJSON::encode($data);
                        $appLogger->log($appLogger->logdate, "[response]", $message);
                        $this->_sendResponse(200, CJSON::encode($data));
                        $logMessage = 'Card is Invalid.';
                        $logger->log($logger->logdate, "[GETBALANCE ERROR]: " . $cardNumber . " || ", $logMessage);
                        $apiDetails = 'GETBALANCE-Failed: Membership card is invalid. Card Number = ' . $cardNumber;
                        $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, '', $apiDetails, '', 2);
                        if ($isInserted == 0) {
                            $logMessage = "Failed to insert to APILogs.";
                            $logger->log($logger->logdate, "[GETBALANCE ERROR]: " . $cardNumber . " || ", $logMessage);
                        }

                        exit;
                    }
                } else {
                    $transMsg = "Cannot access PCWS API.";
                    $errorCode = 120;
                    Utilities::log("ReturnMessage: " . $transMsg . " ErrorCode: " . $errorCode);
                    $data = CommonController::retMsgCheckPoints($module, '', '', $errorCode, $transMsg);
                    $message = "[" . $module . "] Output: " . CJSON::encode($data);
                    $appLogger->log($appLogger->logdate, "[response]", $message);
                    $this->_sendResponse(200, CJSON::encode($data));
                    $logMessage = 'Cannot access PCWS API.';
                    $logger->log($logger->logdate, "[CHECKPOINTS ERROR]: " . $cardNumber . " || ", $logMessage);
                    $apiDetails = 'CHECKPOINTS-Failed: Cannot access PCWS API. Card Number = ' . $cardNumber;
                    $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, '', $apiDetails, '', 2);
                    if ($isInserted == 0) {
                        $logMessage = "Failed to insert to APILogs.";
                        $logger->log($logger->logdate, "[CHECKPOINTS ERROR]: " . $cardNumber . " || ", $logMessage);
                    }

                    exit;
                }
            }
        } else {
            $logMessage = 'One or more fields is not set or is blank';
            $transMsg = "One or more fields is not set or is blank.";
            $errorCode = 1;
            Utilities::log("ReturnMessage: " . $transMsg . " ErrorCode: " . $errorCode);
            $this->_sendResponse(200, CJSON::encode(CommonController::retMsgGetBalance($module, '', '', '', '', $errorCode, $transMsg)));
            $logger->log($logger->logdate, " [GET BALANCE ERROR] ", $logMessage);
            $apiDetails = 'GETBALANCE-Failed: Invalid login parameters.';
            $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, '', $apiDetails, '', 2);
            if ($isInserted == 0) {
                $logMessage = "Failed to insert to APILogs.";
                $logger->log($logger->logdate, " [GET BALANCE ERROR] ", $logMessage);
            }

            exit;
        }
    }

    private function SubmitData($uri, $postdata) {
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
