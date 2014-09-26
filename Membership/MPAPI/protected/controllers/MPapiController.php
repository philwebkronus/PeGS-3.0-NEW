<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Controller for Membership Portal API
 * @date 6-13-2014
 * @author fdlsison
 */

class MPapiController extends Controller {
    
    //@date 07-10-2014
    //@purpose Login authentication
    private function _authenticate($username, $password) {
        $module = 'Login';
        $apiMethod = 1;
        
        $membersModel = new MembersModel();
        $memberCardsModel = new MemberCardsModel();
        $membershipTempModel = new MembershipTempModel();
        $logger = new ErrorLogger();
        $apiLogsModel = new APILogsModel();
        
        //check if username is in members and is already verified
        if(Utilities::validateEmail($username)) {
            $result = $membersModel->getMembersDetails($username);
            $refID = $username;
        }
        else {
            $cardInfo = $memberCardsModel->getMIDUsingCard($username);
            if($cardInfo > 0) {
                if($cardInfo['Status'] == 1 || $cardInfo['Status'] == 5) {
                    $MID = $cardInfo['MID'];
                    $result = $membersModel->getMemberDetailsByMID($MID);
                    
                }
                else if($cardInfo['Status'] == 9) {
                    $result = "Card is banned.";
                }
                else {
                    $result = 0;
                }
            }
            else {
                $result = array();
            }
            $refID = $username;
        }
        

        $retVal = '';
        $strPass = md5($password);
        

        if(is_array($result) && count($result) > 0) {
            $mid = $result['MID'];
            

            switch($result['Status']) {
                case 1:
                    if($result['Password'] != $strPass) {
                        
                        $logMessage = 'Password inputted is incorrect.';
                        
                        $logger->log($logger->logdate, " [LOGIN ERROR] ", $logMessage);
                        $apiDetails = 'LOGIN-Authenticate-Failed: Incorrect password.';
                        $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, $refID, $apiDetails, '', 2);
                        if($isInserted == 0) {
                            $logMessage = "Failed to insert to APILogs.";
                            $logger->log($logger->logdate, " [LOGIN ERROR] ", $logMessage);
                        }
                        $retVal = false;
                        
                    }
                    else
                        $retVal = $result;
                    break;
                case 0:
                    $logMessage = 'Account is Inactive';

                    $logger->log($logger->logdate, " [LOGIN ERROR] ", $logMessage);
                    $apiDetails = 'LOGIN-Authenticate-Failed: Member account is inactive.';
                    $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, $refID, $apiDetails, '', 2);
                    if($isInserted == 0) {
                        $logMessage = "Failed to insert to APILogs.";
                        $logger->log($logger->logdate, " [LOGIN ERROR] ", $logMessage);
                    }
                    $retVal = false;
                    break;
                case 2:
                    $logMessage = 'Account is Suspended.';
                    
                    $logger->log($logger->logdate, " [LOGIN ERROR] ", $logMessage);
                    $apiDetails = 'LOGIN-Authenticate-Failed: Member account is suspended.';
                    $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, $refID, $apiDetails, '', 2);
                    if($isInserted == 0) {
                        $logMessage = "Failed to insert to APILogs.";
                        $logger->log($logger->logdate, " [LOGIN ERROR] ", $logMessage);
                    }
                    $retVal = false;
                    break;
                case 3:
                    $logMessage = 'Account is Locked (Login Attempts).';

                    $logger->log($logger->logdate, " [LOGIN ERROR] ", $logMessage);
                    $apiDetails = 'LOGIN-Authenticate-Failed: Member account is locked (login attempts).';
                    $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, $refID, $apiDetails, '', 2);
                    if($isInserted == 0) {
                        $logMessage = "Failed to insert to APILogs.";
                        $logger->log($logger->logdate, " [LOGIN ERROR] ", $logMessage);
                    }
                    $retVal = false;
                    break;
                case 4:
                    $logMessage = 'Account is Locked (By Admin).';

                    $logger->log($logger->logdate, " [LOGIN ERROR] ", $logMessage);
                    $apiDetails = 'LOGIN-Authenticate-Failed: Member account is locked (admin).';
                    $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, $refID, $apiDetails, '', 2);
                    if($isInserted == 0) {
                        $logMessage = "Failed to insert to APILogs.";
                        $logger->log($logger->logdate, " [LOGIN ERROR] ", $logMessage);
                    }
                    $retVal = false;
                    break;
                case 5:
                    $logMessage = 'Account is Locked (By Admin).';

                    $logger->log($logger->logdate, " [LOGIN ERROR] ", $logMessage);
                    $apiDetails = 'LOGIN-Authenticate-Failed: Member account is locked (admin).';
                    $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, $refID, $apiDetails, '', 2);
                    if($isInserted == 0) {
                        $logMessage = "Failed to insert to APILogs.";
                        $logger->log($logger->logdate, " [LOGIN ERROR] ", $logMessage);
                    }
                    $retVal = false;
                    break;
                case 6:
                    $logMessage = 'Account is Terminated.';

                    $logger->log($logger->logdate, " [LOGIN ERROR] ", $logMessage);
                    $apiDetails = 'LOGIN-Authenticate-Failed: Member account is terminated.';
                    $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, $refID, $apiDetails, '', 2);
                    if($isInserted == 0) {
                        $logMessage = "Failed to insert to APILogs.";
                        $logger->log($logger->logdate, " [LOGIN ERROR] ", $logMessage);
                    }
                    $retVal = false;
                    break;
                default:
                    $logMessage = 'Account is Invalid.';

                    $logger->log($logger->logdate, " [LOGIN ERROR] ", $logMessage);
                    $apiDetails = 'LOGIN-Authenticate-Failed: Member account is invalid.';
                    $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, $refID, $apiDetails, '', 2);
                    if($isInserted == 0) {
                        $logMessage = "Failed to insert to APILogs.";
                        $logger->log($logger->logdate, " [LOGIN ERROR] ", $logMessage);
                    }
                    $retVal = false;
                    break;
            }

        }
        else if(is_string($result)) {
            $transMsg = "Card is Banned.";
            $errorCode = 9;
            Utilities::log("ReturnMessage: " . $transMsg . " ErrorCode: " . $errorCode);
            $this->_sendResponse(200, CJSON::encode(CommonController::retMsgLogin($module, '', '', '', $errorCode, $transMsg)));
            $logMessage = 'Card is Banned.';
            $logger->log($logger->logdate, " [LOGIN ERROR] ", $logMessage);
            $apiDetails = 'LOGIN-Authenticate-Failed: Member card is banned.';
            $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, $refID, $apiDetails, '', 2);
            if($isInserted == 0) {
                $logMessage = "Failed to insert to APILogs.";
                $logger->log($logger->logdate, " [LOGIN ERROR] ", $logMessage);
            }
            $retVal = false;
            exit;
        }
        else if($result == 0) {
            $transMsg = "Account is Invalid.";
            $errorCode = 38;
            Utilities::log("ReturnMessage: " . $transMsg . " ErrorCode: " . $errorCode);
            $this->_sendResponse(200, CJSON::encode(CommonController::retMsgLogin($module, '', '', '', $errorCode, $transMsg)));
            $logMessage = 'Account is Invalid.';
            $logger->log($logger->logdate, " [LOGIN ERROR] ", $logMessage);
            $apiDetails = 'LOGIN-Authenticate-Failed: Member account is invalid.';
            $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, $refID, $apiDetails, '', 2);
            if($isInserted == 0) {
                $logMessage = "Failed to insert to APILogs.";
                $logger->log($logger->logdate, " [LOGIN ERROR] ", $logMessage);
            }
            $retVal = false;
            exit;
        }
        else {

            $isTempAcctExist = $membershipTempModel->checkTempUser($username);

            //check if account has no transactions yet in kronus cashier
            if($isTempAcctExist > 0) {
                $transMsg = "You need to transact at least one transaction before you can login.";
                $errorCode = 39;
                Utilities::log("ReturnMessage: " . $transMsg . " ErrorCode: " . $errorCode);
                $this->_sendResponse(200, CJSON::encode(CommonController::retMsgLogin($module, '', '', '', $errorCode, $transMsg)));
                $logMessage = 'You need to transact at least one transaction before you can login.';
                $logger->log($logger->logdate, " [LOGIN ERROR] ", $logMessage);
                $apiDetails = 'LOGIN-Authenticate-Failed: You need to transact at least one transaction before you can login.';
                $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, $refID, $apiDetails, '', 2);
                if($isInserted == 0) {
                    $logMessage = "Failed to insert to APILogs.";
                    $logger->log($logger->logdate, " [LOGIN ERROR] ", $logMessage);
                }
                $retVal = false;
                exit;
            }
            else {
                $transMsg = "Account is Invalid.";
                $errorCode = 38;
                Utilities::log("ReturnMessage: " . $transMsg . " ErrorCode: " . $errorCode);
                $this->_sendResponse(200, CJSON::encode(CommonController::retMsgLogin($module, '', '', '', $errorCode, $transMsg)));
                $logMessage = 'Account is Invalid.';
                $logger->log($logger->logdate, " [LOGIN ERROR] ", $logMessage);
                $apiDetails = 'LOGIN-Authenticate-Failed: Member account is invalid.';
                $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, $refID, $apiDetails, '', 2);
                if($isInserted == 0) {
                    $logMessage = "Failed to insert to APILogs.";
                    $logger->log($logger->logdate, " [LOGIN ERROR] ", $logMessage);
                }
                $retVal = false;
                exit;
            }
        }
     
        return $retVal;
    }
    
//    private function _getDate() {
//        $ATZ = new DateTimeZone('Asia/Taipei');
//        $dateNow = new DateTime(date('Y-m-d H:i:s'));
//        $dateNow->setTimezone($ATZ);
//        $dateNow = $dateNow->format('[Y-m-d H:i:s]');
//        
//        return $dateNow;
//    }
    
    //@purpose serves as input for the username & password of the member to access the portal
    public function actionLogin() {
        $request = $this->_readJsonRequest();
        
        $transMsg = '';
        $errorCode = '';
        $module = 'Login';
        $apiMethod = 1;
        
        $logger = new ErrorLogger();
        $apiLogsModel = new APILogsModel();
        
        //check if username & password is inputted
        if(isset($request['Username']) && isset($request['Password'])) {
            if (($request['Username'] == '') || ($request['Password'] == '')) {
                $logMessage = "One or more fields is not set or is blank.";
                $transMsg = "One or more fields is not set or is blank.";
                $errorCode = 1;
                Utilities::log("ReturnMessage: " . $transMsg . " ErrorCode: " . $errorCode);
                $this->_sendResponse(200, CJSON::encode(CommonController::retMsgLogin($module, '', '', '', $errorCode, $transMsg)));
                $logger->log($logger->logdate, " [LOGIN ERROR] ", $logMessage);
                $apiDetails = 'LOGIN-Failed: Invalid Login parameters.';
                $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, '', $apiDetails, '', 2);
                if($isInserted == 0) {
                    $logMessage = "Failed to insert to APILogs.";
                    $logger->log($logger->logdate, " [LOGIN ERROR] ", $logMessage);
                }
                exit;
            }
            else {
     
                if (Utilities::validateEmail($request['Username']) || ctype_alnum($request['Password'])) {
                    
                    $username = trim($request['Username']);
                    $password = trim($request['Password']);
                    
                    //start of declaration of models to be used
                    
                    $memberSessionsModel = new MemberSessionsModel();
                    $memberCardsModel = new MemberCardsModel();
                    $cardsModel = new CardsModel();
                    $auditTrailModel = new AuditTrailModel();
                    
                    
                    $members = $this->_authenticate($username, $password);
                    
                    
                    if($members) {
                        $MID = $members['MID'];
                        
                        $isVIP = $members['IsVIP'];
                        $activeSession = $memberSessionsModel->checkSession($MID);
                        
                        $remoteIP = $_SERVER['REMOTE_ADDR'];
                        $session=new CHttpSession();
                        $session->open();
                        $mpSessionID = $session->getSessionID();
                        
                        $session->setSessionID($mpSessionID);
                        
                        if($activeSession['COUNT(MemberSessionID)'] > 0) {
                            $result = $memberSessionsModel->updateSession($mpSessionID, $MID, $remoteIP);
                        }
                        else {
                            
                            $result = $memberSessionsModel->insertMemberSession($MID, $mpSessionID, $remoteIP);
                        }
                        
                        if($result > 0) {                           
                           
                            $memberSessions = $memberSessionsModel->getMemberSessions($MID);
                            
                            
                            $mpSessionID = $memberSessions['SessionID'];
                            //$endDate = $memberSessions['DateEnded'];
                            
                            $memberCards = $memberCardsModel->getActiveMemberCardInfo($MID);
                            
                            
                            
                            $cardNumber = $memberCards['CardNumber'];
                            
                            $cards = $cardsModel->getCardInfo($cardNumber);
                            
                            $cardTypeID = $cards['CardTypeID'];
                            
                            $refID = $username;
                            
                            $isSuccessful = $auditTrailModel->logEvent(AuditTrailModel::API_LOGIN, 'Username: '.$username, array('MID' => $MID, 'SessionID' => $mpSessionID, 'AID' => $MID));
                            if($isSuccessful == 0) {
                                $logMessage = 'Failed to log event on Audittrail.';
                                $logger->log($logger->logdate, " [LOGIN FAILED] ", $logMessage);
                            }
                            $isUpdated = $memberSessionsModel->updateTransactionDate($MID, $mpSessionID);
                            if($isUpdated > 0) {
                                $transMsg = $mpSessionID;
                                $logMessage = 'Login successful.';
                                $errorCode = 0;
                                $this->_sendResponse(200, CJSON::encode(CommonController::retMsgLogin($module, $mpSessionID, $cardTypeID, $isVIP, $errorCode, $transMsg)));
                                $logger->log($logger->logdate, " [LOGIN SUCCESSFUL] ", $logMessage);
                                $apiDetails = 'LOGIN-UpdateTransDate-Success: MID = '.$MID.' SessionID = '.$mpSessionID;
                                $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, $refID, $apiDetails, '', 1);
                                if($isInserted == 0) {
                                    $logMessage = "Failed to insert to APILogs.";
                                    $logger->log($logger->logdate, " [LOGIN ERROR] ", $logMessage);
                                }
                                return $MID;
                            }
                            else {
                                $logMessage = 'Failed to update transaction date in membersessions table WHERE MID = '.$MID.' AND SessionID = '.$mpSessionID;
                                $transMsg = 'Transaction failed.';
                                $errorCode = 4;
                                Utilities::log("ReturnMessage: " . $transMsg . " ErrorCode: " . $errorCode);
                                $this->_sendResponse(200, CJSON::encode(CommonController::retMsgLogin($module, '', '', '', $errorCode, $transMsg)));
                                $logger->log($logger->logdate, " [LOGIN ERROR] ", $logMessage);
                                $apiDetails = 'LOGIN-UpdateTransDate-Failed: '.'Username: '.$username.' MID = '.$MID.' SessionID = '.$mpSessionID;
                                $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, $refID, $apiDetails, '', 2);
                                if($isInserted == 0) {
                                    $logMessage = "Failed to insert to APILogs.";
                                    $logger->log($logger->logdate, " [LOGIN ERROR] ", $logMessage);
                                }
                                exit;
                            } 
                            
                        }
                        else {
                            $logMessage = 'Failed to insert/update membersession in membersessions table WHERE MID = '.$MID.' AND SessionID = '.$mpSessionID.' AND RemoteIP = '.$remoteIP;
                            $transMsg = 'Transaction failed.';
                            $errorCode = 4;
                            Utilities::log("ReturnMessage: " . $transMsg . " ErrorCode: " . $errorCode);
                            $this->_sendResponse(200, CJSON::encode(CommonController::retMsgLogin($module, '', '', '', $errorCode, $transMsg)));
                            $logger->log($logger->logdate, " [LOGIN ERROR] ", $logMessage);
                            $apiDetails = 'LOGIN-Insert/UpdateMemberSession-Failed: MID = '.$MID.' SessionID = '.$mpSessionID;
                            $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, '', $apiDetails, '', 2);
                            if($isInserted == 0) {
                                $logMessage = "Failed to insert to APILogs.";
                                $logger->log($logger->logdate, " [LOGIN ERROR] ", $logMessage);
                            }
                            exit;
                        }
                    }
                    else {
                        $logMessage = 'Member is not found in db.';
                        $transMsg = 'Member not found';
                        $errorCode = 3;
                        Utilities::log("ReturnMessage: " . $transMsg . " ErrorCode: " . $errorCode);
                        $this->_sendResponse(200, CJSON::encode(CommonController::retMsgLogin($module, '', '', '', $errorCode, $transMsg)));
                        $logger->log($logger->logdate, " [LOGIN ERROR] ", $logMessage);
                        $apiDetails = 'LOGIN-Authenticate-Failed: Member account is invalid.';
                        $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, '', $apiDetails, '', 2);
                        if($isInserted == 0) {
                            $logMessage = "Failed to insert to APILogs.";
                            $logger->log($logger->logdate, " [LOGIN ERROR] ", $logMessage);
                        }
                        exit;
                    }

                }
                else {
                        $logMessage = 'Invalid input.';
                        $transMsg = 'Invalid input.';
                        $errorCode = 2;
                        Utilities::log("ReturnMessage: " . $transMsg . " ErrorCode: " . $errorCode);
                        $this->_sendResponse(200, CJSON::encode(CommonController::retMsgLogin($module, '', '', '', $errorCode, $transMsg)));
                        $logger->log($logger->logdate, " [LOGIN ERROR] ", $logMessage);
                        $apiDetails = 'LOGIN-Failed: Invalid input parameters';
                        $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, '', $apiDetails, '', 2);
                        if($isInserted == 0) {
                            $logMessage = "Failed to insert to APILogs.";
                            $logger->log($logger->logdate, " [LOGIN ERROR] ", $logMessage);
                        }
                        exit;
                }
            }  
        }
        else {
            $logMessage = 'One or more fields is not set or is blank';
            $transMsg = "One or more fields is not set or is blank.";
            $errorCode = 1;
            Utilities::log("ReturnMessage: " . $transMsg . " ErrorCode: " . $errorCode);
            $this->_sendResponse(200, CJSON::encode(CommonController::retMsgLogin($module, '', '', '', $errorCode, $transMsg)));
            $logger->log($logger->logdate, " [LOGIN ERROR] ", $logMessage);
            $apiDetails = 'LOGIN-Failed: Invalid login parameters.';
            $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, '', $apiDetails, '', 2);
            if($isInserted == 0) {
                $logMessage = "Failed to insert to APILogs.";
                $logger->log($logger->logdate, " [LOGIN ERROR] ", $logMessage);
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
        $logger = new ErrorLogger();
        $apiLogsModel = new APILogsModel();
       
        if(isset($request['EmailCardNumber'])) {
            if($request['EmailCardNumber'] == '') {
                $transMsg = "One or more fields is not set or is blank.";
                $logMessage = 'One or more fields is not set or is blank.';
                $errorCode = 1;
                Utilities::log("ReturnMessage: " . $transMsg . " ErrorCode: " . $errorCode);
                $this->_sendResponse(200, CJSON::encode(CommonController::retMsg($module, $errorCode, $transMsg)));
                $logger->log($logger->logdate, " [FORGOTPASSWORD ERROR] ", $logMessage);
                $apiDetails = 'FORGOTPASSWORD-Failed: Invalid input parameter.';
                $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, '', $apiDetails, '', 2);
                if($isInserted == 0) {
                    $logMessage = "Failed to insert to APILogs.";
                    $logger->log($logger->logdate, " [FORGOTPASSWORD ERROR] ", $logMessage);
                }
                exit;
            }
            else {
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

                if(Utilities::validateEmail($emailCardNumber)) {
                    $data = $memberInfoModel->getDetailsUsingEmail($emailCardNumber);

                    if($data) {
                        $MID = $data['MID'];
                        $firstname = $data['FirstName'];
                        $lastname = $data['LastName'];
                        $fullname = $firstname.' '.$lastname;
                        $ubCard = $memberCardsModel->getCardNumberUsingMID($MID);
                        $hashedUBCard = base64_encode($ubCard);
                        $result = $membersModel->updateForChangePasswordUsingMID($MID, 1);
                        if($result > 0) {
                            $helpers->sendEmailForgotPassword($emailCardNumber, $fullname, $hashedUBCard);
                            $isSuccessful = $auditTrailModel->logEvent(AuditTrailModel::API_FORGOT_PASSWORD, 'EmailCardNumber: '.$emailCardNumber, array('MID' => $MID,'SessionID' => ''));
                            if($isSuccessful == 0) {
                                $logMessage = "Failed to insert to Audittrail.";
                                $logger->log($logger->logdate, " [FORGOTPASSWORD ERROR] ", $logMessage);
                            }
                            $apiDetails = 'FORGOTPASSWORD-UpdateChangePass-Success: MID = '.$MID;
                            $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, $refID, $apiDetails, '', 1);
                            if($isInserted == 0) {

                                $logMessage = "Failed to insert to APILogs.";
                                $logger->log($logger->logdate, " [FORGOTPASSWORD ERROR] ", $logMessage);
                            }
                            $isUpdated = $memberSessionsModel->updateTransactionDate($MID);
                            if($isUpdated > 0) {
                                $transMsg = 'Request for change password is successfully processed. Please verify the link sent to your email to reset your password.';
                                $errorCode = 0;
                                $this->_sendResponse(200, CJSON::encode(CommonController::retMsg($module, $errorCode, $transMsg)));
                                $logMessage = 'Forgot Password successful.';
                                $logger->log($logger->logdate, " [FORGOTPASSWORD SUCCESSFUL] ", $logMessage);
                                $apiDetails = 'FORGOTPASSWORD-UpdateTransDate-Success: '.' MID = '.$MID;
                                $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, $refID, $apiDetails, '', 1);
                                if($isInserted == 0) {

                                    $logMessage = "Failed to insert to APILogs.";
                                    $logger->log($logger->logdate, " [FORGOTPASSWORD ERROR] ", $logMessage);
                                }
                                exit;
                            }
                            else {
                                $transMsg = 'Error in updating.';
                                $logMessage = 'Error in updating.';
                                $errorCode = 29;
                                $this->_sendResponse(200, CJSON::encode(CommonController::retMsg($module, $errorCode, $transMsg)));
                                $logger->log($logger->logdate, " [FORGOTPASSWORD ERROR] ", $logMessage);
                                $apiDetails = 'FORGOTPASSWORD-UpdateTransDate-Failed: '.' MID = '.$MID;
                                $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, $refID, $apiDetails, '', 2);
                                if($isInserted == 0) {
                                    $logMessage = "Failed to insert to APILogs.";
                                    $logger->log($logger->logdate, " [FORGOTPASSWORD ERROR] ", $logMessage);
                                }
                                exit;
                            }
                        }
                        else {
                            $transMsg = 'Transaction failed.';
                            $logMessage = 'Transaction failed.';
                            $errorCode = 4;
                            Utilities::log("ReturnMessage: " . $transMsg . " ErrorCode: " . $errorCode);
                            $this->_sendResponse(200, CJSON::encode(CommonController::retMsg($module, $errorCode, $transMsg)));
                            $logger->log($logger->logdate, " [FORGOTPASSWORD ERROR] ", $logMessage);
                            $apiDetails = 'FORGOTPASSWORD-UpdateChangePass-Failed: MID = '.$MID;
                            $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, $refID, $apiDetails, '', 2);
                            if($isInserted == 0) {
                                $logMessage = "Failed to insert to APILogs.";
                                $logger->log($logger->logdate, " [FORGOTPASSWORD ERROR] ", $logMessage);
                            }
                            exit;
                        }
                    }
                    else {
                        $transMsg = 'Invalid Email Address.';
                        $logMessage = 'Invalid Email Address.';
                        $errorCode = 5;
                        Utilities::log("ReturnMessage: " . $transMsg . " ErrorCode: " . $errorCode);
                        $this->_sendResponse(200, CJSON::encode(CommonController::retMsg($module, $errorCode, $transMsg)));
                        $logger->log($logger->logdate, " [FORGOTPASSWORD ERROR] ", $logMessage);
                        $apiDetails = 'FORGOTPASSWORD-Failed: Email is not found in db. MID = '.$MID;
                        $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, $refID, $apiDetails, '', 2);
                        if($isInserted == 0) {
                            $logMessage = "Failed to insert to APILogs.";
                            $logger->log($logger->logdate, " [FORGOTPASSWORD ERROR] ", $logMessage);
                        }
                        exit;   
                    }
                }
                else if(Utilities::validateAlphaNumeric($emailCardNumber)) {
                    $isCardExist = $cardsModel->IsExist($emailCardNumber);
                    $data = $memberCardsModel->getMemberDetailsByCard($emailCardNumber);
                    if($data && count($isCardExist) > 0) {
                        if(($data['Status'] == 1) || ($data['Status'] == 5)) {
                            $MID = $data['MID'];
                            $info = $memberInfoModel->getEmailFNameUsingMID($MID);
                            if(isset($info['Email']) && $info['Email'] != '') {
                                $fullname = $info['FirstName'].' '.$info['LastName'];
                                $email = $info['Email'];
                                $hashedUBCard = base64_encode($emailCardNumber);
                                $result = $membersModel->updateForChangePasswordUsingMID($MID, 1);
                                if($result > 0) {
                                    $helpers->sendEmailForgotPassword($email, $fullname, $hashedUBCard);
                                    $isSuccessful = $auditTrailModel->logEvent(AuditTrailModel::API_FORGOT_PASSWORD, 'EmailCardNumber: '.$emailCardNumber, array('MID' => $MID,'SessionID' => ''));
                                    if($isSuccessful == 0) {
                                        $logMessage = "Failed to insert to Audittrail.";
                                        $logger->log($logger->logdate, " [FORGOTPASSWORD ERROR] ", $logMessage);
                                    }
                                    $apiDetails = 'FORGOTPASSWORD-UpdateChangePass-Success: MID = '.$MID;
                                    $apiLogsModel->insertAPIlogs($apiMethod, $refID, $apiDetails, '', 1);
                                    if($isInserted == 0) {
                                        $logMessage = "Failed to insert to APILogs.";
                                        $logger->log($logger->logdate, " [FORGOTPASSWORD ERROR] ", $logMessage);
                                    }
                                    $isUpdated = $memberSessionsModel->updateTransactionDate($MID);
                                    if($isUpdated > 0) {
                                        $transMsg = 'Request for change password is successfully processed. Please verify the link sent to your email to reset your password.';
                                        $errorCode = 0;
                                        $this->_sendResponse(200, CJSON::encode(CommonController::retMsg($module, $errorCode, $transMsg)));
                                        $logMessage = 'Forgot Password successful.';
                                        $logger->log($logger->logdate, " [FORGOTPASSWORD SUCCESSFUL] ", $logMessage);
                                        $apiDetails = 'FORGOTPASSWORD-UpdateTransDate-Success: '.' MID = '.$MID;
                                        $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, $refID, $apiDetails, '', 1);
                                        if($isInserted == 0) {
                                            $logMessage = "Failed to insert to APILogs.";
                                            $logger->log($logger->logdate, " [FORGOTPASSWORD ERROR] ", $logMessage);
                                        }
                                        exit;
                                    }
                                    else {
                                        $transMsg = 'Error in updating.';
                                        $logMessage = 'Error in updating.';
                                        $errorCode = 29;
                                        $this->_sendResponse(200, CJSON::encode(CommonController::retMsg($module, $errorCode, $transMsg)));
                                        $logger->log($logger->logdate, " [FORGOTPASSWORD ERROR] ", $logMessage);
                                        $apiDetails = 'FORGOTPASSWORD-UpdateTransDate-Failed: '.' MID = '.$MID;
                                        $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, $refID, $apiDetails, '', 2);
                                        if($isInserted == 0) {
                                            $logMessage = "Failed to insert to APILogs.";
                                            $logger->log($logger->logdate, " [FORGOTPASSWORD ERROR] ", $logMessage);
                                        }
                                        exit;

                                    }
                                }
                                else {
                                    $transMsg = 'Transaction failed.';
                                    $logMessage = 'Transaction failed.';
                                    $errorCode = 4;
                                    Utilities::log("ReturnMessage: " . $transMsg . " ErrorCode: " . $errorCode);
                                    $this->_sendResponse(200, CJSON::encode(CommonController::retMsg($module, $errorCode, $transMsg)));
                                    $logger->log($logger->logdate, " [FORGOTPASSWORD ERROR] ", $logMessage);
                                    $apiDetails = 'FORGOTPASSWORD-UpdateChangePass-Failed: MID = '.$MID;
                                    $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, $refID, $apiDetails, '', 2);
                                    if($isInserted == 0) {
                                        $logMessage = "Failed to insert to APILogs.";
                                        $logger->log($logger->logdate, " [FORGOTPASSWORD ERROR] ", $logMessage);
                                    }
                                    exit;
                                }
                            }
                            else {
                                $transMsg = 'No Email Address found for this user. Please contact Philweb Customer Service Hotline 338-3388.';
                                $logMessage = 'No Email Address found for this user. Please contact Philweb Customer Service Hotline 338-3388.';
                                $errorCode = 12;
                                Utilities::log("ReturnMessage: " . $transMsg . " ErrorCode: " . $errorCode);
                                $this->_sendResponse(200, CJSON::encode(CommonController::retMsg($module, $errorCode, $transMsg)));
                                $logger->log($logger->logdate, " [FORGOTPASSWORD ERROR] ", $logMessage);
                                $apiDetails = 'FORGOTPASSWORD-Failed: Email not found in db. MID = '.$MID;
                                $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, $refID, $apiDetails, '', 2);
                                if($isInserted == 0) {
                                    $logMessage = "Failed to insert to APILogs.";
                                    $logger->log($logger->logdate, " [FORGOTPASSWORD ERROR] ", $logMessage);
                                }
                                exit;
                            }
                        }
                        else {
                            $transMsg = $helpers->setErrorMsgForCardStatus($data['Status']);

                            if($transMsg == 'Membership Card is Inactive') {
                                $transMsg = 'Card is Inactive.';
                                $errorCode = 6;
                            }
                            else if($transMsg == 'Membership Card is Deactivated') {
                                $transMsg = 'Card is Deactivated.';
                                $errorCode = 11;
                            }
                            else if($transMsg == 'Membership Card is Newly Migrated') {
                                $transMsg = 'Card is Newly Migrated.';
                                $errorCode = 7;
                            }
                            else if($transMsg == 'Membership Card is Temporarily Migrated') {
                                $transMsg = 'Card is Temporarily Migrated.';
                                $errorCode = 8;
                            }
                            else if($transMsg == 'Membership Card is Banned') {
                                $transMsg = 'Card is Banned.';
                                $errorCode = 9;
                            }
                            else {
                                $transMsg = 'Card is Invalid';
                                $errorCode = 10;
                            }

                            $logMessage = $transMsg;
                            Utilities::log("ReturnMessage: " . $transMsg . " ErrorCode: " . $errorCode);
                            $this->_sendResponse(200, CJSON::encode(CommonController::retMsg($module, $errorCode, $transMsg)));
                            $logger->log($logger->logdate, " [FORGOTPASSWORD ERROR] ", $logMessage);
                            $apiDetails = 'FORGOTPASSWORD-Failed: '.$transMsg.'.'.'Status = '.$data['Status'].' MID = '.$MID;
                            $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, $refID, $apiDetails, '', 2);
                            if($isInserted == 0) {
                                $logMessage = "Failed to insert to APILogs.";
                                $logger->log($logger->logdate, " [FORGOTPASSWORD ERROR] ", $logMessage);
                            }
                            exit;
                        }
                    }
                    else {
                        if($isCardExist == 0) {
                            $transMsg = "Card is Invalid.";
                            $logMessage = 'Card is Invalid.';
                            $errorCode = 10;
                            Utilities::log("ReturnMessage: " . $transMsg . " ErrorCode: " . $errorCode);
                            $this->_sendResponse(200, CJSON::encode(CommonController::retMsg($module, $errorCode,$transMsg)));
                            $logger->log($logger->logdate, " [FORGOTPASSWORD ERROR] ", $logMessage);
                            $apiDetails = 'FORGOTPASSWORD-Failed: Membership card is invalid. MID = '.$MID;
                            $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, $refID, $apiDetails, '', 2);
                            if($isInserted == 0) {
                                $logMessage = "Failed to insert to APILogs.";
                                $logger->log($logger->logdate, " [FORGOTPASSWORD ERROR] ", $logMessage);
                            }
                            exit;
                        }
                        else {
                            $transMsg = "Card is Inactive.";
                            $logMessage = 'Card is Inactive.';
                            $errorCode = 6;
                            Utilities::log("ReturnMessage: " . $transMsg . " ErrorCode: " . $errorCode);
                            $this->_sendResponse(200, CJSON::encode(CommonController::retMsg($module, $errorCode, $transMsg)));
                            $logger->log($logger->logdate, " [FORGOTPASSWORD ERROR] ", $logMessage);
                            $apiDetails = 'FORGOTPASSWORD-Failed: Membership card is inactive. MID = '.$MID;
                            $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, $refID, $apiDetails, '', 2);
                            if($isInserted == 0) {
                                $logMessage = "Failed to insert to APILogs.";
                                $logger->log($logger->logdate, " [FORGOTPASSWORD ERROR] ", $logMessage);
                            }
                            exit;
                        }
                    }
                }
                else {
                    $transMsg = "Invalid input.";
                    $errorCode = 2;
                    Utilities::log("ReturnMessage: " . $transMsg . " ErrorCode: " . $errorCode);
                    $this->_sendResponse(200, CJSON::encode(CommonController::retMsg($module, $errorCode, $transMsg)));
                    $logMessage = 'Invalid input.';
                    $logger->log($logger->logdate, " [FORGOTPASSWORD ERROR] ", $logMessage);
                    $apiDetails = 'FORGOTPASSWORD-Failed: Invalid card number. MID = '.$MID;
                    $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, $refID, $apiDetails, '', 2);
                    if($isInserted == 0) {
                        $logMessage = "Failed to insert to APILogs.";
                        $logger->log($logger->logdate, " [FORGOTPASSWORD ERROR] ", $logMessage);
                    }
                    exit;
                }  
            }
        }
        else {
            $transMsg = "One or more fields is not set or is blank.";
            $errorCode = 1;
            Utilities::log("ReturnMessage: " . $transMsg . " ErrorCode: " . $errorCode);
            $this->_sendResponse(200, CJSON::encode(CommonController::retMsg($module, $errorCode, $transMsg)));
            $logMessage = 'One or more fields is not set or is blank.';
            $logger->log($logger->logdate, " [FORGOTPASSWORD ERROR] ", $logMessage);
            $apiDetails = 'FORGOTPASSWORD-Failed: Invalid input parameter.';
            $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, '', $apiDetails, '', 2);
            if($isInserted == 0) {
                $logMessage = "Failed to insert to APILogs.";
                $logger->log($logger->logdate, " [FORGOTPASSWORD ERROR] ", $logMessage);
            }
            exit;
        }
    }
    
    //@date 08-14-2014
    //@purpose check if date is valid
    private function _isRealDate($date) 
    { 
        if (false === strtotime($date)) 
        { 
            return false;
        } 
        else
        { 
            list($year, $month, $day) = explode('-', $date); 
            if (false === checkdate($month, $day, $year)) 
            { 
                return false;
            } 
        } 
        return true;
    }
    
    //@date 07-24-2014
    //@purpose member registration
    public function actionRegisterMember() {
        $request = $this->_readJsonRequest();
        
        $transMsg = '';
        $errorCode = '';
        $module = 'RegisterMember';
        $apiMethod = 3;
        
        $logger = new ErrorLogger();
        $apiLogsModel = new APILogsModel();
        
        if(isset($request['FirstName']) && isset($request['LastName']) && isset($request['MobileNo']) && isset($request['Password']) 
        && isset($request['EmailAddress']) && isset($request['IDNumber']) && isset($request['Birthdate']) && isset($request['IDPresented']) && isset($request['PermanentAdd'])) {
            if(($request['FirstName'] == '') || ($request['LastName'] == '') || ($request['MobileNo'] == '')|| ($request['Password'] == '') || ($request['EmailAddress'] == '')
            || ($request['IDNumber'] == '') || ($request['Birthdate'] == '') || ($request['IDPresented'] == '') || ($request['PermanentAdd'] == '')) {
                $transMsg = "One or more fields is not set or is blank.";
                $errorCode = 1;
                Utilities::log("ReturnMessage: " . $transMsg . " ErrorCode: " . $errorCode);
                $this->_sendResponse(200, CJSON::encode(CommonController::retMsgRegisterMember($module, $errorCode, $transMsg)));
                $logMessage = 'One or more fields is not set or is blank.';
                $logger->log($logger->logdate, " [REGISTERMEMBER ERROR] ", $logMessage);
                $apiDetails = 'REGISTERMEMBER-Failed: Invalid input parameters. ';
                $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, '', $apiDetails, '', 2);
                if($isInserted == 0) {
                    $logMessage = "Failed to insert to APILogs.";
                    $logger->log($logger->logdate, " [REGISTERMEMBER ERROR] ", $logMessage);
                }
                exit;
            }
            else if(strlen($request['FirstName']) < 2 || ($request['MiddleName'] != '' && strlen($request['MiddleName']) < 2) || strlen($request['LastName']) < 2) {
                $transMsg = "Name should not be less than 2 characters long.";
                $errorCode = 14;
                Utilities::log("ReturnMessage: " . $transMsg . " ErrorCode: " . $errorCode);
                $this->_sendResponse(200, CJSON::encode(CommonController::retMsgRegisterMember($module, $errorCode, $transMsg)));
                $logMessage = 'Name should not be less than 2 characters long.';
                $logger->log($logger->logdate, " [REGISTERMEMBER ERROR] ", $logMessage);
                $apiDetails = 'REGISTERMEMBER-Failed: Invalid input parameters. ';
                $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, '', $apiDetails, '', 2);
                if($isInserted == 0) {
                    $logMessage = "Failed to insert to APILogs.";
                    $logger->log($logger->logdate, " [REGISTERMEMBER ERROR] ", $logMessage);
                }
                exit;
            }
            else if(preg_match("/^[A-Za-z\s]+$/", trim($request['FirstName'])) == 0 || preg_match("/^[A-Za-z\s]+$/", trim($request['MiddleName'])) == 0 || preg_match("/^[A-Za-z\s]+$/", trim($request['LastName'])) == 0 || preg_match("/^[A-Za-z\s]+$/", trim($request['NickName'])) == 0) {
//            else if(ctype_alpha($request['FirstName']) == FALSE || ctype_alpha($request['MiddleName']) == FALSE ||
//                    ctype_alpha($request['LastName']) == FALSE || ctype_alpha($request['NickName']) == FALSE) {
                $transMsg = "Name should consist of letters only.";
                $errorCode = 17;
                Utilities::log("ReturnMessage: " . $transMsg . " ErrorCode: " . $errorCode);
                $this->_sendResponse(200, CJSON::encode(CommonController::retMsgRegisterMember($module, $errorCode, $transMsg)));
                $logMessage = 'Name should consist of letters only.';
                $logger->log($logger->logdate, " [REGISTERMEMBER ERROR] ", $logMessage);
                $apiDetails = 'REGISTERMEMBER-Failed: Invalid input parameters. ';
                $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, '', $apiDetails, '', 2);
                if($isInserted == 0) {
                    $logMessage = "Failed to insert to APILogs.";
                    $logger->log($logger->logdate, " [REGISTERMEMBER ERROR] ", $logMessage);
                }
                exit;
            }
            else if(ctype_alnum($request['Password']) == FALSE || ctype_alnum($request['IDNumber']) == FALSE ) {
                $transMsg = "Password and ID Number should consist of letters and numbers only.";
                $errorCode = 18;
                Utilities::log("ReturnMessage: " . $transMsg . " ErrorCode: " . $errorCode);
                $this->_sendResponse(200, CJSON::encode(CommonController::retMsgRegisterMember($module, $errorCode, $transMsg)));
                $logMessage = 'Password and ID Number should consist of letters and numbers only.';
                $logger->log($logger->logdate, " [REGISTERMEMBER ERROR] ", $logMessage);
                $apiDetails = 'REGISTERMEMBER-Failed: Invalid input parameters. ';
                $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, '', $apiDetails, '', 2);
                if($isInserted == 0) {
                    $logMessage = "Failed to insert to APILogs.";
                    $logger->log($logger->logdate, " [REGISTERMEMBER ERROR] ", $logMessage);
                }
                exit;
            }
            else if($request['Password'] != '' && strlen($request['Password']) < 5) {
                $transMsg = "Password should not be less than 5 characters long.";
                $errorCode = 19;
                Utilities::log("ReturnMessage: " . $transMsg . " ErrorCode: " . $errorCode);
                $this->_sendResponse(200, CJSON::encode(CommonController::retMsgRegisterMember($module, $errorCode, $transMsg)));
                $logMessage = 'Password should not be less than 5 characters long.';
                $logger->log($logger->logdate, " [REGISTERMEMBER ERROR] ", $logMessage);
                $apiDetails = 'REGISTERMEMBER-Failed: Invalid input parameters. ';
                $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, '', $apiDetails, '', 2);
                if($isInserted == 0) {
                    $logMessage = "Failed to insert to APILogs.";
                    $logger->log($logger->logdate, " [REGISTERMEMBER ERROR] ", $logMessage);
                }
                exit;
            }
            else if(strlen($request['MobileNo']) < 9 || ($request['AlternateMobileNo'] != '' && strlen($request['AlternateMobileNo']) < 9)) {
                $transMsg = "Mobile number should not be less than 9 digits long.";
                $errorCode = 15;
                Utilities::log("ReturnMessage: " . $transMsg . " ErrorCode: " . $errorCode);
                $this->_sendResponse(200, CJSON::encode(CommonController::retMsgRegisterMember($module, $errorCode, $transMsg)));
                $logMessage = 'Mobile number should not be less than 9 digits long.';
                $logger->log($logger->logdate, " [REGISTERMEMBER ERROR] ", $logMessage);
                $apiDetails = 'REGISTERMEMBER-Failed: Invalid input parameters. ';
                $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, '', $apiDetails, '', 2);
                if($isInserted == 0) {
                    $logMessage = "Failed to insert to APILogs.";
                    $logger->log($logger->logdate, " [REGISTERMEMBER ERROR] ", $logMessage);
                }
                exit;
            }
            else if((substr($request['MobileNo'],0, 2) != '09' && substr($request['MobileNo'],0, 3) != '639') && (substr($request['AlternateMobileNo'],0, 2) != '09' && substr($request['AlternateMobileNo'],0, 3) != '639') ) {
                $transMsg = "Mobile number should begin with either '09' or '639'.";
                $errorCode = 69;
                Utilities::log("ReturnMessage: " . $transMsg . " ErrorCode: " . $errorCode);
                $this->_sendResponse(200, CJSON::encode(CommonController::retMsgRegisterMember($module, $errorCode, $transMsg)));
                $logMessage = "Mobile number should begin with either '09' or '639'.";
                $logger->log($logger->logdate, " [REGISTERMEMBER ERROR] ", $logMessage);
                $apiDetails = 'REGISTERMEMBER-Failed: Invalid input parameters. ';
                $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, '', $apiDetails, '', 2);
                if($isInserted == 0) {
                    $logMessage = "Failed to insert to APILogs.";
                    $logger->log($logger->logdate, " [REGISTERMEMBER ERROR] ", $logMessage);
                }
                exit;
            }
            else if(!is_numeric($request['MobileNo']) || !is_numeric($request['AlternateMobileNo'])) {
                $transMsg = "Mobile number should consist of numbers only.";
                $errorCode = 16;
                Utilities::log("ReturnMessage: " . $transMsg . " ErrorCode: " . $errorCode);
                $this->_sendResponse(200, CJSON::encode(CommonController::retMsgRegisterMember($module, $errorCode, $transMsg)));
                $logMessage = 'Mobile number should consist of numbers only.';
                $logger->log($logger->logdate, " [REGISTERMEMBER ERROR] ", $logMessage);
                $apiDetails = 'REGISTERMEMBER-Failed: Invalid input parameters. ';
                $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, '', $apiDetails, '', 2);
                if($isInserted == 0) {
                    $logMessage = "Failed to insert to APILogs.";
                    $logger->log($logger->logdate, " [REGISTERMEMBER ERROR] ", $logMessage);
                }
                exit;
            }
            else if($request['MobileNo'] == $request['AlternateMobileNo']) {
                $transMsg = "Mobile number should not be the same as alternate mobile number.";
                $errorCode = 86;
                Utilities::log("ReturnMessage: " . $transMsg . " ErrorCode: " . $errorCode);
                $this->_sendResponse(200, CJSON::encode(CommonController::retMsgRegisterMember($module, $errorCode, $transMsg)));
                $logMessage = 'Mobile number should not be the same as alternate mobile number.';
                $logger->log($logger->logdate, " [REGISTERMEMBER ERROR] ", $logMessage);
                $apiDetails = 'REGISTERMEMBER-Failed: Invalid input parameters. ';
                $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, '', $apiDetails, '', 2);
                if($isInserted == 0) {
                    $logMessage = "Failed to insert to APILogs.";
                    $logger->log($logger->logdate, " [REGISTERMEMBER ERROR] ", $logMessage);
                }
                exit;
            }
            else if(!Utilities::validateEmail($request['EmailAddress']) || !Utilities::validateEmail($request['AlternateEmail']) ) {
                $transMsg = "Invalid Email Address.";
                $errorCode = 5;
                Utilities::log("ReturnMessage: " . $transMsg . " ErrorCode: " . $errorCode);
                $this->_sendResponse(200, CJSON::encode(CommonController::retMsgRegisterMember($module, $errorCode, $transMsg)));
                $logMessage = 'Invalid Email Address.';
                $logger->log($logger->logdate, " [REGISTERMEMBER ERROR] ", $logMessage);
                $apiDetails = 'REGISTERMEMBER-Failed: Invalid input parameters. ';
                $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, '', $apiDetails, '', 2);
                if($isInserted == 0) {
                    $logMessage = "Failed to insert to APILogs.";
                    $logger->log($logger->logdate, " [REGISTERMEMBER ERROR] ", $logMessage);
                }
                exit;
            }
            else if($request['EmailAddress'] == $request['AlternateEmail']) {
                $transMsg = "Email Address should not be the same as alternate email.";
                $errorCode = 87;
                Utilities::log("ReturnMessage: " . $transMsg . " ErrorCode: " . $errorCode);
                $this->_sendResponse(200, CJSON::encode(CommonController::retMsgRegisterMember($module, $errorCode, $transMsg)));
                $logMessage = 'Email Address should not be the same as alternate email.';
                $logger->log($logger->logdate, " [REGISTERMEMBER ERROR] ", $logMessage);
                $apiDetails = 'REGISTERMEMBER-Failed: Invalid input parameters. ';
                $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, '', $apiDetails, '', 2);
                if($isInserted == 0) {
                    $logMessage = "Failed to insert to APILogs.";
                    $logger->log($logger->logdate, " [REGISTERMEMBER ERROR] ", $logMessage);
                }
                exit;
            }
            else if($request['Gender'] != 1 && $request['Gender'] != 2) {
                $transMsg = "Please input 1 for male or 2 for female.";
                $errorCode = 77;
                Utilities::log("ReturnMessage: " . $transMsg . " ErrorCode: " . $errorCode);
                $this->_sendResponse(200, CJSON::encode(CommonController::retMsgRegisterMember($module, $errorCode, $transMsg)));
                $logMessage = 'Please input 1 for male or 2 for female.';
                $logger->log($logger->logdate, " [REGISTERMEMBER ERROR] ", $logMessage);
                $apiDetails = 'REGISTERMEMBER-Failed: Invalid input parameters. ';
                $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, '', $apiDetails, '', 2);
                if($isInserted == 0) {
                    $logMessage = "Failed to insert to APILogs.";
                    $logger->log($logger->logdate, " [REGISTERMEMBER ERROR] ", $logMessage);
                }
                exit;
            }
            else if($request['IDPresented'] != 1 && $request['IDPresented'] != 2 && $request['IDPresented'] != 3 && $request['IDPresented'] != 4 && $request['IDPresented'] != 5 && $request['IDPresented'] != 6 && $request['IDPresented'] != 7 && $request['IDPresented'] != 8 && $request['IDPresented'] != 9) {
                $transMsg = "Please input a valid ID Presented (1 to 9).";
                $errorCode = 78;
                Utilities::log("ReturnMessage: " . $transMsg . " ErrorCode: " . $errorCode);
                $this->_sendResponse(200, CJSON::encode(CommonController::retMsgRegisterMember($module, $errorCode, $transMsg)));
                $logMessage = 'Please input a valid ID Presented (1 to 9).';
                $logger->log($logger->logdate, " [REGISTERMEMBER ERROR] ", $logMessage);
                $apiDetails = 'REGISTERMEMBER-Failed: Invalid input parameters. ';
                $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, '', $apiDetails, '', 2);
                if($isInserted == 0) {
                    $logMessage = "Failed to insert to APILogs.";
                    $logger->log($logger->logdate, " [REGISTERMEMBER ERROR] ", $logMessage);
                }
                exit;
            }
            else if($request['Nationality'] != 1 && $request['Nationality'] != 2 && $request['Nationality'] != 3 && $request['Nationality'] != 4 && $request['Nationality'] != 5 && $request['Nationality'] != 6) {
                $transMsg = "Please input a valid Nationality (1 to 6).";
                $errorCode = 79;
                Utilities::log("ReturnMessage: " . $transMsg . " ErrorCode: " . $errorCode);
                $this->_sendResponse(200, CJSON::encode(CommonController::retMsgRegisterMember($module, $errorCode, $transMsg)));
                $logMessage = 'Please input a valid Nationality (1 to 6).';
                $logger->log($logger->logdate, " [REGISTERMEMBER ERROR] ", $logMessage);
                $apiDetails = 'REGISTERMEMBER-Failed: Invalid input parameters. ';
                $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, '', $apiDetails, '', 2);
                if($isInserted == 0) {
                    $logMessage = "Failed to insert to APILogs.";
                    $logger->log($logger->logdate, " [REGISTERMEMBER ERROR] ", $logMessage);
                }
                exit;
            }
            else if($this->_isRealDate($request['Birthdate']) == FALSE) {
                $transMsg = "Please input a valid Date (YYYY-MM-DD).";
                $errorCode = 80;
                Utilities::log("ReturnMessage: " . $transMsg . " ErrorCode: " . $errorCode);
                $this->_sendResponse(200, CJSON::encode(CommonController::retMsgRegisterMember($module, $errorCode, $transMsg)));
                $logMessage = 'Please input a valid Date.';
                $logger->log($logger->logdate, " [REGISTERMEMBER ERROR] ", $logMessage);
                $apiDetails = 'REGISTERMEMBER-Failed: Invalid input parameters. ';
                $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, '', $apiDetails, '', 2);
                if($isInserted == 0) {
                    $logMessage = "Failed to insert to APILogs.";
                    $logger->log($logger->logdate, " [REGISTERMEMBER ERROR] ", $logMessage);
                }
                exit;   
            }
            else if($request['Occupation'] != 1 && $request['Occupation'] != 2 && $request['Occupation'] != 3 && $request['Occupation'] != 4 && $request['Occupation'] != 5) {
                $transMsg = "Please input a valid Occupation (1 to 5).";
                $errorCode = 81;
                Utilities::log("ReturnMessage: " . $transMsg . " ErrorCode: " . $errorCode);
                $this->_sendResponse(200, CJSON::encode(CommonController::retMsgRegisterMember($module, $errorCode, $transMsg)));
                $logMessage = 'Please input a valid Occupation (1 to 5).';
                $logger->log($logger->logdate, " [REGISTERMEMBER ERROR] ", $logMessage);
                $apiDetails = 'REGISTERMEMBER-Failed: Invalid input parameters. ';
                $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, '', $apiDetails, '', 2);
                if($isInserted == 0) {
                    $logMessage = "Failed to insert to APILogs.";
                    $logger->log($logger->logdate, " [REGISTERMEMBER ERROR] ", $logMessage);
                }
                exit;
            }
            else if($request['IsSmoker'] != 1 && $request['IsSmoker'] != 2) {
                $transMsg = "Please input 1 for smoker or 2 for non-smoker.";
                $errorCode = 82;
                Utilities::log("ReturnMessage: " . $transMsg . " ErrorCode: " . $errorCode);
                $this->_sendResponse(200, CJSON::encode(CommonController::retMsgRegisterMember($module, $errorCode, $transMsg)));
                $logMessage = 'Please input 1 for smoker or 2 for non-smoker.';
                $logger->log($logger->logdate, " [REGISTERMEMBER ERROR] ", $logMessage);
                $apiDetails = 'REGISTERMEMBER-Failed: Invalid input parameters. ';
                $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, '', $apiDetails, '', 2);
                if($isInserted == 0) {
                    $logMessage = "Failed to insert to APILogs.";
                    $logger->log($logger->logdate, " [REGISTERMEMBER ERROR] ", $logMessage);
                }
                exit;
            }
            else if($request['ReferrerID'] != 1 && $request['ReferrerID'] != 2 && $request['ReferrerID'] != 3 && $request['ReferrerID'] != 4 && $request['ReferrerID'] != 5 && $request['ReferrerID'] != 6 && $request['ReferrerID'] != 7 && $request['ReferrerID'] != 8 && $request['ReferrerID'] != 9 && $request['ReferrerID'] != 10) {
                $transMsg = "Please input a valid Referrer ID (1 to 10).";
                $errorCode = 83;
                Utilities::log("ReturnMessage: " . $transMsg . " ErrorCode: " . $errorCode);
                $this->_sendResponse(200, CJSON::encode(CommonController::retMsgRegisterMember($module, $errorCode, $transMsg)));
                $logMessage = 'Please input a valid Referrer ID (1 to 10).';
                $logger->log($logger->logdate, " [REGISTERMEMBER ERROR] ", $logMessage);
                $apiDetails = 'REGISTERMEMBER-Failed: Invalid input parameters. ';
                $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, '', $apiDetails, '', 2);
                if($isInserted == 0) {
                    $logMessage = "Failed to insert to APILogs.";
                    $logger->log($logger->logdate, " [REGISTERMEMBER ERROR] ", $logMessage);
                }
                exit;
            }
            else if($request['EmailSubscription'] != '0' && $request['EmailSubscription'] != '1') {
                $transMsg = "Please input 0 for email non-subscription else input 1.";
                $errorCode = 84;
                Utilities::log("ReturnMessage: " . $transMsg . " ErrorCode: " . $errorCode);
                $this->_sendResponse(200, CJSON::encode(CommonController::retMsgRegisterMember($module, $errorCode, $transMsg)));
                $logMessage = 'Please input 0 for email non-subscription else input 1.';
                $logger->log($logger->logdate, " [REGISTERMEMBER ERROR] ", $logMessage);
                $apiDetails = 'REGISTERMEMBER-Failed: Invalid input parameters. ';
                $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, '', $apiDetails, '', 2);
                if($isInserted == 0) {
                    $logMessage = "Failed to insert to APILogs.";
                    $logger->log($logger->logdate, " [REGISTERMEMBER ERROR] ", $logMessage);
                }
                exit;
            }
            else if($request['SMSSubscription'] != '0' && $request['SMSSubscription'] != '1') {
                $transMsg = "Please input 0 for sms non-subscription else input 1.";
                $errorCode = 85;
                Utilities::log("ReturnMessage: " . $transMsg . " ErrorCode: " . $errorCode);
                $this->_sendResponse(200, CJSON::encode(CommonController::retMsgRegisterMember($module, $errorCode, $transMsg)));
                $logMessage = 'Please input 0 for sms non-subscription else input 1.';
                $logger->log($logger->logdate, " [REGISTERMEMBER ERROR] ", $logMessage);
                $apiDetails = 'REGISTERMEMBER-Failed: Invalid input parameters. ';
                $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, '', $apiDetails, '', 2);
                if($isInserted == 0) {
                    $logMessage = "Failed to insert to APILogs.";
                    $logger->log($logger->logdate, " [REGISTERMEMBER ERROR] ", $logMessage);
                }
                exit;
            }
            else {
                
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
                    if($middlename == '')
                        $middlename = '';
                    $lastname = trim($request['LastName']);
                    $lastname = str_replace(" ", "", $lastname);
                    $nickname = trim($request['NickName']);
                    if($nickname == '')
                        $nickname = '';
                    $password = md5(trim($request['Password']));
                    $permanentAddress = trim($request['PermanentAdd']);
                    $mobileNumber = trim($request['MobileNo']);
                    $alternateMobileNumber = trim($request['AlternateMobileNo']);
                    if($alternateMobileNumber == '')
                        $alternateMobileNumber = '';
                    $alternateEmail = trim($request['AlternateEmail']);
                    if($alternateEmail == '')
                        $alternateEmail = '';
                    $idNumber = trim($request['IDNumber']);
                    $idPresented = trim($request['IDPresented']);
                    $gender = trim($request['Gender']);
                    if($gender == '')
                        $gender = '';
                    $birthdate = trim($request['Birthdate']);
                    //$age = number_format((abs(strtotime($birthdate) - strtotime(date('Y-m-d'))) / 60 / 60 / 24 / 365), 0);
                    $tz = new DateTimeZone("Asia/Taipei");
                    $age = DateTime::createFromFormat('Y-m-d', $birthdate, $tz)->diff(new DateTime('now', $tz))->y;
                    $nationalityID = trim($request['Nationality']);
                    if($nationalityID == '')
                        $nationalityID = '';
                    $occupationID = trim($request['Occupation']);
                    if($occupationID == '')
                        $occupationID = '';
                    $isSmoker = trim($request['IsSmoker']);
                    if($isSmoker == '')
                        $isSmoker = '';
                    $referrerID = trim($request['ReferrerID']);
                    if($referrerID == '')
                        $referrerID = '';
                    $referralCode = trim($request['ReferralCode']);
                    if($referralCode == '')
                        $referralCode = '';
                    $emailSubscription = trim($request['EmailSubscription']);
                    $smsSubscription = trim($request['SMSSubscription']);
                  
                    $refID = $firstname.' '.$lastname;
                    
                    //check if member is blacklisted
                    $isBlackListed = $blackListsModel->checkIfBlackListed($firstname, $lastname, $birthdate, 3);
                    //check if email is active and existing in live membership db
                    $activeEmail = $membershipTempModel->checkIfActiveVerifiedEmail($emailAddress);
                    
                    if($activeEmail['COUNT(MID)'] > 0) {
                        $transMsg = "Sorry, " . $emailAddress . " already belongs to an existing account. Please enter another email address.";
                        $errorCode = 21;
                        Utilities::log("ReturnMessage: " . $transMsg . " ErrorCode: " . $errorCode);
                        $this->_sendResponse(200, CJSON::encode(CommonController::retMsgRegisterMember($module, $errorCode, $transMsg)));
                        $logMessage = 'Sorry, ' . $emailAddress . ' already belongs to an existing account. Please enter another email address.';
                        $logger->log($logger->logdate, " [REGISTERMEMBER ERROR] ", $logMessage);
                        $apiDetails = 'REGISTERMEMBER-Failed: Email is already used. EmailAddress = '.$emailAddress;
                        $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, $refID, $apiDetails, '', 2);
                        if($isInserted == 0) {
                            $logMessage = "Failed to insert to APILogs.";
                            $logger->log($logger->logdate, " [REGISTERMEMBER ERROR] ", $logMessage);
                        }
                        exit;
                    }
                    else if($isBlackListed['Count'] > 0) {
                        $transMsg = "Registration cannot proceed. Please contact Customer Service.";
                        $errorCode = 22;
                        Utilities::log("ReturnMessage: " . $transMsg . " ErrorCode: " . $errorCode);
                        $this->_sendResponse(200, CJSON::encode(CommonController::retMsgRegisterMember($module, $errorCode, $transMsg)));
                        $logMessage = 'Registration cannot proceed. Please contact Customer Service.';
                        $logger->log($logger->logdate, " [REGISTERMEMBER ERROR] ", $logMessage);
                        $apiDetails = 'REGISTERMEMBER-Failed: Player is blacklisted. Name = '.$firstname.' '.$lastname.', Birthdate = '.$birthdate;
                        $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, $refID, $apiDetails, '', 2);
                        if($isInserted == 0) {
                            $logMessage = "Failed to insert to APILogs.";
                            $logger->log($logger->logdate, " [REGISTERMEMBER ERROR] ", $logMessage);
                        }
                        exit;
                    }
                    else if($age < 21) {
                        $transMsg = "Must be at least 21 years old to register.";
                        $errorCode = 89;
                        Utilities::log("ReturnMessage: " . $transMsg . " ErrorCode: " . $errorCode);
                        $this->_sendResponse(200, CJSON::encode(CommonController::retMsgRegisterMember($module, $errorCode, $transMsg)));
                        $logMessage = 'Must be at least 21 years old to register.';
                        $logger->log($logger->logdate, " [REGISTERMEMBER ERROR] ", $logMessage);
                        $apiDetails = 'REGISTERMEMBER-Failed: Player is under 21. Name = '.$firstname.' '.$lastname.', Birthdate = '.$birthdate;
                        $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, $refID, $apiDetails, '', 2);
                        if($isInserted == 0) {
                            $logMessage = "Failed to insert to APILogs.";
                            $logger->log($logger->logdate, " [REGISTERMEMBER ERROR] ", $logMessage);
                        }
                        exit;
                    }
                    else {
                        //check if email is already verified in temp table
                        $tempEmail = $membershipTempModel->checkTempVerifiedEmail($emailAddress);
                        
                        if($tempEmail['COUNT(a.MID)'] > 0) {
                            
                            $transMsg = "Email is already verified. Please choose a different email address.";
                            $errorCode = 52;
                            Utilities::log("ReturnMessage: " . $transMsg . " ErrorCode: " . $errorCode);
                            $this->_sendResponse(200, CJSON::encode(CommonController::retMsgRegisterMember($module, $errorCode, $transMsg)));
                            $logMessage = 'Email is already verified. Please choose a different email address.';
                            $logger->log($logger->logdate, " [REGISTERMEMBER ERROR] ", $logMessage);
                            $apiDetails = 'REGISTERMEMBER-Failed: Email is already verified. Email = '.$emailAddress;
                            $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, $refID, $apiDetails, '', 2);
                            if($isInserted == 0) {
                                $logMessage = "Failed to insert to APILogs.";
                                $logger->log($logger->logdate, " [REGISTERMEMBER ERROR] ", $logMessage);
                            }
                            exit;
                        }
                        else {
                            $lastInsertedMID = $membershipTempModel->register($emailAddress, $firstname, $middlename, $lastname, $nickname, $password, $permanentAddress, $mobileNumber, $alternateMobileNumber, $alternateEmail, $idNumber, $idPresented, $gender, $referralCode, $birthdate, $occupationID, $nationalityID, $isSmoker, $referrerID, $emailSubscription, $smsSubscription);
                            if($lastInsertedMID > 0) {
//                                if(isset($session['MID'])) {
//                                    $ID = $session['MID'];
//                                    $mpSessionID = $session['SessionID'];
//                                }
                          //      else {
                                    $MID = $lastInsertedMID;
                                    $mpSessionID = '';
                                   // $emailAddress = 'guest';
                               // }
                                
                                    $memberInfos = $membershipTempModel->getTempMemberInfoForSMS($lastInsertedMID);

                                    //match to 09 or 639 in mobile number
                                    $match = substr($memberInfos['MobileNumber'], 0, 3);
                                    if($match == "639"){
                                        $mncount = count($memberInfos["MobileNumber"]);
                                        if(!$mncount == 12){
                                            $message = "Failed to send SMS. Invalid Mobile Number.";
                                            $logger->log($logger->logdate,"[REGISTERMEMBER ERROR] ", $message);
                                            $apiDetails = "REGISTERMEMBER-Failed: Failed to send SMS. Invalid Mobile Number [MID = $lastInsertedMID].";
                                            $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, $refID, $apiDetails, '', 2);
                                            if($isInserted == 0) {
                                                $logMessage = "Failed to insert to APILogs.";
                                                $logger->log($logger->logdate, " [REGISTERMEMBER ERROR] ", $logMessage);
                                            }
                                            
                                        } else {
                                            $templateid = $ref_SMSApiMethodsModel->getSMSMethodTemplateID(Ref_SMSApiMethodsModel::PLAYER_REGISTRATION);
                                            $methodid = Ref_SMSApiMethodsModel::PLAYER_REGISTRATION;
                                            $mobileno = $memberInfos["MobileNumber"];
                                            $smslastinsertedid = $smsRequestLogsModel->insertSMSRequestLogs($methodid, $mobileno, $memberInfos["DateCreated"]);
                                            if($smslastinsertedid != 0 && $smslastinsertedid != ''){
                                                $trackingid = "SMSR".$smslastinsertedid;
                                                $apiURL = Yii::app()->params["SMSURI"];    
                                                $app_id = Yii::app()->params["app_id"];    
                                                $membershipSMSApi = new MembershipSmsAPI($apiURL, $app_id);
                                                $smsresult = $membershipSMSApi->sendRegistration($mobileno, $templateid['SMSTemplateID'], $memberInfos["DateCreated"], $memberInfos["TemporaryAccountCode"], $trackingid);

                                                if(isset($smsresult['status'])){
                                                    if($smsresult['status'] != 1){
                                                        $message = "Failed to send SMS.";
                                                        $logger->log($logger->logdate,"[REGISTERMEMBER ERROR] ", $message);
                                                        $apiDetails = "REGISTERMEMBER-Failed: Failed to send SMS. [MID = $lastInsertedMID].";
                                                        $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, $refID, $apiDetails, '', 2);
                                                        if($isInserted == 0) {
                                                            $logMessage = "Failed to insert to APILogs.";
                                                            $logger->log($logger->logdate, " [REGISTERMEMBER ERROR] ", $logMessage);
                                                        }
                                                        
                                                    }
                                                    else {
                                                        $transMsg = "You have successfully registered! An active Temporary Account will be sent to your email address or mobile number, which can be used to start session or credit points in the absence of Membership Card. Please note that your Registered Account and Temporary Account will be activated only after 24 hours.";
                                                        $errorCode = 0;
                                                        Utilities::log("ReturnMessage: " . $transMsg . " ErrorCode: " . $errorCode);
                                                        $this->_sendResponse(200, CJSON::encode(CommonController::retMsgRegisterMember($module, $errorCode, nl2br($transMsg))));
                                                        $logMessage = 'Registration is successful.';
                                                        $logger->log($logger->logdate, " [REGISTERMEMBER SUCCESSFUL] ", $logMessage);
                                                        $apiDetails = 'REGISTERMEMBER-Success: Registration is successful. MID = '.$lastInsertedMID;
                                                        $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, $refID, $apiDetails, '', 1);
                                                        if($isInserted == 0) {
                                                            $logMessage = "Failed to insert to APILogs.";
                                                            $logger->log($logger->logdate, " [REGISTERMEMBER ERROR] ", $logMessage);
                                                        }
                                                        
                                                    }
                                                }
                                            } else {
                                                $message = "Failed to send SMS: Error on logging event in database.";
                                                $logger->log($logger->logdate,"[REGISTERMEMBER ERROR] ", $message);
                                                $apiDetails = "REGISTERMEMBER-Failed: Failed to send SMS. Error on logging event in database. [MID = $lastInsertedMID].";
                                                $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, $refID, $apiDetails, '', 2);
                                                if($isInserted == 0) {
                                                    $logMessage = "Failed to insert to APILogs.";
                                                    $logger->log($logger->logdate, " [REGISTERMEMBER ERROR] ", $logMessage);
                                                }
                                                
                                            }
                                        }
                                    } else {
                                        $match = substr($memberInfos["MobileNumber"], 0, 2);
                                        if($match == "09"){
                                            $mncount = count($memberInfos["MobileNumber"]);

                                            if(!$mncount == 11){
                                                 $message = "Failed to send SMS: Invalid Mobile Number.";
                                                 $logger->log($logger->logdate,"[REGISTERMEMBER ERROR] ", $message);
                                                 $apiDetails = "REGISTERMEMBER-Failed: Failed to send SMS. Invalid Mobile Number [MID = $lastInsertedMID].";
                                                 $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, $refID, $apiDetails, '', 2);
                                                 if($isInserted == 0) {
                                                    $logMessage = "Failed to insert to APILogs.";
                                                    $logger->log($logger->logdate, " [REGISTERMEMBER ERROR] ", $logMessage);
                                                 }
                                                 
                                             } else {
                                                $cpNumber = $memberInfos["MobileNumber"];
                                                $mobileno = $this->formatMobileNumber($cpNumber);
                                                $templateid = $ref_SMSApiMethodsModel->getSMSMethodTemplateID(Ref_SMSApiMethodsModel::PLAYER_REGISTRATION);
                                                $templateid = $templateid['SMSTemplateID'];
                                                $methodid = Ref_SMSApiMethodsModel::PLAYER_REGISTRATION;

                                                $smslastinsertedid = $smsRequestLogsModel->insertSMSRequestLogs($methodid, $mobileno, $memberInfos["DateCreated"]);
                                                if($smslastinsertedid != 0 && $smslastinsertedid != ''){

                                                    $trackingid = "SMSR".$smslastinsertedid;
                                                    $apiURL = Yii::app()->params['SMSURI'];   
                                                    $app_id = Yii::app()->params['app_id'];  
                                                    $membershipSMSApi = new MembershipSmsAPI($apiURL, $app_id);
                                                    $smsresult = $membershipSMSApi->sendRegistration($mobileno, $templateid, $memberInfos["DateCreated"], $memberInfos["TemporaryAccountCode"], $trackingid);
                                                  if(isset($smsresult['status'])){
                                                        if($smsresult['status'] != 1){

                                                            $message = "Failed to send SMS.";
                                                            $logger->log($logger->logdate,"[REGISTERMEMBER ERROR] ", $message);
                                                            $apiDetails = "REGISTERMEMBER-Failed: Failed to send SMS. [MID = $lastInsertedMID].";
                                                            $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, $refID, $apiDetails, '', 2);
                                                            if($isInserted == 0) {
                                                                $logMessage = "Failed to insert to APILogs.";
                                                                $logger->log($logger->logdate, " [REGISTERMEMBER ERROR] ", $logMessage);
                                                            }
                                                            
                                                        }
                                                        else {
                                                            $transMsg = "You have successfully registered! An active Temporary Account will be sent to your email address or mobile number, which can be used to start session or credit points in the absence of Membership Card. Please note that your Registered Account and Temporary Account will be activated only after 24 hours.";
                                                            $errorCode = 0;
                                                            Utilities::log("ReturnMessage: " . $transMsg . " ErrorCode: " . $errorCode);
                                                            $this->_sendResponse(200, CJSON::encode(CommonController::retMsgRegisterMember($module, $errorCode, nl2br($transMsg))));
                                                            $logMessage = 'Registration is successful.';
                                                            $logger->log($logger->logdate, " [REGISTERMEMBER SUCCESSFUL] ", $logMessage);
                                                            $apiDetails = 'REGISTERMEMBER-Success: Registration is successful. MID = '.$lastInsertedMID;
                                                            $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, $refID, $apiDetails, '', 1);
                                                            if($isInserted == 0) {
                                                                $logMessage = "Failed to insert to APILogs.";
                                                                $logger->log($logger->logdate, " [REGISTERMEMBER ERROR] ", $logMessage);
                                                            }
                                                            
                                                        }
                                                    }
                                                } else {
                                                    $message = "Failed to send SMS: Error on logging event in database.";
                                                    $logger->log($logger->logdate,"[REGISTRATION ERROR] ", $message);
                                                    $apiDetails = "REGISTERMEMBER-Failed: Failed to send SMS. Error on logging event in database [MID = $lastInsertedMID].";
                                                    $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, $refID, $apiDetails, '', 2);
                                                    if($isInserted == 0) {
                                                        $logMessage = "Failed to insert to APILogs.";
                                                        $logger->log($logger->logdate, " [REGISTERMEMBER ERROR] ", $logMessage);
                                                    }
                                                    
                                                }
                                             }
                                        } else {
                                            $message = "Failed to send SMS: Invalid Mobile Number.";
                                            $logger->log($logger->logdate,"[REGISTRATION ERROR] ", $message);
                                            $apiDetails = "REGISTERMEMBER-Failed: Failed to send SMS. Invalid Mobile Number [MID = $lastInsertedMID].";
                                            $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, $refID, $apiDetails, '', 2);
                                            if($isInserted == 0) {
                                                $logMessage = "Failed to insert to APILogs.";
                                                $logger->log($logger->logdate, " [REGISTERMEMBER ERROR] ", $logMessage);
                                            }
                                            
                                        }
                                    }
                                 
                                $auditTrailModel->logEvent(AuditTrailModel::API_REGISTER_MEMBER, 'Email: '.$emailAddress, array('MID' => $MID, 'SessionID' => $mpSessionID));
                                
                            }
                            else {
                                //check if email is already verified in temp table
                                $tempEmail = $membershipTempModel->checkTempVerifiedEmail($emailAddress);
                                if($tempEmail['COUNT(a.MID)'] > 0) {
                                    $transMsg = "Email is already verified. Please choose a different email address.";
                                    $errorCode = 52;
                                    Utilities::log("ReturnMessage: " . $transMsg . " ErrorCode: " . $errorCode);
                                    $this->_sendResponse(200, CJSON::encode(CommonController::retMsgRegisterMember($module, $errorCode, $transMsg)));
                                    $logMessage = 'Email is already verified. Please choose a different email address.';
                                    $logger->log($logger->logdate, " [REGISTERMEMBER ERROR] ", $logMessage);
                                    $apiDetails = 'REGISTERMEMBER-Failed: Email is already verified. Email = '.$emailAddress;
                                    $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, $refID, $apiDetails, '', 2);
                                    if($isInserted == 0) {
                                        $logMessage = "Failed to insert to APILogs.";
                                        $logger->log($logger->logdate, " [REGISTERMEMBER ERROR] ", $logMessage);
                                    }
                                    exit;
                                }
                                else {
                                    $lastInsertedMID = $membershipTempModel->register($emailAddress, $firstname, $middlename, $lastname, $nickname, $password, $permanentAddress, $mobileNumber, $alternateMobileNumber, $alternateEmail, $idNumber, $idPresented, $gender, $referralCode, $birthdate, $occupationID, $nationalityID, $isSmoker, $emailSubscription, $smsSubscription, $referrerID);
                                    
                                    if($lastInsertedMID > 0) {
                                        
                                        
                                            $ID = 0;
                                            $mpSessionID = '';

                                            $memberInfos = $membershipTempModel->getTempMemberInfoForSMS($lastInsertedMID);

                                            //match to 09 or 639 in mobile number
                                            $match = substr($memberInfos['MobileNumber'], 0, 3);
                                            if($match == "639"){
                                                $mncount = count($memberInfos["MobileNumber"]);
                                                if(!$mncount == 12){
                                                    $message = "Failed to send SMS. Invalid Mobile Number.";
                                                    $logger->log($logger->logdate,"[REGISTERMEMBER ERROR] ", $message);
                                                    $apiDetails = "REGISTERMEMBER-Failed: Failed to send SMS. Invalid Mobile Number [MID = $lastInsertedMID].";
                                                    $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, $refID, $apiDetails, '', 2);
                                                    if($isInserted == 0) {
                                                        $logMessage = "Failed to insert to APILogs.";
                                                        $logger->log($logger->logdate, " [REGISTERMEMBER ERROR] ", $logMessage);
                                                    }
                                                    
                                                } else {
                                                    $templateid = $ref_SMSApiMethodsModel->getSMSMethodTemplateID(Ref_SMSApiMethodsModel::PLAYER_REGISTRATION);
                                                    $methodid = Ref_SMSApiMethodsModel::PLAYER_REGISTRATION;
                                                    $mobileno = $memberInfos["MobileNumber"];
                                                    $smslastinsertedid = $smsRequestLogsModel->insertSMSRequestLogs($methodid, $mobileno, $memberInfos["DateCreated"]);
                                                    if($smslastinsertedid != 0 && $smslastinsertedid != ''){
                                                        $trackingid = "SMSR".$smslastinsertedid;
                                                        $apiURL = Yii::app()->params["SMSURI"];    
                                                        $app_id = Yii::app()->params["app_id"];    
                                                        $membershipSMSApi = new MembershipSmsAPI($apiURL, $app_id);
                                                        $smsresult = $membershipSMSApi->sendRegistration($mobileno, $templateid['SMSTemplateID'], $memberInfos["DateCreated"], $memberInfos["TemporaryAccountCode"], $trackingid);
                                                        if(isset($smsresult['status'])){
                                                            if($smsresult['status'] != 1){
                                                                $message = "Failed to send SMS.";
                                                                $logger->log($logger->logdate,"[REGISTERMEMBER ERROR] ", $message);
                                                                $apiDetails = "REGISTERMEMBER-Failed: Failed to send SMS. [MID = $lastInsertedMID].";
                                                                $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, $refID, $apiDetails, '', 2);
                                                                if($isInserted == 0) {
                                                                    $logMessage = "Failed to insert to APILogs.";
                                                                    $logger->log($logger->logdate, " [REGISTERMEMBER ERROR] ", $logMessage);
                                                                }
                                                                
                                                            }
                                                            else {
                                                                $transMsg = "You have successfully registered! An active Temporary Account will be sent to your email address or mobile number, which can be used to start session or credit points in the absence of Membership Card. Please note that your Registered Account and Temporary Account will be activated only after 24 hours.";
                                                                $errorCode = 0;
                                                                Utilities::log("ReturnMessage: " . $transMsg . " ErrorCode: " . $errorCode);
                                                                $this->_sendResponse(200, CJSON::encode(CommonController::retMsgRegisterMember($module, $errorCode, nl2br($transMsg))));
                                                                $logMessage = 'Registration is successful.';
                                                                $logger->log($logger->logdate, " [REGISTERMEMBER SUCCESSFUL] ", $logMessage);
                                                                $apiDetails = 'REGISTERMEMBER-Success: Registration is successful. MID = '.$lastInsertedMID;
                                                                $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, $refID, $apiDetails, '', 1);
                                                                if($isInserted == 0) {
                                                                    $logMessage = "Failed to insert to APILogs.";
                                                                    $logger->log($logger->logdate, " [REGISTERMEMBER ERROR] ", $logMessage);
                                                                }
                                                                
                                                            }
                                                        }
                                                    } else {
                                                        $message = "Failed to send SMS: Error on logging event in database.";
                                                        $logger->log($logger->logdate,"[REGISTERMEMBER ERROR] ", $message);
                                                        $apiDetails = "REGISTERMEMBER-Failed: Failed to send SMS. Error on logging event in database. [MID = $lastInsertedMID].";
                                                        $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, $refID, $apiDetails, '', 2);
                                                        if($isInserted == 0) {
                                                            $logMessage = "Failed to insert to APILogs.";
                                                            $logger->log($logger->logdate, " [REGISTERMEMBER ERROR] ", $logMessage);
                                                        }
                                                        
                                                    }
                                                }
                                            } else {
                                                $match = substr($memberInfos["MobileNumber"], 0, 2);
                                                if($match == "09"){
                                                    $mncount = count($memberInfos["MobileNumber"]);
                                                    if(!$mncount == 11){
                                                         $message = "Failed to send SMS: Invalid Mobile Number.";
                                                         $logger->log($logger->logdate,"[REGISTERMEMBER ERROR] ", $message);
                                                         $apiDetails = "REGISTERMEMBER-Failed: Failed to send SMS. Invalid Mobile Number [MID = $lastInsertedMID].";
                                                         $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, $refID, $apiDetails, '', 2);
                                                         if($isInserted == 0) {
                                                            $logMessage = "Failed to insert to APILogs.";
                                                            $logger->log($logger->logdate, " [REGISTERMEMBER ERROR] ", $logMessage);
                                                         }
                                                         
                                                     } else {
                                                        $mobileno = str_replace("09", "639", $memberInfos["MobileNumber"]);
                                                        $templateid = $ref_SMSApiMethodsModel->getSMSMethodTemplateID(Ref_SMSApiMethodsModel::PLAYER_REGISTRATION);
                                                        $methodid = Ref_SMSApiMethodsModel::PLAYER_REGISTRATION;
                                                        $smslastinsertedid = $smsRequestLogsModel->insertSMSRequestLogs($methodid, $mobileno, $memberInfos["DateCreated"]);
                                                        if($smslastinsertedid != 0 && $smslastinsertedid != ''){
                                                            $trackingid = "SMSR".$smslastinsertedid;
                                                            $apiURL = Yii::app()->params['SMSURI'];   
                                                            $app_id = Yii::app()->params['app_id'];  
                                                            $membershipSMSApi = new MembershipSmsAPI($apiURL, $app_id);
                                                            $smsresult = $membershipSMSApi->sendRegistration($mobileno, $templateid['SMSTemplateID'], $memberInfos["DateCreated"], $memberInfos["TemporaryAccountCode"], $trackingid);
                                                            if(isset($smsresult['status'])){
                                                                if($smsresult['status'] != 1){
                                                                    $message = "Failed to send SMS.";
                                                                    $logger->log($logger->logdate,"[REGISTERMEMBER ERROR] ", $message);
                                                                    $apiDetails = "REGISTERMEMBER-Failed: Failed to send SMS. [MID = $lastInsertedMID].";
                                                                    $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, $refID, $apiDetails, '', 2);
                                                                    if($isInserted == 0) {
                                                                        $logMessage = "Failed to insert to APILogs.";
                                                                        $logger->log($logger->logdate, " [REGISTERMEMBER ERROR] ", $logMessage);
                                                                    }
                                                                    
                                                                }
                                                                else {
                                                                    $transMsg = "You have successfully registered! An active Temporary Account will be sent to your email address or mobile number, which can be used to start session or credit points in the absence of Membership Card. Please note that your Registered Account and Temporary Account will be activated only after 24 hours.";
                                                                    $errorCode = 0;
                                                                    Utilities::log("ReturnMessage: " . $transMsg . " ErrorCode: " . $errorCode);
                                                                    $this->_sendResponse(200, CJSON::encode(CommonController::retMsgRegisterMember($module, $errorCode, nl2br($transMsg))));
                                                                    $logMessage = 'Registration is successful.';
                                                                    $logger->log($logger->logdate, " [REGISTERMEMBER SUCCESSFUL] ", $logMessage);
                                                                    $apiDetails = 'REGISTERMEMBER-Success: Registration is successful. MID = '.$lastInsertedMID;
                                                                    $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, $refID, $apiDetails, '', 1);
                                                                    if($isInserted == 0) {
                                                                        $logMessage = "Failed to insert to APILogs.";
                                                                        $logger->log($logger->logdate, " [REGISTERMEMBER ERROR] ", $logMessage);
                                                                    }
                                                                    
                                                                }
                                                            }
                                                        } else {
                                                            $message = "Failed to send SMS: Error on logging event in database.";
                                                            $logger->log($logger->logdate,"[REGISTRATION ERROR] ", $message);
                                                            $apiDetails = "REGISTERMEMBER-Failed: Failed to send SMS. Error on logging event in database [MID = $lastInsertedMID].";
                                                            $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, $refID, $apiDetails, '', 2);
                                                            if($isInserted == 0) {
                                                                $logMessage = "Failed to insert to APILogs.";
                                                                $logger->log($logger->logdate, " [REGISTERMEMBER ERROR] ", $logMessage);
                                                            }
                                                            
                                                        }
                                                     }
                                                } else {
                                                    $message = "Failed to send SMS: Invalid Mobile Number.";
                                                    $logger->log($logger->logdate,"[REGISTRATION ERROR] ", $message);
                                                    $apiDetails = "REGISTERMEMBER-Failed: Failed to send SMS. Invalid Mobile Number [MID = $lastInsertedMID].";
                                                    $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, $refID, $apiDetails, '', 2);
                                                    if($isInserted == 0) {
                                                        $logMessage = "Failed to insert to APILogs.";
                                                        $logger->log($logger->logdate, " [REGISTERMEMBER ERROR] ", $logMessage);
                                                    }
                                                    
                                                }
                                            }
                                        

                                        $auditTrailModel->logEvent(AuditTrailModel::API_REGISTER_MEMBER, 'Email: '.$emailAddress, array('ID' => $ID));
                                        
                                        
                                    }
                                    else {
                                        if(strpos($lastInsertedMID, " Integrity constraint violation: 1062 Duplicate entry") > 0) {
                                            $transMsg = "Sorry, " . $emailAddress . "already belongs to an existing account. Please enter another email address.";
                                            $errorCode = 21;
                                            Utilities::log("ReturnMessage: " . $transMsg . " ErrorCode: " . $errorCode);
                                            $this->_sendResponse(200, CJSON::encode(CommonController::retMsgRegisterMember($module, $errorCode, $transMsg)));
                                            $logMessage = "Sorry, " . $emailAddress . "already belongs to an existing account. Please enter another email address.";
                                            $logger->log($logger->logdate, " [REGISTERMEMBER ERROR] ", $logMessage);
                                            $apiDetails = 'REGISTERMEMBER-Failed: Email already exists. Please choose a different email address. Email = '.$emailAddress;
                                            $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, $refID, $apiDetails, '', 2);
                                            if($isInserted == 0) {
                                                $logMessage = "Failed to insert to APILogs.";
                                                $logger->log($logger->logdate, " [REGISTERMEMBER ERROR] ", $logMessage);
                                            }
                                            exit;
                                        }
                                        else {
                                            $transMsg = "Registration failed.";
                                            $errorCode = 53;
                                            Utilities::log("ReturnMessage: " . $transMsg . " ErrorCode: " . $errorCode);
                                            $this->_sendResponse(200, CJSON::encode(CommonController::retMsgRegisterMember($module, $errorCode, $transMsg)));
                                            $logMessage = "Registration failed.";
                                            $logger->log($logger->logdate, " [REGISTERMEMBER ERROR] ", $logMessage);
                                            $apiDetails = 'REGISTERMEMBER-Failed: Registration failed.';
                                            $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, $refID, $apiDetails, '', 2);
                                            if($isInserted == 0) {
                                                $logMessage = "Failed to insert to APILogs.";
                                                $logger->log($logger->logdate, " [REGISTERMEMBER ERROR] ", $logMessage);
                                            }
                                            exit;
                                        }
                                    }

                                }
                            }
                            
                        }
                    }
            }
        }
        else {
            $transMsg = "One or more fields is not set or is blank.";
            $errorCode = 1;
            Utilities::log("ReturnMessage: " . $transMsg . " ErrorCode: " . $errorCode);
            $this->_sendResponse(200, CJSON::encode(CommonController::retMsgRegisterMember($module, $errorCode, $transMsg)));
            $logMessage = 'One or more fields is not set or is blank.';
            $logger->log($logger->logdate, " [REGISTERMEMBER ERROR] ", $logMessage);
            $apiDetails = 'REGISTERMEMBER-Failed: Invalid input parameters.';
            $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, '', $apiDetails, '', 2);
            if($isInserted == 0) {
                $logMessage = "Failed to insert to APILogs.";
                $logger->log($logger->logdate, " [REGISTERMEMBER ERROR] ", $logMessage);
            }
            exit;
        }
    }
    
    private function formatMobileNumber($cpNumber){
       return str_replace("09", "639", substr($cpNumber,0,2)).substr($cpNumber,2,  strlen($cpNumber));
    }
    
    public function actionUpdateProfile() {
        $request = $this->_readJsonRequest();
        
        $transMsg = '';
        $errorCode = '';
        $module = 'UpdateProfile';
        $apiMethod = 4;
        
        $logger = new ErrorLogger();
        $apiLogsModel = new APILogsModel();
        
        if(isset($request['FirstName']) && isset($request['LastName']) && isset($request['MobileNo'])
        && isset($request['EmailAddress']) && isset($request['IDNumber']) && isset($request['Birthdate']) && isset($request['MPSessionID'])) {
            if(($request['FirstName'] == '') || ($request['LastName'] == '') || ($request['MobileNo'] == '')|| ($request['EmailAddress'] == '')
            || ($request['IDNumber'] == '') || ($request['Birthdate'] == '') || ($request['MPSessionID'] == '')) {
                $transMsg = "One or more fields is not set or is blank.";
                $errorCode = 1;
                Utilities::log("ReturnMessage: " . $transMsg . " ErrorCode: " . $errorCode);
                $this->_sendResponse(200, CJSON::encode(CommonController::retMsgUpdateProfile($module, $errorCode, $transMsg)));
                $logMessage = 'One or more fields is not set or is blank.';
                $logger->log($logger->logdate, " [UPDATEPROFILE ERROR] ", $logMessage);
                $apiDetails = 'UPDATEPROFILE-Failed: Invalid input parameters. ';
                $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, '', $apiDetails, '', 2);
                if($isInserted == 0) {
                    $logMessage = "Failed to insert to APILogs.";
                    $logger->log($logger->logdate, " [UPDATEPROFILE ERROR] ", $logMessage);
                }
                exit;
            }
            else if(strlen($request['FirstName']) < 2 || ($request['MiddleName'] != '' && strlen($request['MiddleName']) < 2) || strlen($request['LastName']) < 2) {
                $transMsg = "Name should not be less than 2 characters long.";
                $errorCode = 14;
                Utilities::log("ReturnMessage: " . $transMsg . " ErrorCode: " . $errorCode);
                $this->_sendResponse(200, CJSON::encode(CommonController::retMsgUpdateProfile($module, $errorCode, $transMsg)));
                $logMessage = 'Name should not be less than 2 characters long.';
                $logger->log($logger->logdate, " [UPDATEPROFILE ERROR] ", $logMessage);
                $apiDetails = 'UPDATEPROFILE-Failed: Invalid input parameters. ';
                $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, '', $apiDetails, '', 2);
                if($isInserted == 0) {
                    $logMessage = "Failed to insert to APILogs.";
                    $logger->log($logger->logdate, " [UPDATEPROFILE ERROR] ", $logMessage);
                }
                exit;
            }
            else if(ctype_alpha($request['FirstName']) == FALSE || ctype_alpha($request['MiddleName']) == FALSE ||
                    ctype_alpha($request['LastName']) == FALSE || ctype_alpha($request['NickName']) == FALSE) {
                $transMsg = "Name should consist of letters only.";
                $errorCode = 17;
                Utilities::log("ReturnMessage: " . $transMsg . " ErrorCode: " . $errorCode);
                $this->_sendResponse(200, CJSON::encode(CommonController::retMsgUpdateProfile($module, $errorCode, $transMsg)));
                $logMessage = 'Name should consist of letters only.';
                $logger->log($logger->logdate, " [UPDATEPROFILE ERROR] ", $logMessage);
                $apiDetails = 'UPDATEPROFILE-Failed: Invalid input parameters. ';
                $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, '', $apiDetails, '', 2);
                if($isInserted == 0) {
                    $logMessage = "Failed to insert to APILogs.";
                    $logger->log($logger->logdate, " [UPDATEPROFILE ERROR] ", $logMessage);
                }
                exit;
            }
            else if(ctype_alnum($request['Password']) == FALSE || ctype_alnum($request['IDNumber']) == FALSE ) {
                $transMsg = "Password and ID Number should consist of letters and numbers only.";
                $errorCode = 18;
                Utilities::log("ReturnMessage: " . $transMsg . " ErrorCode: " . $errorCode);
                $this->_sendResponse(200, CJSON::encode(CommonController::retMsgUpdateProfile($module, $errorCode, $transMsg)));
                $logMessage = 'Password and ID Number should consist of letters and numbers only.';
                $logger->log($logger->logdate, " [UPDATEPROFILE ERROR] ", $logMessage);
                $apiDetails = 'UPDATEPROFILE-Failed: Invalid input parameters. ';
                $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, '', $apiDetails, '', 2);
                if($isInserted == 0) {
                    $logMessage = "Failed to insert to APILogs.";
                    $logger->log($logger->logdate, " [UPDATEPROFILE ERROR] ", $logMessage);
                }
                exit;
            }
            else if($request['Password'] != '' && strlen($request['Password']) < 5) {
                $transMsg = "Password should not be less than 5 characters long.";
                $errorCode = 19;
                Utilities::log("ReturnMessage: " . $transMsg . " ErrorCode: " . $errorCode);
                $this->_sendResponse(200, CJSON::encode(CommonController::retMsgUpdateProfile($module, $errorCode, $transMsg)));
                $logMessage = 'Password should not be less than 5 characters long.';
                $logger->log($logger->logdate, " [UPDATEPROFILE ERROR] ", $logMessage);
                $apiDetails = 'UPDATEPROFILE-Failed: Invalid input parameters. ';
                $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, '', $apiDetails, '', 2);
                if($isInserted == 0) {
                    $logMessage = "Failed to insert to APILogs.";
                    $logger->log($logger->logdate, " [UPDATEPROFILE ERROR] ", $logMessage);
                }
                exit;
            }
            else if(strlen($request['MobileNo']) < 9 || ($request['AlternateMobileNo'] != '' && strlen($request['AlternateMobileNo']) < 9)) {
                $transMsg = "Mobile number should not be less than 9 digits long.";
                $errorCode = 15;
                Utilities::log("ReturnMessage: " . $transMsg . " ErrorCode: " . $errorCode);
                $this->_sendResponse(200, CJSON::encode(CommonController::retMsgUpdateProfile($module, $errorCode, $transMsg)));
                $logMessage = 'Mobile number should not be less than 9 digits long.';
                $logger->log($logger->logdate, " [UPDATEPROFILE ERROR] ", $logMessage);
                $apiDetails = 'UPDATEPROFILE-Failed: Invalid input parameters. ';
                $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, '', $apiDetails, '', 2);
                if($isInserted == 0) {
                    $logMessage = "Failed to insert to APILogs.";
                    $logger->log($logger->logdate, " [UPDATEPROFILE ERROR] ", $logMessage);
                }
                exit;
            }
            else if((substr($request['MobileNo'],0, 2) != '09' && substr($request['MobileNo'],0, 3) != '639') && (substr($request['AlternateMobileNo'],0, 2) != '09' && substr($request['AlternateMobileNo'],0, 3) != '639') ) {
                $transMsg = "Mobile number should begin with either '09' or '639'.";
                $errorCode = 69;
                Utilities::log("ReturnMessage: " . $transMsg . " ErrorCode: " . $errorCode);
                $this->_sendResponse(200, CJSON::encode(CommonController::retMsgUpdateProfile($module, $errorCode, $transMsg)));
                $logMessage = "Mobile number should begin with either '09' or '639'.";
                $logger->log($logger->logdate, " [UPDATEPROFILE ERROR] ", $logMessage);
                $apiDetails = 'UPDATEPROFILE-Failed: Invalid input parameters. ';
                $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, '', $apiDetails, '', 2);
                if($isInserted == 0) {
                    $logMessage = "Failed to insert to APILogs.";
                    $logger->log($logger->logdate, " [UPDATEPROFILE ERROR] ", $logMessage);
                }
                exit;
            }
            else if(!is_numeric($request['MobileNo']) || !is_numeric($request['AlternateMobileNo'])) {
                $transMsg = "Mobile number should consist of numbers only.";
                $errorCode = 16;
                Utilities::log("ReturnMessage: " . $transMsg . " ErrorCode: " . $errorCode);
                $this->_sendResponse(200, CJSON::encode(CommonController::retMsgUpdateProfile($module, $errorCode, $transMsg)));
                $logMessage = 'Mobile number should consist of numbers only.';
                $logger->log($logger->logdate, " [UPDATEPROFILE ERROR] ", $logMessage);
                $apiDetails = 'UPDATEPROFILE-Failed: Invalid input parameters. ';
                $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, '', $apiDetails, '', 2);
                if($isInserted == 0) {
                    $logMessage = "Failed to insert to APILogs.";
                    $logger->log($logger->logdate, " [UPDATEPROFILE ERROR] ", $logMessage);
                }
                exit;
            }
            else if($request['MobileNo'] == $request['AlternateMobileNo']) {
                $transMsg = "Mobile number should not be the same as alternate mobile number.";
                $errorCode = 86;
                Utilities::log("ReturnMessage: " . $transMsg . " ErrorCode: " . $errorCode);
                $this->_sendResponse(200, CJSON::encode(CommonController::retMsgUpdateProfile($module, $errorCode, $transMsg)));
                $logMessage = 'Mobile number should not be the same as alternate mobile number.';
                $logger->log($logger->logdate, " [UPDATEPROFILE ERROR] ", $logMessage);
                $apiDetails = 'UPDATEPROFILE-Failed: Invalid input parameters. ';
                $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, '', $apiDetails, '', 2);
                if($isInserted == 0) {
                    $logMessage = "Failed to insert to APILogs.";
                    $logger->log($logger->logdate, " [UPDATEPROFILE ERROR] ", $logMessage);
                }
                exit;
            }
            else if(!Utilities::validateEmail($request['EmailAddress']) || !Utilities::validateEmail($request['AlternateEmail']) ) {
                $transMsg = "Invalid Email Address.";
                $errorCode = 5;
                Utilities::log("ReturnMessage: " . $transMsg . " ErrorCode: " . $errorCode);
                $this->_sendResponse(200, CJSON::encode(CommonController::retMsgUpdateProfile($module, $errorCode, $transMsg)));
                $logMessage = 'Invalid Email Address.';
                $logger->log($logger->logdate, " [UPDATEPROFILE ERROR] ", $logMessage);
                $apiDetails = 'UPDATEPROFILE-Failed: Invalid input parameters. ';
                $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, '', $apiDetails, '', 2);
                if($isInserted == 0) {
                    $logMessage = "Failed to insert to APILogs.";
                    $logger->log($logger->logdate, " [UPDATEPROFILE ERROR] ", $logMessage);
                }
                exit;
            }
            else if($request['EmailAddress'] == $request['AlternateEmail']) {
                $transMsg = "Email Address should not be the same as alternate email.";
                $errorCode = 87;
                Utilities::log("ReturnMessage: " . $transMsg . " ErrorCode: " . $errorCode);
                $this->_sendResponse(200, CJSON::encode(CommonController::retMsgUpdateProfile($module, $errorCode, $transMsg)));
                $logMessage = 'Email Address should not be the same as alternate email.';
                $logger->log($logger->logdate, " [UPDATEPROFILE ERROR] ", $logMessage);
                $apiDetails = 'UPDATEPROFILE-Failed: Invalid input parameters. ';
                $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, '', $apiDetails, '', 2);
                if($isInserted == 0) {
                    $logMessage = "Failed to insert to APILogs.";
                    $logger->log($logger->logdate, " [UPDATEPROFILE ERROR] ", $logMessage);
                }
                exit;
            }
            else if($request['Gender'] != 1 && $request['Gender'] != 2) {
                $transMsg = "Please input 1 for male or 2 for female.";
                $errorCode = 77;
                Utilities::log("ReturnMessage: " . $transMsg . " ErrorCode: " . $errorCode);
                $this->_sendResponse(200, CJSON::encode(CommonController::retMsgUpdateProfile($module, $errorCode, $transMsg)));
                $logMessage = 'Please input 1 for male or 2 for female.';
                $logger->log($logger->logdate, " [UPDATEPROFILE ERROR] ", $logMessage);
                $apiDetails = 'UPDATEPROFILE-Failed: Invalid input parameters. ';
                $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, '', $apiDetails, '', 2);
                if($isInserted == 0) {
                    $logMessage = "Failed to insert to APILogs.";
                    $logger->log($logger->logdate, " [UPDATEPROFILE ERROR] ", $logMessage);
                }
                exit;
            }
            else if($request['IDPresented'] != 1 && $request['IDPresented'] != 2 && $request['IDPresented'] != 3 && $request['IDPresented'] != 4 && $request['IDPresented'] != 5 && $request['IDPresented'] != 6 && $request['IDPresented'] != 7 && $request['IDPresented'] != 8 && $request['IDPresented'] != 9) {
                $transMsg = "Please input a valid ID Presented (1 to 9).";
                $errorCode = 78;
                Utilities::log("ReturnMessage: " . $transMsg . " ErrorCode: " . $errorCode);
                $this->_sendResponse(200, CJSON::encode(CommonController::retMsgUpdateProfile($module, $errorCode, $transMsg)));
                $logMessage = 'Please input a valid ID Presented (1 to 9).';
                $logger->log($logger->logdate, " [UPDATEPROFILE ERROR] ", $logMessage);
                $apiDetails = 'UPDATEPROFILE-Failed: Invalid input parameters. ';
                $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, '', $apiDetails, '', 2);
                if($isInserted == 0) {
                    $logMessage = "Failed to insert to APILogs.";
                    $logger->log($logger->logdate, " [UPDATEPROFILE ERROR] ", $logMessage);
                }
                exit;
            }
            else if($request['Nationality'] != 1 && $request['Nationality'] != 2 && $request['Nationality'] != 3 && $request['Nationality'] != 4 && $request['Nationality'] != 5 && $request['Nationality'] != 6) {
                $transMsg = "Please input a valid Nationality (1 to 6).";
                $errorCode = 79;
                Utilities::log("ReturnMessage: " . $transMsg . " ErrorCode: " . $errorCode);
                $this->_sendResponse(200, CJSON::encode(CommonController::retMsgUpdateProfile($module, $errorCode, $transMsg)));
                $logMessage = 'Please input a valid Nationality (1 to 6).';
                $logger->log($logger->logdate, " [UPDATEPROFILE ERROR] ", $logMessage);
                $apiDetails = 'UPDATEPROFILE-Failed: Invalid input parameters. ';
                $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, '', $apiDetails, '', 2);
                if($isInserted == 0) {
                    $logMessage = "Failed to insert to APILogs.";
                    $logger->log($logger->logdate, " [UPDATEPROFILE ERROR] ", $logMessage);
                }
                exit;
            }
            else if($request['Occupation'] != 1 && $request['Occupation'] != 2 && $request['Occupation'] != 3 && $request['Occupation'] != 4 && $request['Occupation'] != 5) {
                $transMsg = "Please input a valid Occupation (1 to 5).";
                $errorCode = 81;
                Utilities::log("ReturnMessage: " . $transMsg . " ErrorCode: " . $errorCode);
                $this->_sendResponse(200, CJSON::encode(CommonController::retMsgUpdateProfile($module, $errorCode, $transMsg)));
                $logMessage = 'Please input a valid Occupation (1 to 5).';
                $logger->log($logger->logdate, " [UPDATEPROFILE ERROR] ", $logMessage);
                $apiDetails = 'UPDATEPROFILE-Failed: Invalid input parameters. ';
                $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, '', $apiDetails, '', 2);
                if($isInserted == 0) {
                    $logMessage = "Failed to insert to APILogs.";
                    $logger->log($logger->logdate, " [UPDATEPROFILE ERROR] ", $logMessage);
                }
                exit;
            }
            else if($request['IsSmoker'] != 1 && $request['IsSmoker'] != 2) {
                $transMsg = "Please input 1 for smoker or 2 for non-smoker.";
                $errorCode = 82;
                Utilities::log("ReturnMessage: " . $transMsg . " ErrorCode: " . $errorCode);
                $this->_sendResponse(200, CJSON::encode(CommonController::retMsgUpdateProfile($module, $errorCode, $transMsg)));
                $logMessage = 'Please input 1 for smoker or 2 for non-smoker.';
                $logger->log($logger->logdate, " [UPDATEPROFILE ERROR] ", $logMessage);
                $apiDetails = 'UPDATEPROFILE-Failed: Invalid input parameters. ';
                $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, '', $apiDetails, '', 2);
                if($isInserted == 0) {
                    $logMessage = "Failed to insert to APILogs.";
                    $logger->log($logger->logdate, " [UPDATEPROFILE ERROR] ", $logMessage);
                }
                exit;
            }
            else if($this->_isRealDate($request['Birthdate']) == FALSE) {
                $transMsg = "Please input a valid Date (YYYY-MM-DD).";
                $errorCode = 80;
                Utilities::log("ReturnMessage: " . $transMsg . " ErrorCode: " . $errorCode);
                $this->_sendResponse(200, CJSON::encode(CommonController::retMsgUpdateProfile($module, $errorCode, $transMsg)));
                $logMessage = 'Please input a valid Date.';
                $logger->log($logger->logdate, " [UPDATEPROFILE ERROR] ", $logMessage);
                $apiDetails = 'UPDATEPROFILE-Failed: Invalid input parameters. ';
                $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, '', $apiDetails, '', 2);
                if($isInserted == 0) {
                    $logMessage = "Failed to insert to APILogs.";
                    $logger->log($logger->logdate, " [UPDATEPROFILE ERROR] ", $logMessage);
                }
                exit;   
            }
            else {
                
                //start of declaration of models to be used
                $memberInfoModel = new MemberInfoModel();
                $memberCardsModel = new MemberCardsModel();
                $memberSessionsModel = new MemberSessionsModel();
                $membershipTempModel = new MembershipTempModel();
                $membersModel = new MembersModel();
                $logger = new ErrorLogger();
                $auditTrailModel = new AuditTrailModel();
                
//                if(isset($session['SessionID'])) {
//                    $mpSessionID = $session['SessionID'];
//                }
//                else {
//                    $mpSessionID = 0;
//                }
//                
//                if(isset($session['MID'])) {
//                    $MID = $session['MID'];
//                }
//                else {
//                    $MID = 0;
//                }
//                
//                if(isset($session['Email'])) {
//                    $email = $session['Email'];
//                }
//                else {
//                    $email = null;
//                }
                          
                
                $emailAddress = trim($request['EmailAddress']);
                $firstname = trim($request['FirstName']);
                $middlename = trim($request['MiddleName']);
                $lastname = trim($request['LastName']);
                $nickname = trim($request['NickName']);
                $password = md5(trim($request['Password']));
                $permanentAddress = trim($request['PermanentAdd']);
                $mobileNumber = trim($request['MobileNo']);
                $alternateMobileNumber = trim($request['AlternateMobileNo']);

                $alternateEmail = trim($request['AlternateEmail']);
                $idNumber = trim($request['IDNumber']);
                $idPresented = trim($request['IDPresented']);
                $gender = trim($request['Gender']);
                $birthdate = trim($request['Birthdate']);
                $nationalityID = trim($request['Nationality']);
                $occupationID = trim($request['Occupation']);
                $isSmoker = trim($request['IsSmoker']);
                $mpSessionID = trim($request['MPSessionID']);
                
                $memberSessions = $memberSessionsModel->getMID($mpSessionID);
                          
                if($memberSessions)
                    $MID = $memberSessions['MID'];
                else {
                    $transMsg = "MPSessionID does not exist.";
                    $errorCode = 13;
                    Utilities::log("ReturnMessage: " . $transMsg . " ErrorCode: " . $errorCode);
                    $this->_sendResponse(200, CJSON::encode(CommonController::retMsgUpdateProfile($module, $errorCode, $transMsg)));
                    $logMessage = 'MPSessionID does not exist.';
                    $logger->log($logger->logdate, " [UPDATEPROFILE ERROR] ", $logMessage);
                    $apiDetails = 'UPDATEPROFILE-Failed: There is no active session. MID = '.$MID;
                    $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, '', $apiDetails, '', 2);
                    if($isInserted == 0) {
                        $logMessage = "Failed to insert to APILogs.";
                        $logger->log($logger->logdate, " [UPDATEPROFILE ERROR] ", $logMessage);
                    }
                    exit; 
                }
                
                $isExist = $memberSessionsModel->checkIfSessionExist($MID, $mpSessionID);
                
                if(count($isExist) > 0) {
                    //check if from old to newly migrated card
                    if(!is_null($emailAddress)) {
                        $tempMID = $membershipTempModel->getMID($emailAddress);
                        if(empty($tempMID)) {
                            $tempMID['MID'] = 0;
                            $mid = 0;
                        }    
                        else
                            $mid = $tempMID['MID'];
                    }
                    else
                        $mid = $MID;
                    
                    
                    
                    $tempHasEmailCount = $membershipTempModel->checkIfEmailExistsWithMID($mid, $emailAddress);
                    if(is_null($tempHasEmailCount))
                        $tempHasEmailCount = 0;      
                    else
                        $tempHasEmailCount = $tempHasEmailCount['COUNT'];
                    
                    
                    $hasEmailCount = $memberInfoModel->checkIfEmailExistsWithMID($MID, $emailAddress);
                    if(is_null($hasEmailCount))
                        $hasEmailCount = 0;
                    else
                        $hasEmailCount = $hasEmailCount['COUNT'];
                    
                    if(($tempHasEmailCount > 0) || ($hasEmailCount > 0)) {
                        $transMsg = "Sorry, " . $emailAddress . " already belongs to an existing account. Please enter another email address.";
                        $errorCode = 21;
                        Utilities::log("ReturnMessage: " . $transMsg . " ErrorCode: " . $errorCode);
                        $this->_sendResponse(200, CJSON::encode(CommonController::retMsgUpdateProfile($module, $errorCode, $transMsg)));
                        $logMessage = 'Sorry, ' . $emailAddress . ' already belongs to an existing account. Please enter another email address.';
                        $logger->log($logger->logdate, " [UPDATEPROFILE ERROR] ", $logMessage);
                        $apiDetails = 'UPDATEPROFILE-Failed: Email is already used. ';
                        $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, '', $apiDetails, '', 2);
                        if($isInserted == 0) {
                            $logMessage = "Failed to insert to APILogs.";
                            $logger->log($logger->logdate, " [UPDATEPROFILE ERROR] ", $logMessage);
                        }
                        exit;
                    }
                    else {
                        $refID = $firstname.' '.$lastname;
                        //proceed with the updating of member profile
                        $result = $memberInfoModel->updateProfile($firstname, $middlename, $lastname, $nickname, $mid, $permanentAddress, $mobileNumber, $alternateMobileNumber, $emailAddress, $alternateEmail, $birthdate, $nationalityID, $occupationID, $idNumber, $idPresented, $gender, $isSmoker);
                        
                        if($result > 0) {
                            $result2 = $membersModel->updateMemberUsername($mid, $emailAddress, $password);
                            
                            if($result2 > 0) {
                                $result3 = $membershipTempModel->updateTempEmail($MID, $emailAddress);
                                
                                if($result3 > 0) {
                                    $result4 = $membershipTempModel->updateTempMemberUsername($MID, $emailAddress, $password);
                                    
                                    if($result4 > 0) {
                                        $isSuccessful = $auditTrailModel->logEvent(AuditTrailModel::API_UPDATE_PROFILE, 'Email: '.$emailAddress, array('MID' => $MID, 'SessionID' => $mpSessionID));
                                        if($isSuccessful == 0) {
                                            $logMessage = "Failed to insert to Audittrail.";
                                            $logger->log($logger->logdate, " [UPDATEPROFILE ERROR] ", $logMessage);
                                        }
                                        
                                        $result5 = $memberInfoModel->updateProfileDateUpdated($MID, $mid);
                                        $result6 = $membershipTempModel->updateTempProfileDateUpdated($MID, $mid);
                                        
                                        if($result5 > 0 && $result6 > 0) {
                                            $transMsg = 'No Error, Transaction successful.';
                                            $errorCode = 0;
                                            $this->_sendResponse(200, CJSON::encode(CommonController::retMsgUpdateProfile($module, $errorCode, $transMsg)));
                                            $logMessage = 'Update profile successful.';
                                            $logger->log($logger->logdate, " [UPDATEPROFILE SUCCESSFUL] ", $logMessage);
                                            $apiDetails = 'UPDATEPROFILE-UpdateProfile/TempDateUpdated-Success: Username = '.$emailAddress.' MID = '.$MID;
                                            $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, $refID, $apiDetails, '', 1);
                                            if($isInserted == 0) {
                                                $logMessage = "Failed to insert to APILogs.";
                                                $logger->log($logger->logdate, " [UPDATEPROFILE ERROR] ", $logMessage);
                                            }
                                            exit;
                                        }
                                        else {
                                            $transMsg = 'Transaction failed.';
                                            $errorCode = 4;
                                            Utilities::log("ReturnMessage: " . $transMsg . " ErrorCode: " . $errorCode);
                                            $this->_sendResponse(200, CJSON::encode(CommonController::retMsgUpdateProfile($module, $errorCode, $transMsg)));
                                            $logMessage = 'Transaction failed.';
                                            $logger->log($logger->logdate, " [UPDATEPROFILE ERROR] ", $logMessage);
                                            $apiDetails = 'UPDATEPROFILE-UpdateProfile/TempDateUpdated-Failed: Username = '.$emailAddress.' MID = '.$MID;
                                            $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, $refID, $apiDetails, '', 2);
                                            if($isInserted == 0) {
                                                $logMessage = "Failed to insert to APILogs.";
                                                $logger->log($logger->logdate, " [UPDATEPROFILE ERROR] ", $logMessage);
                                            }
                                            exit;
                                         }
                                    }
                                    else {
                                        $transMsg = 'Transaction failed.';
                                        $errorCode = 4;
                                        Utilities::log("ReturnMessage: " . $transMsg . " ErrorCode: " . $errorCode);
                                        $this->_sendResponse(200, CJSON::encode(CommonController::retMsgUpdateProfile($module, $errorCode, $transMsg)));
                                        $logMessage = 'Transaction failed.';
                                        $logger->log($logger->logdate, " [UPDATEPROFILE ERROR] ", $logMessage);
                                        $apiDetails = 'UPDATEPROFILE-UpdateTempMemberUsername-Failed: Username = '.$emailAddress.' MID = '.$MID;
                                        $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, $refID, $apiDetails, '', 2);
                                        if($isInserted == 0) {
                                            $logMessage = "Failed to insert to APILogs.";
                                            $logger->log($logger->logdate, " [UPDATEPROFILE ERROR] ", $logMessage);
                                        }
                                        exit;
                                    }
                                }
                                else {
                                    $transMsg = 'Transaction failed.';
                                    $errorCode = 4;
                                    Utilities::log("ReturnMessage: " . $transMsg . " ErrorCode: " . $errorCode);
                                    $this->_sendResponse(200, CJSON::encode(CommonController::retMsgUpdateProfile($module, $errorCode, $transMsg)));
                                    $logMessage = 'Transaction failed.';
                                    $logger->log($logger->logdate, " [UPDATEPROFILE ERROR] ", $logMessage);
                                    $apiDetails = 'UPDATEPROFILE-UpdateTempEmail-Failed: Username = '.$emailAddress.' MID = '.$MID;
                                    $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, $refID, $apiDetails, '', 2);
                                    if($isInserted == 0) {
                                        $logMessage = "Failed to insert to APILogs.";
                                        $logger->log($logger->logdate, " [UPDATEPROFILE ERROR] ", $logMessage);
                                    }
                                    exit;
                                }
                            }
                            else {
                                $transMsg = 'Transaction failed.';
                                $errorCode = 4;
                                Utilities::log("ReturnMessage: " . $transMsg . " ErrorCode: " . $errorCode);
                                $this->_sendResponse(200, CJSON::encode(CommonController::retMsgUpdateProfile($module, $errorCode, $transMsg)));
                                $logMessage = 'Transaction failed.';
                                $logger->log($logger->logdate, " [UPDATEPROFILE ERROR] ", $logMessage);
                                $apiDetails = 'UPDATEPROFILE-UpdateMemberUsername-Failed: Username = '.$emailAddress.' MID = '.$mid;
                                $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, $refID, $apiDetails, '', 2);
                                if($isInserted == 0) {
                                    $logMessage = "Failed to insert to APILogs.";
                                    $logger->log($logger->logdate, " [UPDATEPROFILE ERROR] ", $logMessage);
                                }
                                exit;
                            }
                        }
                        else {
                            $transMsg = 'Transaction failed.';
                            $errorCode = 4;
                            Utilities::log("ReturnMessage: " . $transMsg . " ErrorCode: " . $errorCode);
                            $this->_sendResponse(200, CJSON::encode(CommonController::retMsgUpdateProfile($module, $errorCode, $transMsg)));
                            $logMessage = 'Transaction failed.';
                            $logger->log($logger->logdate, " [UPDATEPROFILE ERROR] ", $logMessage);
                            $apiDetails = 'UPDATEPROFILE-UpdateProfileMemberInfo-Failed: Name = '.$firstname.' '.$middlename.' '.$lastname.' MID = '.$mid;
                            $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, $refID, $apiDetails, '', 2);
                            if($isInserted == 0) {
                                $logMessage = "Failed to insert to APILogs.";
                                $logger->log($logger->logdate, " [UPDATEPROFILE ERROR] ", $logMessage);
                            }
                            exit;
                       }
                    }
                }
                else {
                    $transMsg = "MPSessionID does not exist.";
                    $errorCode = 13;
                    Utilities::log("ReturnMessage: " . $transMsg . " ErrorCode: " . $errorCode);
                    $this->_sendResponse(200, CJSON::encode(CommonController::retMsgUpdateProfile($module, $errorCode, $transMsg)));
                    $logMessage = 'MPSessionID does not exist.';
                    $logger->log($logger->logdate, " [UPDATEPROFILE ERROR] ", $logMessage);
                    $apiDetails = 'UPDATEPROFILE-Failed: There is no active session. ';
                    $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, '', $apiDetails, '', 2);
                    if($isInserted == 0) {
                        $logMessage = "Failed to insert to APILogs.";
                        $logger->log($logger->logdate, " [UPDATEPROFILE ERROR] ", $logMessage);
                    }
                    exit;
                }
            }
        }
        else {
            $transMsg = "One or more fields is not set or is blank.";
            $errorCode = 1;
            Utilities::log("ReturnMessage: " . $transMsg . " ErrorCode: " . $errorCode);
            $this->_sendResponse(200, CJSON::encode(CommonController::retMsgUpdateProfile($module, $errorCode, $transMsg)));
            $logMessage = 'One or more fields is not set or is blank.';
            $logger->log($logger->logdate, " [UPDATEPROFILE ERROR] ", $logMessage);
            $apiDetails = 'UPDATEPROFILE-Failed: Invalid input parameters.';
            $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, '', $apiDetails, '', 2);
            if($isInserted == 0) {
                $logMessage = "Failed to insert to APILogs.";
                $logger->log($logger->logdate, " [UPDATEPROFILE ERROR] ", $logMessage);
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
        
        $logger = new ErrorLogger();
        $apiLogsModel = new APILogsModel();
        
        if(isset($request['CardNumber'])) {
            if($request['CardNumber'] == '') {
                $transMsg = "One or more fields is not set or is blank.";
                $errorCode = 1;
                Utilities::log("ReturnMessage: " . $transMsg . " ErrorCode: " . $errorCode);
                $this->_sendResponse(200, CJSON::encode(CommonController::retMsgCheckPoints($module, '', '', $errorCode, $transMsg)));
                $logMessage = 'One or more fields is not set or is blank.';
                $logger->log($logger->logdate, " [CHECKPOINTS ERROR] ", $logMessage);
                $apiDetails = 'CHECKPOINTS-Failed: Invalid input parameters.';
                $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, '', $apiDetails, '', 2);
                if($isInserted == 0) {
                    $logMessage = "Failed to insert to APILogs.";
                    $logger->log($logger->logdate, " [CHECKPOINTS ERROR] ", $logMessage);
                }
                exit;
            }
            else {
                $cardNumber = trim($request['CardNumber']);
                            
                $refID = $cardNumber;
                //start of declaration of models to be used
                $memberCardsModel = new MemberCardsModel();
                $auditTrailModel = new AuditTrailModel();
                
                //$data = $memberCardsModel->getMemberDetailsByCard($cardNumber);
                //$cardTypeID = $data[0]['CardTypeID'];
//                $status = $data[0]['Status'];
//                
                $memberPoints = $memberCardsModel->getMemberPointsAndStatus($cardNumber);
                
                $memberDetails = $memberCardsModel->getMemberDetailsByCard($cardNumber);
                
                if(!empty($memberDetails))
                    $MID = $memberDetails['MID'];
                else
                    $MID = 0;
                
 //               $cardNumberPoints = $memberCards->getCurrentPointsAndStatus($cardNumber);
                if(!empty($memberPoints)) {
                    $currentPoints = $memberPoints['CurrentPoints'];
//                    $bonusPoints = $memberPoints['BonusPoints'];
//                    $redeemedPoints = $memberPoints['RedeemedPoints'];
//                    $lifetimePoints = $memberPoints['LifetimePoints'];
                    $status = $memberPoints['Status'];
//                    $currentPoints = $cardNumberPoints['CurrentPoints'];
//                    $status = $cardNumberPoints['Status'];
                    
                    switch($status) {
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
                    
                    $isSuccessful = $auditTrailModel->logEvent(AuditTrailModel::API_CHECK_POINTS, 'CardNumber: '.$cardNumber, array('MID' => $MID, 'SessionID' => ''));
                    if($isSuccessful == 0) {
                        $logMessage = "Failed to insert to Audittrail.";
                        $logger->log($logger->logdate, " [CHECKPOINTS ERROR] ", $logMessage);
                    }
                    
                    //$transMsg = 'No Error, Transaction successful.';
                    $transMsg = $message;
                    if($status == 1 || $status == 5)
                        $errorCode = 0;
                    else if($status == 0)
                        $errorCode = 6;
                    else if($status == 2)
                        $errorCode = 11;
                    else if($status == 7)
                        $errorCode = 7;
                    else if($status == 8)
                        $errorCode = 8;
                    else if($status == 9)
                        $errorCode = 9;
                    else
                        $errorCode = 10;
                    $this->_sendResponse(200, CJSON::encode(CommonController::retMsgCheckPoints($module, $currentPoints, $cardNumber, $errorCode, $transMsg)));
                    $logMessage = 'Check Points is successful.';
                    $logger->log($logger->logdate, " [CHECKPOINTS SUCCESSFUL] ", $logMessage);
                    $apiDetails = 'CHECKPOINTS-Successful: MID = '.$MID;
                    $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, $refID, $apiDetails, '', 2);
                    if($isInserted == 0) {
                        $logMessage = "Failed to insert to APILogs.";
                        $logger->log($logger->logdate, " [CHECKPOINTS ERROR] ", $logMessage);
                    }
                    exit;
                    
                }
                else {
                    $transMsg = "Card is Invalid.";
                    $errorCode = 10;
                    Utilities::log("ReturnMessage: " . $transMsg . " ErrorCode: " . $errorCode);
                    $this->_sendResponse(200, CJSON::encode(CommonController::retMsgCheckPoints($module, '', '', $errorCode, $transMsg)));
                    $logMessage = 'Card is Invalid.';
                    $logger->log($logger->logdate, " [CHECKPOINTS ERROR] ", $logMessage);
                    $apiDetails = 'CHECKPOINTS-Failed: Membership card is invalid. Card Number = '.$cardNumber;
                    $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, $refID, $apiDetails, '', 2);
                    if($isInserted == 0) {
                        $logMessage = "Failed to insert to APILogs.";
                        $logger->log($logger->logdate, " [CHECKPOINTS ERROR] ", $logMessage);
                    }
                    exit;
                }              
            }
        }
        else {
            
            $transMsg = "One or more fields is not set or is blank.";
            $errorCode = 1;
            Utilities::log("ReturnMessage: " . $transMsg . " ErrorCode: " . $errorCode);
            //$currentPoints = '';
            $this->_sendResponse(200, CJSON::encode(CommonController::retMsgCheckPoints($module, '', '', $errorCode, $transMsg)));
            $logMessage = 'One or more fields is not set or is blank.';
            $logger->log($logger->logdate, " [CHECKPOINTS ERROR] ", $logMessage);
            $apiDetails = 'CHECKPOINTS-Failed: Invalid input parameters.';
            $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, '', $apiDetails, '', 2);
            if($isInserted == 0) {
                $logMessage = "Failed to insert to APILogs.";
                $logger->log($logger->logdate, " [CHECKPOINTS ERROR] ", $logMessage);
            }
            exit;
            
        }
    }
    
    //private function _getSession()
    
    
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
        
        $logger = new ErrorLogger();
        $apiLogsModel = new APILogsModel();
        
        $itemsList = array('RewardID' => $rewardID, 'RewardItemID' => $rewardItemID, 'Description' => $description, 'AvailableItemCount' => $availableItemCount, 
                                       'ProductName' => $productName, 'PartnerName' => $partnerName, 'Points' => $points, 'ThumbnailLimitedImage' => $thumbnailLimitedImage, 
                                       'ECouponImage' => $eCouponImage, 'LearnMoreLimitedImage' => $learnMoreLimitedImage, 'LearnMoreOutOfStockImage' => $learnMoreOutOfStockImage,
                                       'ThumbnailOutOfStockImage' => $thumbnailOutOfStockImage, 'PromoName' => $promoName, 'IsMystery' => $isMystery,
                                       'MysteryName' => $mysteryName, 'MysteryAbout' => $mysteryAbout, 'MysteryTerms' => $mysteryTerms, 'MysterySubtext' => $mysterySubtext, 'About' => $about, 'Terms' => $terms, 
                                       'CompanyAddress' => $companyAddress, 'CompanyPhone' => $companyPhone, 'CompanyWebsite' => $companyWebsite);
        
        if(isset($request['MPSessionID']) && isset($request['PlayerClassID'])) {
            if($request['MPSessionID'] == '' || $request['PlayerClassID'] == '') {
                $transMsg = "One or more fields is not set or is blank.";
                $errorCode = 1;
                Utilities::log("ReturnMessage: " . $transMsg . " ErrorCode: " . $errorCode);
                $this->_sendResponse(200, CJSON::encode(CommonController::retMsgListItems($module, $itemsList, $errorCode, $transMsg)));
                $logMessage = 'One or more fields is not set or is blank.';
                $logger->log($logger->logdate, " [LISTITEMS ERROR] ", $logMessage);
                $apiDetails = 'LISTITEMS-Failed: Invalid input parameters.';
                $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, '', $apiDetails, '', 2);
                if($isInserted == 0) {
                    $logMessage = "Failed to insert to APILogs.";
                    $logger->log($logger->logdate, " [LISTITEMS ERROR] ", $logMessage);
                }
                exit;        
            }
            else {
                $playerClassID = trim($request['PlayerClassID']);
                $mpSessionID = trim($request['MPSessionID']);
                
                
                
                //start of declaration of models to be used
                $rewardItemsModel = new RewardItemsModel();
                $memberSessionsModel = new MemberSessionsModel();
                $auditTrailModel = new AuditTrailModel();
                $refPartnersModel = new Ref_PartnersModel();
                
//                if(isset($session['SessionID'])) {
//                    $mpSessionID = $session['SessionID'];
//                }
//                else {
//                    $mpSessionID = 0;
//                }
                if($playerClassID != 2 && $playerClassID != 3) {
                    $transMsg = "Please input 2 for regular or 3 for vip.";
                    $errorCode = 67;
                    Utilities::log("ReturnMessage: " . $transMsg . " ErrorCode: " . $errorCode);
                    $this->_sendResponse(200, CJSON::encode(CommonController::retMsgListItems($module, $itemsList, $errorCode, $transMsg)));
                    $logMessage = 'Please input 2 for regular or 3 for vip.';
                    $logger->log($logger->logdate, " [LISTITEMS ERROR] ", $logMessage);
                    $apiDetails = 'LISTITEMS-Failed: Invalid input parameter(s)';
                    $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, '', $apiDetails, '', 2);
                    if($isInserted == 0) {
                        $logMessage = "Failed to insert to APILogs.";
                        $logger->log($logger->logdate, " [LISTITEMS ERROR] ", $logMessage);
                    }
                    exit; 
                }
                
                $memberSessions = $memberSessionsModel->getMID($mpSessionID);
                                
                
//                if(isset($session['MID'])) {
//                    $MID = $session['MID'];
//                }
//                else {
//                    $MID = 0;
//                }
                
//                var_dump($MID);
//                //var_dump($mpSessionID);
//                exit;
                if($memberSessions)
                    $MID = $memberSessions['MID'];
                else {
                    $transMsg = "MPSessionID does not exist.";
                    $errorCode = 13;
                    Utilities::log("ReturnMessage: " . $transMsg . " ErrorCode: " . $errorCode);
                    $this->_sendResponse(200, CJSON::encode(CommonController::retMsgListItems($module, $itemsList, $errorCode, $transMsg)));
                    $logMessage = 'MPSessionID does not exist.';
                    $logger->log($logger->logdate, " [LISTITEMS ERROR] ", $logMessage);
                    $apiDetails = 'LISTITEMS-Failed: There is no active session. MID = '.$MID;
                    $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, '', $apiDetails, '', 2);
                    if($isInserted == 0) {
                        $logMessage = "Failed to insert to APILogs.";
                        $logger->log($logger->logdate, " [LISTITEMS ERROR] ", $logMessage);
                    }
                    exit; 
                }
                
                $isExist = $memberSessionsModel->checkIfSessionExist($MID, $mpSessionID);
                
                if(count($isExist) > 0) {
                    $refID = $playerClassID;
                    $rewardoffers = $rewardItemsModel->getAllRewardOffersBasedOnPlayerClassification($playerClassID);
//                    var_dump($rewardoffers);
//                    exit;
                    //$rewardname = $rewardoffers['ProductName'];
                    for($itr = 0; $itr < count($rewardoffers); $itr++) {
                        //$rewardname[$itr] = $rewardoffers[$itr]["ItemName"];
//                        preg_match('/\((.*?)\)/', $rewardoffers[$itr]["ProductName"], $rewardoffers[$itr]["ProductName"]);
//                        if (is_array($rewardname) && isset($rewardname[1])) {
//                            unset($rewardoffers[$itr]["ProductName"]);
//                            $rewardoffers[$itr]["ProductName"] = $rewardname[1];
//
//                        }
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
                        //var_dump($partnerSD);
                        if(isset($partnerSD) || $partnerSD != null || $partnerSD != '') {
                            $companyAddress[$itr] = $partnerSD[$itr]['CompanyAddress'];
                            $companyPhone[$itr] = $partnerSD[$itr]['CompanyPhone'];
                            $companyWebsite[$itr] = $partnerSD[$itr]['CompanyWebsite'];
                        }
                        else {
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
                       // var_dump($itemsList[$itr]);
                        $items[$itr] = $itemsList[$itr];
                     }
                     
                    
                    
                    $isSuccessful = $auditTrailModel->logEvent(AuditTrailModel::API_LIST_ITEMS, 'PlayerClassID: '.$playerClassID, array('MID' => $MID,'SessionID' => $mpSessionID));
                    
                    if($isSuccessful == 0) {
                        $logMessage = "Failed to insert to Audittrail.";
                        $logger->log($logger->logdate, " [LISTITEMS ERROR] ", $logMessage);
                    }

                    $transMsg = 'No Error, Transaction successful.';
                    $errorCode = 0;
                    $this->_sendResponse(200, CJSON::encode(CommonController::retMsgListItems($module, $items, $errorCode, $transMsg)));
                    $logMessage = 'List Items is successful.';
                    $logger->log($logger->logdate, " [LISTITEMS SUCCESSFUL] ", $logMessage);
                    $apiDetails = 'LISTITEMS-Successful: MID = '.$MID;
                    $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, $refID, $apiDetails, '', 1);
                    if($isInserted == 0) {
                        $logMessage = "Failed to insert to APILogs.";
                        $logger->log($logger->logdate, " [LISTITEMS ERROR] ", $logMessage);
                    }
                    exit;
                }
                else {
                    $transMsg = "MPSessionID does not exist.";
                    $errorCode = 13;
                    Utilities::log("ReturnMessage: " . $transMsg . " ErrorCode: " . $errorCode);
                    $this->_sendResponse(200, CJSON::encode(CommonController::retMsgListItems($module, $itemsList, $errorCode, $transMsg)));
                    $logMessage = 'MPSessionID does not exist.';
                    $logger->log($logger->logdate, " [LISTITEMS ERROR] ", $logMessage);
                    $apiDetails = 'LISTITEMS-Failed: There is no active session. MID = '.$MID;
                    $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, '', $apiDetails, '', 2);
                    if($isInserted == 0) {
                        $logMessage = "Failed to insert to APILogs.";
                        $logger->log($logger->logdate, " [LISTITEMS ERROR] ", $logMessage);
                    }
                    exit; 
                }
            }   
        }
        else {
            $transMsg = "One or more fields is not set or is blank.";
            $errorCode = 1;
            Utilities::log("ReturnMessage: " . $transMsg . " ErrorCode: " . $errorCode);
            $this->_sendResponse(200, CJSON::encode(CommonController::retMsgListItems($module, $itemsList, $errorCode, $transMsg)));
            $logMessage = 'One or more fields is not set or is blank.';
            $logger->log($logger->logdate, " [LISTITEMS ERROR] ", $logMessage);
            $apiDetails = 'LISTITEMS-Failed: Invalid input parameters.';
            $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, '', $apiDetails, '', 2);
            if($isInserted == 0) {
                $logMessage = "Failed to insert to APILogs.";
                $logger->log($logger->logdate, " [LISTITEMS ERROR] ", $logMessage);
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
        
        $redemption = array('ItemImage' => $itemImage, 'ItemName' => $itemName, 'PartnerName' => $partnerName, 'PlayerName' => $playerName, 'CardNumber' => $cardNumber, 'RedemptionDate' => $redemptionDate, 'SerialNumber' => $serialCode, 'SecurityCode' => $securityCode, 'ValidityDate' => $validUntil, 'CompanyAddress' => $companyAddress, 'CompanyPhone' => $companyPhone, 'CompanyWebsite' => $companyWebsite, 'Quantity' => $quantity, 'SiteCode' => $siteCode, 'PromoCode' => $promoCode, 'PromoTitle' => $promoTitle,
                            'PromoPeriod' => $promoPeriod, 'DrawDate' => $drawDate, 'Address' => $address, 'Birthdate' => $birthdate, 'EmailAddress' => $email, 'ContactNo' => $contactNo, 'CheckSum' => $checkSum, 'About' => $about, 'Terms' => $terms);
        
        $logger = new ErrorLogger();
        $apiLogsModel = new APILogsModel();
        
        $process = new Processing();
        
        if(isset($request['CardNumber']) && isset($request['RewardItemID']) && isset($request['Quantity']) 
           && isset($request['RewardID']) && isset($request['Source']) && isset($request['MPSessionID'])) {
            if(($request['CardNumber'] == '') || ($request['RewardItemID'] == '') || ($request['RewardID'] == '') || ($request['Quantity'] == '') 
                || ($request['Source'] == '') || ($request['MPSessionID'] == '')) {
                $transMsg = "One or more fields is not set or is blank.";
                $errorCode = 1;
                Utilities::log("ReturnMessage: " . $transMsg . " ErrorCode: " . $errorCode);
                $this->_sendResponse(200, CJSON::encode(CommonController::retMsgRedemption($module, $redemption, $errorCode, $transMsg)));
                $logMessage = 'One or more fields is not set or is blank.';
                $logger->log($logger->logdate, " [REDEEMITEMS ERROR] ", $logMessage);
                $apiDetails = 'REDEEMITEMS-Failed: Invalid input parameters.';
                $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, '', $apiDetails, '', 2);
                if($isInserted == 0) {
                    $logMessage = "Failed to insert to APILogs.";
                    $logger->log($logger->logdate, " [REDEEMITEMS ERROR] ", $logMessage);
                }
                exit;
            }
            else {
                $cardNumber = trim($request['CardNumber']);
                $rewardItemID = trim($request['RewardItemID']);
                $rewardID = trim($request['RewardID']);
                $quantity = trim($request['Quantity']);
                //$itemQuantity = trim($request['ItemQuantity']);
                //$redeemPoints = trim($request['RedeemPoints']);
                $source = trim($request['Source']);
                //$redeemDate = trim($request['RedeemDate']);
                $mpSessionID = trim($request['MPSessionID']);
                
                $memberSessionsModel = new MemberSessionsModel();
                $memberCardsModel = new MemberCardsModel();
                $auditTrailModel = new AuditTrailModel();
                $raffleCouponsModel = new RaffleCouponsModel();
                $rewardItemsModel = new RewardItemsModel();
                $pendingRedemptionModel = new PendingRedemptionModel();
                $couponRedemptionLogsModel = new CouponRedemptionLogsModel();
                $memberInfoModel = new MemberInfoModel();
                //$logger = new ErrorLogger();
                $itemSerialCodesModel = new ItemSerialCodesModel();
                $refPartnersModel = new Ref_PartnersModel();
                
                $helpers = new Helpers();
                
                
                if($rewardID == 1) {
                    $qty1 = $quantity;
                }
                else if($rewardID == 2){
                    $itemQty1 = $quantity;
                }
                else {
                    $transMsg = "RewardID does not exist.";
                    $errorCode = 62;
                    Utilities::log("ReturnMessage: " . $transMsg . " ErrorCode: " . $errorCode);
                    $this->_sendResponse(200, CJSON::encode(CommonController::retMsgRedemption($module, $redemption, $errorCode, $transMsg)));
                    $logMessage = 'RewardID does not exist.';
                    $logger->log($logger->logdate, " [REDEEMITEMS ERROR] ", $logMessage);
                    $apiDetails = 'REDEEMITEMS-Failed: Invalid input parameters.';
                    $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, '', $apiDetails, '', 2);
                    if($isInserted == 0) {
                        $logMessage = "Failed to insert to APILogs.";
                        $logger->log($logger->logdate, " [REDEEMITEMS ERROR] ", $logMessage);
                    }
                    exit;
                }
                
                $result = $memberSessionsModel->getMID($mpSessionID);
                if($result)
                    $MID = $result['MID'];
                else {
                    $transMsg = "MPSessionID does not exist.";
                    $errorCode = 13;
                    Utilities::log("ReturnMessage: " . $transMsg . " ErrorCode: " . $errorCode);
                    $this->_sendResponse(200, CJSON::encode(CommonController::retMsgRedemption($module, $redemption, $errorCode, $transMsg)));
                    $logMessage = 'MPSessionID does not exist.';
                    $logger->log($logger->logdate, " [REDEEMITEMS ERROR] ", $logMessage);
                    $apiDetails = 'REDEEMITEMS-Failed: There is no active session.';
                    $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, '', $apiDetails, '', 2);
                    if($isInserted == 0) {
                        $logMessage = "Failed to insert to APILogs.";
                        $logger->log($logger->logdate, " [REDEEMITEMS ERROR] ", $logMessage);
                    }
                    exit;
                }
                 
                $memberInfo = $memberInfoModel->getMemberInfoUsingMID($MID);
                if($memberInfo)
                    $mobileNumber = $memberInfo['MobileNumber'];
                else {
                    $transMsg = "No member found for that account.";
                    $errorCode = 55;
                    Utilities::log("ReturnMessage: " . $transMsg . " ErrorCode: " . $errorCode);
                    $this->_sendResponse(200, CJSON::encode(CommonController::retMsgRedemption($module, $redemption, $errorCode, $transMsg)));
                    $logMessage = 'No found member for that account.';
                    $logger->log($logger->logdate, " [REDEEMITEMS ERROR] ", $logMessage);
                    $apiDetails = 'REDEEMITEMS-Failed: No member found for that account. [MID] = '.$MID;
                    $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, '', $apiDetails, '', 2);
                    if($isInserted == 0) {
                        $logMessage = "Failed to insert to APILogs.";
                        $logger->log($logger->logdate, " [REDEEMITEMS ERROR] ", $logMessage);
                    }
                    exit;
                }
                
                $isExist = $memberSessionsModel->checkIfSessionExist($MID, $mpSessionID);
                
                if(count($isExist) > 0) {
                    $refID = $cardNumber.';'.$rewardID.';'.$rewardItemID.';'.$quantity;
                    if($source == 3) {
                        $result = $memberCardsModel->getMemberPointsAndStatus($cardNumber);
                        $memberDetails = $memberInfoModel->getMemberInfoUsingMID($MID);
                        $currentPoints = $result['CurrentPoints'];
                    }
                    else {
                        $transMsg = "Please input 3 as source.";
                        $errorCode = 23;
                        Utilities::log("ReturnMessage: " . $transMsg . " ErrorCode: " . $errorCode);
                        $this->_sendResponse(200, CJSON::encode(CommonController::retMsgRedemption($module, $redemption, $errorCode, $transMsg)));
                        $logMessage = 'Please input 3 as source.';
                        $logger->log($logger->logdate, " [REDEEMITEMS ERROR] ", $logMessage);
                        $apiDetails = 'REDEEMITEMS-Failed: Invalid input parameters.';
                        $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, $refID, $apiDetails, '', 2);
                        if($isInserted == 0) {
                            $logMessage = "Failed to insert to APILogs.";
                            $logger->log($logger->logdate, " [REDEEMITEMS ERROR] ", $logMessage);
                        }
                        exit;
                    }
                    
                    if(!$result) {
                        $transMsg = "Card number does not exist.";
                        $errorCode = 61;
                        Utilities::log("ReturnMessage: " . $transMsg . " ErrorCode: " . $errorCode);
                        $this->_sendResponse(200, CJSON::encode(CommonController::retMsgRedemption($module, $redemption, $errorCode, $transMsg)));
                        $logMessage = 'Card number does not exist.';
                        $logger->log($logger->logdate, " [REDEEMITEMS ERROR] ", $logMessage);
                        $apiDetails = 'REDEEMITEMS-Failed: Invalid input parameters.';
                        $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, $refID, $apiDetails, '', 2);
                        if($isInserted == 0) {
                            $logMessage = "Failed to insert to APILogs.";
                            $logger->log($logger->logdate, " [REDEEMITEMS ERROR] ", $logMessage);
                        }
                        exit;
                    }
                    
                    
                    
                    if($rewardID == 1) {
                        $quantity = $qty1;
                    }
                    else {
                        $quantity = $itemQty1;
                    }
                    
                    $itemDetail = $rewardItemsModel->getItemDetails($rewardItemID);
                    if(!$itemDetail || $itemDetail == FALSE) {
                        $transMsg = "RewardItemID does not exist.";
                        $errorCode = 63;
                        Utilities::log("ReturnMessage: " . $transMsg . " ErrorCode: " . $errorCode);
                        $this->_sendResponse(200, CJSON::encode(CommonController::retMsgRedemption($module, $redemption, $errorCode, $transMsg)));
                        $logMessage = 'RewardItemID does not exist.';
                        $logger->log($logger->logdate, " [REDEEMITEMS ERROR] ", $logMessage);
                        $apiDetails = 'REDEEMITEMS-Failed: Invalid input parameters.';
                        $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, $refID, $apiDetails, '', 2);
                        if($isInserted == 0) {
                            $logMessage = "Failed to insert to APILogs.";
                            $logger->log($logger->logdate, " [REDEEMITEMS ERROR] ", $logMessage);
                        }
                        exit;
                    }
                    
                    $requiredPoints = $itemDetail['RequiredPoints'];
//                    $isMystery = $rewardItemsModel->checkIfMystery($rewardItemID);
//                    $isMystery = $isMystery['IsMystery'];
//                    if($isMystery == 1)
                    
                    if($quantity > 0) {
                        if($quantity > 1 && $itemDetail['IsMystery'] == 1) {
                            $transMsg = "Item to be redeemed is a mystery item. Only one mystery item per redeem is allowed.";
                            $errorCode = 60;
                            Utilities::log("ReturnMessage: " . $transMsg . " ErrorCode: " . $errorCode);
                            $this->_sendResponse(200, CJSON::encode(CommonController::retMsgRedemption($module, $redemption, $errorCode, $transMsg)));
                            $quantity = '';
                            $totalItemPoints = '';
                            $logMessage = 'Item to be redeemed is a mystery item. Only one mystery item per redeem is allowed.';
                            $logger->log($logger->logdate, " [REDEEMITEMS ERROR] ", $logMessage);
                            $apiDetails = 'REDEEMITEMS-Failed: Item to be redeemed is a mystery item. Only one mystery item per redeem is allowed. RewardItemID = '.$rewardItemID;
                            $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, $refID, $apiDetails, '', 2);
                            if($isInserted == 0) {
                                $logMessage = "Failed to insert to APILogs.";
                                $logger->log($logger->logdate, " [REDEEMITEMS ERROR] ", $logMessage);
                            }
                            exit;
                        }
                            
                        $totalItemPoints = $quantity * $requiredPoints;
                        if($currentPoints < $totalItemPoints) {
                            $logMessage = 'Transaction failed. Card has insufficient points.';
                            $logger->log($logger->logdate, " [REDEEMITEMS ERROR] ", $logMessage);
                            $apiDetails = 'REDEEMITEMS-Failed: Card has insufficient points. CurrentPoints = '.$currentPoints;
                            $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, $refID, $apiDetails, '', 2);
                            if($isInserted == 0) {
                                $logMessage = "Failed to insert to APILogs.";
                                $logger->log($logger->logdate, " [REDEEMITEMS ERROR] ", $logMessage);
                            }
                            
//                            $transMsg = "Transaction failed. Card has insufficient points.";
//                            $errorCode = 24;
//                            Utilities::log("ReturnMessage: " . $transMsg . " ErrorCode: " . $errorCode);
//                            $this->_sendResponse(200, CJSON::encode(CommonController::retMsg($module, $transMsg, $errorCode)));
                            if($rewardID == 1) {
                                $result = $auditTrailModel->logEvent(AuditTrailModel::API_REDEEM_ITEMS, 'Message: '.$transMsg, array('MID' => $MID, 'SessionID' => $mpSessionID));
                            }
                            else {
                                $result = $auditTrailModel->logEvent(AuditTrailModel::API_REDEEM_ITEMS, 'Message: '.$transMsg, array('MID' => $MID, 'SessionID' => $mpSessionID));
                            }
                            
                            if($result > 0) {
                                $transMsg = "Transaction failed. Card has insufficient points.";
                                $errorCode = 24;
                                Utilities::log("ReturnMessage: " . $transMsg . " ErrorCode: " . $errorCode);
                                $this->_sendResponse(200, CJSON::encode(CommonController::retMsgRedemption($module, $redemption, $errorCode, $transMsg)));
                                $quantity = '';
                                $totalItemPoints = '';
                                $logMessage = 'Transaction failed. Card has insufficient points.';
                                $logger->log($logger->logdate, " [REDEEMITEMS ERROR] ", $logMessage);
                                $apiDetails = 'REDEEMITEMS-Failed: Card has insufficient points. CurrentPoints = '.$currentPoints;
                                $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, $refID, $apiDetails, '', 2);
                                if($isInserted == 0) {
                                    $logMessage = "Failed to insert to APILogs.";
                                    $logger->log($logger->logdate, " [REDEEMITEMS ERROR] ", $logMessage);
                                }
                                exit;
                            }
                            else {
                                $quantity = '';
                                $totalItemPoints = '';
                                if($rewardID == 2) {
                                    $logType == '[COUPON REDEMPTION ERROR]';
                                }
                                else {
                                    $logType == '[ITEM REDEMPTION ERROR]';
                                }
                                
                                $logMessage = "Failed to log event on Audit Trail.";
                                //$transMsg = "Failed to log event on Audit Trail. "." $logType";
                                //$errorCode = 25;
                                $transMsg = "Transaction failed. Card has insufficient points.";
                                $errorCode = 24;
                                $logger->log($logger->logdate,$logType, $logMessage);
                                Utilities::log("ReturnMessage: " . $transMsg . " ErrorCode: " . $errorCode);
                                $this->_sendResponse(200, CJSON::encode(CommonController::retMsgRedemption($module, $redemption, $errorCode, $transMsg)));
                                $apiDetails = 'REDEEMITEMS-Failed: Card has insufficient points. CurrentPoints = '.$currentPoints;
                                $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, $refID, $apiDetails, '', 2);
                                if($isInserted == 0) {
                                    $logMessage = "Failed to insert to APILogs.";
                                    $logger->log($logger->logdate, " [REDEEMITEMS ERROR] ", $logMessage);
                                }
                                exit;
                            }
                            
                        }
                        else {
                            $isCoupon = ($rewardID == 1 || $rewardID == "1") ? false:true;
                            if($isCoupon) {
                                //Check if the available coupon is greater than or match with the quantity avail by the player.
                                $availableCoupon = $raffleCouponsModel->getAvailableCoupons($rewardItemID, $quantity);
                                if(count($availableCoupon) == $quantity && $quantity <= 99999) {
                                    //redemption process for coupon
                                    $offerEndDate = $rewardItemsModel->getOfferEndDate($rewardItemID);
                                    $redeemedDate = $offerEndDate['ItemCurrentDate'];
                                    
                                    
                                    
                                    //check if the date availed is greater than the end date of the reward offer
                                    if($redeemedDate <= $offerEndDate['OfferEndDate']) {
                                        $toBeCurrentPoints = (int)$currentPoints - (int)$totalItemPoints;
                                        if($toBeCurrentPoints < 0) {
                                            $transMsg = "Transaction failed. Card has insufficient points.";
                                            $errorCode = 24;
                                            Utilities::log("ReturnMessage: " . $transMsg . " ErrorCode: " . $errorCode);
                                            $this->_sendResponse(200, CJSON::encode(CommonController::retMsgRedemption($module, $redemption, $errorCode, $transMsg)));
                                            $logMessage = 'Transaction failed. Card has insufficient points.';
                                            $logger->log($logger->logdate, " [REDEEMITEMS ERROR] ", $logMessage);
                                            $apiDetails = 'REDEEMITEMS-Failed: Card has insufficient points. CurrentPoints = '.$currentPoints;
                                            $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, $refID, $apiDetails, '', 2);
                                            if($isInserted == 0) {
                                                $logMessage = "Failed to insert to APILogs.";
                                                $logger->log($logger->logdate, " [REDEEMITEMS ERROR] ", $logMessage);
                                            }
                                            //exit;
                                            $result = $auditTrailModel->logEvent(AuditTrailModel::API_REDEEM_ITEMS, 'ReturnMessage: '.$transMsg, array('MID' => $MID, 'SessionID' => $mpSessionID));
                                            if($result > 0) {
                                                $quantity = '';
                                                $totalItemPoints = '';
                                            }
                                            else {
                                                $quantity = '';
                                                $totalItemPoints = '';
                                                $logMessage = "Failed to log event on Audit Trail.";
                                               // $transMsg = "Failed to log event on Audit Trail. ". " [COUPON REDEMPTION ERROR]";
                                                $errorCode = 25;
                                                
                                                $logger->log($logger->logdate,"[COUPON REDEMPTION ERROR] ", $logMessage);
//                                                Utilities::log("ReturnMessage: " . $transMsg . " ErrorCode: " . $errorCode);
//                                                $this->_sendResponse(200, CJSON::encode(CommonController::retMsg($module, $transMsg, $errorCode)));
                                                
                                            }
                                            exit;
                                        }
                                        else {
                                            $pendingRedemption = $pendingRedemptionModel->checkPendingRedemption($MID);
                                            
                                            //check if there is pending redemption, if there is, throw an error message
                                            if($pendingRedemption) {
                                                $transMsg = 'Transaction failed. Card has a pending redemption.';
                                                $logMessage = 'Transaction failed. Card has a pending redemption.';
                                                $logger->log($logger->logdate, " [REDEEMITEMS ERROR] ", $logMessage);
                                                $apiDetails = 'REDEEMITEMS-Failed: Card has a pending redemption. MID = '.$MID;
                                                $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, $refID, $apiDetails, '', 2);
                                                if($isInserted == 0) {
                                                    $logMessage = "Failed to insert to APILogs.";
                                                    $logger->log($logger->logdate, " [REDEEMITEMS ERROR] ", $logMessage);
                                                }
                                                
                                                $result = $auditTrailModel->logEvent(AuditTrailModel::API_REDEEM_ITEMS, 'ReturnMessage: '.$transMsg, array('MID' => $MID, 'SessionID' => $mpSessionID));
                                                
                                                if($result > 0) {
                                                    $quantity = '';
                                                    $totalItemPoints = '';
                                                }
                                                else {
                                                    $quantity = '';
                                                    $totalItemPoints = '';
                                                    $logMessage = "Failed to log event on Audit Trail.";
                                                    $transMsg = "Failed to log event on Audit Trail. ". " [COUPON REDEMPTION ERROR]";
                                                    $errorCode = 25;
                                                    
                                                    $logger->log($logger->logdate,"[COUPON REDEMPTION ERROR] ", $logMessage);
//                                                    Utilities::log("ReturnMessage: " . $transMsg . " ErrorCode: " . $errorCode);
//                                                    $this->_sendResponse(200, CJSON::encode(CommonController::retMsg($module, $transMsg, $errorCode)));
                                                }
                                                $errorCode = 26;
                                                Utilities::log("ReturnMessage: " . $transMsg . " ErrorCode: " . $errorCode);
                                                $this->_sendResponse(200, CJSON::encode(CommonController::retMsgRedemption($module, $redemption, $errorCode, $transMsg)));
                                                $logMessage = 'Transaction failed. Card has a pending redemption.';
                                                $logger->log($logger->logdate, " [REDEEMITEMS ERROR] ", $logMessage);
                                                $apiDetails = 'REDEEMITEMS-Failed: Card has a pending redemption. MID = '.$MID;
                                                $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, $refID, $apiDetails, '', 2);
                                                if($isInserted == 0) {
                                                    $logMessage = "Failed to insert to APILogs.";
                                                    $logger->log($logger->logdate, " [REDEEMITEMS ERROR] ", $logMessage);
                                                }
                                                exit;
                                            }
                                            else {
                                                //process coupon redemption
                                                $resultArray = $process->processCouponRedemption($MID, $rewardItemID, $quantity, $totalItemPoints, $cardNumber, 3, $redeemedDate);
                                                //var_dump($resultArray);
                                                if($resultArray['IsSuccess']) {
                                                    $oldCurrentPoints = number_format($resultArray['OldCurrentPoints']);
                                                    $redeemedPoints = number_format($totalItemPoints);
                                                    $rewardItem = $rewardItemsModel->getItemDetails($rewardItemID);
                                                    $itemName = $rewardItem['ItemName'];
                                                    $message = "CP: ".$oldCurrentPoints.", Item: ".$itemName.", RP: ".$redeemedPoints.", Series: ".$resultArray['CouponSeries'];
                                                    
                                                }
                                                else {
                                                    $transMsg = $resultArray['Message'];
//                                                    $errorCode = 4;
//                                                    Utilities::log("ReturnMessage: " . $transMsg . " ErrorCode: " . $errorCode);
//                                                    $this->_sendResponse(200, CJSON::encode(CommonController::retMsg($module, $errorCode, $transMsg)));
                                                    $logMessage = 'Transaction failed.';
                                                    $logger->log($logger->logdate, " [REDEEMITEMS ERROR] ", $logMessage);
                                                    $apiDetails = 'REDEEMITEMS-Failed: Processing of coupon redemption failed. MID = '.$MID.'. RewardItemID = '.$rewardItemID.'. TotalItemPoints = '.$totalItemPoints.'. CardNumber = '.$cardNumber;
                                                    $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, $refID, $apiDetails, '', 2);
                                                    if($isInserted == 0) {
                                                        $logMessage = "Failed to insert to APILogs.";
                                                        $logger->log($logger->logdate, " [REDEEMITEMS ERROR] ", $logMessage);
                                                    }
                                                    
                                                }
                                                $isLogged = $auditTrailModel->logEvent(AuditTrailModel::API_REDEEM_ITEMS, 'ReturnMessage: '.$message, array('MID'=>$MID, 'SessionID'=> $mpSessionID));
                                                if($isLogged == 0) {
                                                    $logMessage = "Failed to log event on Audit Trail.";
                                                    $logger->log($logger->logdate,"[COUPON REDEMPTION ERROR] ", $logMessage);
                                                }
                                                    
                                                $quantity = '';
                                                $totalItemPoints = '';
                                                
                                                if(!$resultArray['IsSuccess']) {
                                                    $errorCode = 56;
                                                    Utilities::log("ReturnMessage: " . $transMsg . " ErrorCode: " . $errorCode);
                                                    $this->_sendResponse(200, CJSON::encode(CommonController::retMsgRedemption($module, $redemption, $errorCode, $transMsg)));
                                                }
                                                else {
                                                    $errorCode = 0;
                                                    $transMsg = 'Redemption successful.';
                                                   // $couponRedemptionArray = array('');
                                                    //send SMS alert to player
                                                    //$mobileNumber = '09052111604';
                                                    $this->_sendSMS(SMSRequestLogsModel::COUPON_REDEMPTION, $mobileNumber, $redeemedDate, $resultArray['SerialNumber'], $quantity, "SMSC", $resultArray['LastInsertedID'], '', $resultArray['CouponSeries']);
                                                    
                                                    $showcouponredemptionwindow = true;
                                                    $showitemredemptionwindow = false;
                                                    
                                                    //if coupon, display appropriate reward offer transaction printable copy and send to legit player email
                                                    if($showcouponredemptionwindow == true) {
                                                        //get reward item details
                                                        $rewardOffers = $rewardItemsModel->getRewardItemDetails($rewardItemID);
                                                         
                                                        $birthdate = $memberDetails['Birthdate'];
                                                        $playerName = $memberDetails['FirstName'].' '.$memberDetails['LastName'];
                                                        $address = $memberDetails['Address1'];
                                                        $email = $memberDetails['Email'];
                                                        $contactNo = $memberDetails['MobileNumber'];

                                                        

                                                        for($itr = 0; $itr < count($rewardOffers); $itr++) {

                                                            $eCouponImage[$itr] = $rewardOffers[$itr]["ECouponImage"];
                                                            $partnerName[$itr] = $rewardOffers[$itr]["PartnerName"];
                                                        }

                                                        if(isset($rewardOffers['About'])) {
                                                            $about = $rewardOffers['About'];
                                                            $terms = $rewardOffers['Terms'];
                                                            $promoName = $rewardOffers['PromoName'];
                                                            $promoCode = $rewardOffers['PromoCode'];
                                                        }

                                                        if(isset($resultArray['CouponSeries'])) {
                                                            $startYear = date('Y', strtotime($rewardOffers['StartDate']));
                                                            $endYear = date('Y', strtotime($rewardOffers['EndDate']));
                                                            if($startYear == $endYear) {
                                                                $sDate = new DateTime(date($rewardOffers['Startdate']));
                                                                $startDate = $sDate->format("F j");
                                                                $eDate = new DateTime(date($rewardOffers['EndDate']));
                                                                $endDate = $eDate->format("F j, Y");
                                                                $promoPeriod = $startDate." to ".$endDate;
                                                            }
                                                            else {
                                                                $sDate = new DateTime(date($rewardOffers['Startdate']));
                                                                $startDate = $sDate->format("F j, Y");
                                                                $eDate = new DateTime(date($rewardOffers['EndDate']));
                                                                $endDate = $eDate->format("F j, Y");
                                                                $promoPeriod = $startDate." to ".$endDate;
                                                            }
                                                        }
                                                        else {
                                                            $sDate = new DateTime(date($rewardOffers["StartDate"]));
                                                            $startDate = $sDate->format("F j, Y");
                                                            $eDate = new DateTime(date($rewardOffers["EndDate"]));
                                                            $endDate = $eDate->format("F j, Y");
                                                            $promoPeriod = $startDate." to ".$endDate;
                                                        }
                                                        
                                                        if($rewardOffers['IsMystery'] == 1 && $rewardOffers['AvailableItemCount'] > 0)
                                                            $itemName = $rewardOffers['MysteryName'];
                                                        else
                                                            $itemName = $rewardOffers['ItemName'];
                                                        
                                                        //for coupon only : set draw date format.
                                                        if($rewardOffers['DrawDate'] != '' && $rewardOffers['DrawDate'] != null) {
                                                            $dDate = new DateTime(date($rewardOffers['DrawDate']));
                                                            $drawDate = $dDate->format("F j, Y, gA");
                                                        }
                                                        else
                                                            $drawDate = '';
                                                        
                                                        $newHeader = Yii::app()->params['extra_imagepath'].'extra_images/newheader.jpg';
                                                        $newFooter = Yii::app()->params['extra_imagepath'].'extra_images/newfooter.jpg';
                                                        $itemImage = Yii::app()->params['rewarditem_imagepath'].$eCouponImage;
                                                        $importantReminder = Yii::app()->params['extra_imagepath']."important_reminders.jpg";
                                                        
                                                        $redemptionDate = $resultArray['RedemptionDate'];
                                                        $rDate = new DateTime(date($redemptionDate));
                                                        $redemptionDate = $rDate->format("F j, Y, g:i a");
                                                        
                                                        
           
                                                        $fBirthdate = date("F j, Y", strtotime($birthdate));
                                                        $siteCode = 'Website';
                                                        
                                                        //$email = 'fdlsison@philweb.com.ph';
                                                        $helpers->sendEmailCouponRedemption($playerName,$address,$siteCode,$cardNumber,$fBirthdate,$email,$contactNo,'',
                                                                                        '',$newHeader,$newFooter,$itemImage,$resultArray['CouponSeries'],
                                                                                        $resultArray["Quantity"],$resultArray["CheckSum"],
                                                                                        $resultArray["SerialNumber"],$redemptionDate,$promoCode,
                                                                                        $promoName,$promoPeriod,$drawDate,$about,$terms);

                                                        
                                                    }
                                                    
                                                    $couponRedemptionArray = array('ItemImage' => $itemImage, 'ItemName' => $itemName, 'PartnerName' => $partnerName, 'PlayerName' => $playerName, 'CardNumber' => $cardNumber, 'RedemptionDate' => $redemptionDate, 'SerialNumber' => $resultArray['SerialNumber'],  'SecurityCode' => $resultArray['CouponSeries'], 'ValidityDate' => $validUntil, 'CompanyAddress' => $companyAddress, 'CompanyPhone' => $companyPhone, 'CompanyWebsite' => $companyWebsite, 'Quantity' => $resultArray['Quantity'], 'SiteCode' => $siteCode , 'PromoCode' => $promoCode, 'PromoTitle' => $promoName, 'PromoPeriod' => $promoPeriod, 'DrawDate' => $drawDate, 'Address' => $address, 'Birthdate' => $birthdate, 'EmailAddress' => $email, 'ContactNo' => $contactNo, 'CheckSum' => $resultArray['CheckSum'], 'About' => $about, 'Terms' => $terms);
                                                    Utilities::log("ReturnMessage: " . $transMsg . " ErrorCode: " . $errorCode);
                                                    $this->_sendResponse(200, CJSON::encode(CommonController::retMsgCouponRedemptionSuccess($module, $couponRedemptionArray, $errorCode, $transMsg)));
                                                    $logMessage = $transMsg;
                                                    $logger->log($logger->logdate, " [REDEEMITEMS SUCCESSFUL] ", $logMessage);
                                                    $apiDetails = $transMsg;
                                                    $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, $refID, $apiDetails, $resultArray['LastInsertedID'], 1);
                                                    if($isInserted == 0) {
                                                        $logMessage = "Failed to insert to APILogs.";
                                                        $logger->log($logger->logdate, " [REDEEMITEMS ERROR] ", $logMessage);
                                                    }
                                                    
                                                    
                                                 
                                              }
                                           }
                                           
                                        }
                                    } else {
                                        $message = "Player Redemption: Transaction Failed. Reward Offer has already ended.";
                                        $isLogged = $auditTrailModel->logEvent(AuditTrailModel::API_REDEEM_ITEMS, 'ReturnMessage: '.$message, array('MID'=>$MID, 'SessionID'=>$mpSessionID));
                                        if($isLogged > 0) {
                                            $quantity = '';
                                            $totalItemPoints = '';
                                        }
                                        else {
                                            $logMessage = "Failed to log event on Audit Trail.";
                                            $quantity = '';
                                            $totalItemPoints = '';
                                            $logger->log($logger->logdate,"[COUPON REDEMPTION ERROR] ", $logMessage);
                                        }
                                        $transMsg = "Player Redemption: Transaction Failed. Reward Offer has already ended.";
                                        $errorCode = 49;
                                        Utilities::log("ReturnMessage: " . $transMsg . " ErrorCode: " . $errorCode);
                                        $this->_sendResponse(200, CJSON::encode(CommonController::retMsgRedemption($module, $redemption, $errorCode, $transMsg)));
                                        $logMessage = 'Transaction failed. Reward offer has already ended';
                                        $logger->log($logger->logdate, " [REDEEMITEMS ERROR] ", $logMessage);
                                        $apiDetails = 'REDEEMITEMS-Failed: Reward offer has already ended. RewardItemID = '.$rewardItemID.'.'.', ItemCurrentDate = '.$redeemedDate;
                                        $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, $refID, $apiDetails, '', 2);
                                        if($isInserted == 0) {
                                            $logMessage = "Failed to insert to APILogs.";
                                            $logger->log($logger->logdate, " [REDEEMITEMS ERROR] ", $logMessage);
                                        }
                                        exit;
                                    }
                                } else {
                                    if($quantity > 99999) {
                                        $message = 'Transaction failed. Max number of coupons redeemable is 99999';
                                        $errorCode = 65;
                                    }
                                    else {
                                        $message = "Transaction Failed. Raffle Coupon is either insufficient or unavailable.";
                                        $errorCode = 47;
                                    }    
                                    
                                    $isLogged = $auditTrailModel->logEvent(AuditTrailModel::API_REDEEM_ITEMS, 'ReturnMessage: '.$message, array('MID'=>$MID, 'SessionID'=>$mpSessionID));
                                    if($isLogged > 0) {
                                        $quantity = '';
                                        $totalItemPoints = '';
                                    }
                                    else {
                                        $logMessage = "Failed to log event on Audit Trail.";
                                        $quantity = '';
                                        $totalItemPoints = '';
                                        $logger->log($logger->logdate,"[COUPON REDEMPTION ERROR] ", $logMessage);
                                    }
                                    $transMsg = $message;
                                    //$errorCode = 47;
                                    Utilities::log("ReturnMessage: " . $transMsg . " ErrorCode: " . $errorCode);
                                    $this->_sendResponse(200, CJSON::encode(CommonController::retMsgRedemption($module, $redemption, $errorCode, $transMsg)));
                                    $logMessage = 'Transaction failed. Raffle coupon is either insufficient or unavailable.';
                                    $logger->log($logger->logdate, " [REDEEMITEMS ERROR] ", $logMessage);
                                    $apiDetails = 'REDEEMITEMS-Failed: Processing of coupon redemption failed. MID = '.$MID.'. RewardItemID = '.$rewardItemID.'. TotalItemPoints = '.$totalItemPoints.'. CardNumber = '.$cardNumber;
                                    $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, $refID, $apiDetails, '', 2);
                                    if($isInserted == 0) {
                                        $logMessage = "Failed to insert to APILogs.";
                                        $logger->log($logger->logdate, " [REDEEMITEMS ERROR] ", $logMessage);
                                    }
                                    exit;
                                }
                                
                            } else {
                               
                                //check if the available item is greater than or equal to the quantity availed by the player.
                                $availableItemCount = $rewardItemsModel->getAvailableItemCount($rewardItemID);
                                
                                if($availableItemCount['AvailableItemCount'] >= $quantity) {
                                    $availableSerialCode = $itemSerialCodesModel->getAvailableSerialCodeCount($rewardItemID, $quantity);
                                    
                                    if(count($availableSerialCode) >= $quantity && $quantity <= 5) {
                                        //redemption process for item
                                        $offerEndDate = $rewardItemsModel->getOfferEndDate($rewardItemID);
                                        $redeemedDate = $offerEndDate['ItemCurrentDate'];
                                        $currentDate = $offerEndDate['CurrentDate'];
                                        
                                        //check if the avail date is greater than the end date of the reward offer
                                        if($redeemedDate <= $offerEndDate['OfferEndDate']) {
                                            $toBeCurrentPoints = (int)$currentPoints - (int)$totalItemPoints;
                                            if($toBeCurrentPoints < 0) {
                                                $transMsg = "Transaction Failed. Card has insufficient points.";
                                                $isLogged = $auditTrailModel->logEvent(AuditTrailModel::API_REDEEM_ITEMS, 'ReturnMessage: '.$transMsg, array('MID'=>$MID, 'SessionID'=>$mpSessionID));
                                                if($isLogged > 0) {
                                                    $quantity = '';
                                                    $totalItemPoints = '';
                                                }
                                                else {
                                                    $logMessage = "Failed to log event on database";
                                                    $quantity = '';
                                                    $totalItemPoints = '';
                                                    $logger->log($logger->logdate,"[ITEM REDEMPTION ERROR] ", $logMessage);
                                                }
                                                //$transMsg = "Transaction failed. Card has insufficient points.";
                                                $errorCode = 24;
                                                Utilities::log("ReturnMessage: " . $transMsg . " ErrorCode: " . $errorCode);
                                                $this->_sendResponse(200, CJSON::encode(CommonController::retMsgRedemption($module, $redemption, $errorCode, $transMsg)));
                                                $logMessage = 'Transaction failed. Card has insufficient points.';
                                                $logger->log($logger->logdate, " [REDEEMITEMS ERROR] ", $logMessage);
                                                $apiDetails = 'REDEEMITEMS-Failed: Card has insufficient points. CurrentPoints = '.$currentPoints;
                                                $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, $refID, $apiDetails, '', 2);
                                                if($isInserted == 0) {
                                                    $logMessage = "Failed to insert to APILogs.";
                                                    $logger->log($logger->logdate, " [REDEEMITEMS ERROR] ", $logMessage);
                                                }
                                                exit;
                                            }
                                            else {
                                                $pendingRedemption = $pendingRedemptionModel->checkPendingRedemption($MID);
                                                
                                                //check if there is a pending redemption for this player
                                                if($pendingRedemption) {
                                                    $transMsg = "Transaction failed. Card has a pending redemption.";
                                                    $isLogged = $auditTrailModel->logEvent(AuditTrailModel::API_REDEEM_ITEMS, 'ReturnMessage: '.$transMsg, array('MID'=>$MID, 'SessionID'=>$mpSessionID));
                                                    if($isLogged > 0) {
                                                        $quantity = '';
                                                        $totalItemPoints = '';
                                                    }
                                                    else {
                                                        $logMessage = "Failed to log event on Audit Trail.";
                                                        $quantity = '';
                                                        $totalItemPoints = '';
                                                        $logger->log($logger->logdate,"[ITEM REDEMPTION ERROR] ", $logMessage);
                                                    }
                                                    $errorCode = 26;
                                                    Utilities::log("ReturnMessage: " . $transMsg . " ErrorCode: " . $errorCode);
                                                    $this->_sendResponse(200, CJSON::encode(CommonController::retMsgRedemption($module, $redemption, $errorCode, $transMsg)));
                                                    $logMessage = 'Transaction failed. Card has a pending redemption.';
                                                    $logger->log($logger->logdate, " [REDEEMITEMS ERROR] ", $logMessage);
                                                    $apiDetails = 'REDEEMITEMS-Failed: Card has a pending redemption. MID = '.$MID;
                                                    $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, $refID, $apiDetails, '', 2);
                                                    if($isInserted == 0) {
                                                        $logMessage = "Failed to insert to APILogs.";
                                                        $logger->log($logger->logdate, " [REDEEMITEMS ERROR] ", $logMessage);
                                                    }
                                                    exit;
                                                }
                                                else {
                                                    
                                                    //process item redemption
                                                    $resultsArray = $process->processItemRedemption($MID, $rewardItemID, $quantity, $totalItemPoints, $cardNumber, 3, $redeemedDate );
                                                    
                                                    if($resultsArray['IsSuccess']) {
                                                        $oldCurrentPoints = number_format($resultsArray['OldCP']);
                                                        $redeemedPoints = number_format($totalItemPoints);
                                                        $rewardItem = $rewardItemsModel->getItemDetails($rewardItemID);
                                                        $itemName = $rewardItem['ItemName'];
                                                        $transMsg = "CP: ".$oldCurrentPoints.", Item: ".$itemName.", RP: ".$redeemedPoints;
                                                        $logMessage = $transMsg;
                                                        $logger->log($logger->logdate, " [REDEEMITEMS SUCCESSFUL] ", $logMessage);
                                                        $apiDetails = $transMsg;
                                                        $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, $refID, $apiDetails, '', 1);
                                                        if($isInserted == 0) {
                                                            $logMessage = "Failed to insert to APILogs.";
                                                            $logger->log($logger->logdate, " [REDEEMITEMS ERROR] ", $logMessage);
                                                        }
                                                        
                                                    }
                                                    else {
                                                        $transMsg = $resultsArray['Message'];
                                                        $logMessage = $transMsg;
                                                        $logger->log($logger->logdate, " [REDEEMITEMS ERROR] ", $logMessage);
                                                        $apiDetails = $transMsg;
                                                        $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, $refID, $apiDetails, '', 2);
                                                        if($isInserted == 0) {
                                                            $logMessage = "Failed to insert to APILogs.";
                                                            $logger->log($logger->logdate, " [REDEEMITEMS ERROR] ", $logMessage);
                                                        }
                                                    }
                                                    
                                                    $isLogged = $auditTrailModel->logEvent(AuditTrailModel::API_REDEEM_ITEMS, 'ReturnMessage: '.$transMsg, array('MID'=>$MID, 'SessionID'=>$mpSessionID));
                                                    
                                                    if($isLogged == 0) {
                                                        $logMessage = "Failed to log event on Audit Trail.";
                                                        //$quantity = '';
                                                        //$totalItemPoints = '';
                                                        $logger->log($logger->logdate,"[ITEM REDEMPTION ERROR] ", $logMessage);
                                                    }
                                                    
//                                                    $errorCode = 56;
//                                                    Utilities::log("ReturnMessage: " . $transMsg . " ErrorCode: " . $errorCode);
//                                                    $this->_sendResponse(200, CJSON::encode(CommonController::retMsg($module, $errorCode, $transMsg)));
                                                    
                                                    
                                                    if(!$resultsArray['IsSuccess']) {
                                                        $errorCode = 56;
                                                        Utilities::log("ReturnMessage: " . $transMsg . " ErrorCode: " . $errorCode);
                                                        $this->_sendResponse(200, CJSON::encode(CommonController::retMsgRedemption($module, $redemption, $errorCode, $transMsg)));
                                                        $logMessage = $transMsg;
                                                        $logger->log($logger->logdate, " [REDEEMITEMS ERROR] ", $logMessage);
                                                        $apiDetails = $transMsg;
                                                        $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, $refID, $apiDetails, '', 2);
                                                        if($isInserted == 0) {
                                                            $logMessage = "Failed to insert to APILogs.";
                                                            $logger->log($logger->logdate, " [REDEEMITEMS ERROR] ", $logMessage);
                                                        }
                                                    }
                                                    else {
                                                        $errorCode = 0;
                                                        $transMsg = 'Redemption successful.';
                                                        if($itemDetail['IsMystery'] == 1)
                                                            $itemName = $itemDetail['MysteryName'];
                                                        
//                                                        for($itr = 0; $itr < count($itemDetail); $itr++) {
//
//                                                            $eCouponImage[$itr] = $itemDetail[$itr]["ECouponImage"];
//                                                            $partnerName[$itr] = $itemDetail[$itr]["PartnerName"];
//                                                        }
                                                        $partnerName = $itemDetail['PartnerID'];
                                                        $partner = $refPartnersModel->getPartnerNameUsingPartnerID($partnerName);
                                                        $partnerName = $partner['PartnerName'];
                                                        
                                                        //get partner details
                                                        $partnerSD = $refPartnersModel->getPartnerDetailsUsingPartnerName($partnerName);
                                                        if(isset($partnerSD) || $partnerSD != null || $partnerSD != '') {
                                                            $companyAddress = $partnerSD['CompanyAddress'];
                                                            $companyPhone = $partnerSD['CompanyPhone'];
                                                            $companyWebsite = $partnerSD['CompanyWebsite'];
                                                        }
                                                        else {
                                                            $companyAddress = '';
                                                            $companyPhone = '';
                                                            $companyWebsite = '';
                                                        }
                                                        
                                                        $eCouponImage = $itemDetail['ECouponImage'];
                                                        $itemImage = $eCouponImage;
                                                        $playerName = $memberDetails['FirstName'].' '.$memberDetails['LastName'];
                                                        $redemptionDate = $resultsArray['RedemptionDate'];
                                                        $rDate = new DateTime(date($redemptionDate));
                                                        $redemptionDate = $rDate->format("F j, Y, g:i a");
                                                        
                                                        if(isset($itemDetail['About'])) {
                                                            $about = $itemDetail['About'];
                                                            $terms = $itemDetail['Terms'];
//                                                            $promoName = $rewardOffers['PromoName'];
//                                                            $promoCode = $rewardOffers['PromoCode'];
                                                        }
                                                        //$itemImage = rawurlencode($itemImage);
                                                        
                                                        $itemRedemptionArray = array('ItemImage' => $itemImage, 'ItemName' => $itemName, 'PartnerName' => $partnerName, 'PlayerName' => $playerName, 'CardNumber' => $cardNumber, 'RedemptionDate' => $redemptionDate, 'SerialNumber' => $resultsArray['SessionSerialCode'], 'SecurityCode' => $resultsArray['SessionSecurityCode'], 'ValidityDate' => $resultsArray['ValidUntil'], 'CompanyAddress' => $companyAddress, 'CompanyPhone' => $companyPhone, 'CompanyWebsite' => $companyWebsite, 'Quantity' => $quantity, 'SiteCode' => $siteCode, 'PromoCode' => $promoCode, 'PromoTitle' => $promoTitle,
                                                                                     'PromoPeriod' => $promoPeriod, 'DrawDate' => $drawDate, 'Address' => $address, 'Birthdate' => $birthdate, 'EmailAddress' => $email, 'ContactNo' => $contactNo, 'CheckSum' => $checkSum, 'About' => $about, 'Terms' => $terms);
                                                        //var_dump($transMsg);
                                                        
                                                        Utilities::log("ReturnMessage: " . $transMsg . " ErrorCode: " . $errorCode);
                                                        $this->_sendResponse(200, CJSON::encode(CommonController::retMsgItemRedemptionSuccess($module, $itemRedemptionArray, $errorCode, $transMsg)));
                                                        $logMessage = $transMsg;
                                                        $logger->log($logger->logdate, " [REDEEMITEMS SUCCESSFUL] ", $logMessage);
                                                        $apiDetails = $transMsg;
                                                        $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, $refID, $apiDetails, $resultsArray['LastInsertedID'], 1);
                                                        if($isInserted == 0) {
                                                            $logMessage = "Failed to insert to APILogs.";
                                                            $logger->log($logger->logdate, " [REDEEMITEMS ERROR] ", $logMessage);
                                                        }
                                                        //var_dump($resultsArray['SessionSerialCode']);
                                                        //exit;
                                                        $ctr = count($resultsArray['SessionSerialCode']);
//                                                        var_dump($ctr);
//                                                        exit;
//                                                        var_dump($ctr);
//                                                        var_dump($quantity);
//                                                        var_dump($totalItemPoints);
//                                                        exit;
                                                        var_dump($resultsArray['SessionSerialCode']);
                                                        
                                                        $totalPoints = $totalItemPoints/$quantity;
                                                        //exit;
                                                        //$mobileNumber = '09052111604';
                                                        for($itr = 0; $itr < $ctr; $itr++) {
                                                            $this->_sendSMS(SMSRequestLogsModel::ITEM_REDEMPTION, $mobileNumber, $redeemedDate, $resultsArray['SessionSerialCode'][$itr], 1, "SMSI", $resultsArray['LastInsertedID'][$itr], $totalPoints);
                                                        }
                                                        $showcouponredemptionwindow = true;
                                                        $showitemredemptionwindow = true;
                                                        
                                                        
                                                                    
                                                        $ctr = count($resultsArray['SessionSerialCode']);
                                                        $itemImage = Yii::app()->params['rewarditem_imagepath'].$eCouponImage;
                                                        $newHeader = Yii::app()->params['extra_imagepath'].'extra_images/newheader.jpg';
                                                        $newFooter = Yii::app()->params['extra_imagepath'].'extra_images/newfooter.jpg';
                                                        $importantReminder = Yii::app()->params['extra_imagepath']."important_reminders.jpg";
                                                        
//                                                        var_dump($ctr, $memberDetails['Email'], $newHeader, $itemImage, $itemName, $partnerName, $playerName, $cardNumber, $redemptionDate, $resultsArray['SessionSerialCode'], $resultsArray['SessionSecurityCode'], $resultsArray['ValidUntil'], $companyAddress, $companyPhone, $companyWebsite, $importantReminder, $about, $terms, $newFooter);
//                                                        exit;
                                                        $email = 'fdlsison@philweb.com.ph';
                                                        
                                                        //$email = 'wolfgang24amadeus@yahoo.com';
                                                        //$email = $memberDetails['Email'];
                                                        
                                                        //$itemImage = 'http://www.300headers.com/samples/samples1/2samp.jpg';
//                                                        $path= $newHeader;
//                                                        $type = pathinfo($path, PATHINFO_EXTENSION);
//                                                        $data = file_get_contents($path);
//                                                        $base64 = 'data:image/' . $type . ';base64,' . base64_encode($data);
                                                        //include('../../templates/itemredemptiontemplate');
                                                        //var_dump($email);
                                                        for($itr = 0;$itr < $ctr; $itr++) {
                                                            $helpers->sendEmailItemRedemption($email, $newHeader, $itemImage, $itemName, $partnerName, $playerName, $cardNumber, $redemptionDate, $resultsArray['SessionSerialCode'][$itr], $resultsArray['SessionSecurityCode'][$itr], $resultsArray['ValidUntil'][$itr], $companyAddress, $companyPhone, $companyWebsite, $importantReminder, $about, $terms, $newFooter);
                                                            
                                                            if($itemDetail['IsMystery'] == 1) {
                                                                $rdDate = new DateTime(date($resultsArray['RedemptionDate']));
                                                                $redeemedDate = $rdDate->format('m-d-Y');
                                                                $redeemedTime = $rdDate->format('G:i A');
                                                                $sender = Yii::app()->params['MarketingEmail'];
                                                                if($itemDetail['IsVIP'] == 0)
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
                                        } else {
                                            $transMsg = 'Player Redemption: Transacation failed. Reward offer has already ended.';
                                            $isLogged = $auditTrailModel->logEvent(AuditTrailModel::API_REDEEM_ITEMS, 'ReturnMessage: '.$transMsg, array('MID'=>$MID, 'SessionID'=>$mpSessionID));
                                            if($isLogged > 0) {
                                                $quantity = '';
                                                $totalItemPoints = '';
                                            }
                                            else {
                                                $logMessage = "Failed to log event on Audit Trail.";
                                                $quantity = '';
                                                $totalItemPoints = '';
                                                $logger->log($logger->logdate,"[ITEM REDEMPTION ERROR] ", $logMessage);
                                            }
                                            
                                            $errorCode = 49;
                                            Utilities::log("ReturnMessage: " . $transMsg . " ErrorCode: " . $errorCode);
                                            $this->_sendResponse(200, CJSON::encode(CommonController::retMsgRedemption($module, $redemption, $errorCode, $transMsg)));
                                            $logMessage = $transMsg;
                                            $logger->log($logger->logdate, " [REDEEMITEMS ERROR] ", $logMessage);
                                            $apiDetails = $transMsg;
                                            $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, $refID, $apiDetails, '', 2);
                                            if($isInserted == 0) {
                                                $logMessage = "Failed to insert to APILogs.";
                                                $logger->log($logger->logdate, " [REDEEMITEMS ERROR] ", $logMessage);
                                            }
                                            exit;
                                        }
                                    } else {
                                        if($quantity > 5) {
                                            $transMsg = 'Transaction failed. Max number of items regular items redeemable is 5.';
                                            $errorCode = 66;
                                        }
                                        else {
                                            $transMsg = 'Transaction failed. Serial code is unavailable.';
                                            $errorCode = 57;
                                        }
                                        $isLogged = $auditTrailModel->logEvent(AuditTrailModel::API_REDEEM_ITEMS, 'ReturnMessage: '.$transMsg, array('MID'=>$MID, 'SessionID'=>$mpSessionID));
                                        if($isLogged > 0) {
                                            $quantity = '';
                                            $totalItemPoints = '';
                                        }
                                        else {
                                            $logMessage = "Failed to log event on Audit Trail.";
                                            $quantity = '';
                                            $totalItemPoints = '';
                                            $logger->log($logger->logdate,"[ITEM REDEMPTION ERROR] ", $logMessage);
                                        }

                                        
                                        Utilities::log("ReturnMessage: " . $transMsg . " ErrorCode: " . $errorCode);
                                        $this->_sendResponse(200, CJSON::encode(CommonController::retMsgRedemption($module, $redemption, $errorCode, $transMsg)));
                                        $logMessage = $transMsg;
                                        $logger->log($logger->logdate, " [REDEEMITEMS ERROR] ", $logMessage);
                                        $apiDetails = $transMsg;
                                        $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, $refID, $apiDetails, '', 2);
                                        if($isInserted == 0) {
                                            $logMessage = "Failed to insert to APILogs.";
                                            $logger->log($logger->logdate, " [REDEEMITEMS ERROR] ", $logMessage);
                                        }
                                        exit;
                                    }
                                } else {
                                    $transMsg = 'Transaction failed. Number of available item is insufficient.';
                                    $isLogged = $auditTrailModel->logEvent(AuditTrailModel::API_REDEEM_ITEMS, 'ReturnMessage: '.$transMsg, array('MID'=>$MID, 'SessionID'=>$mpSessionID));
                                    if($isLogged > 0) {
                                        $quantity = '';
                                        $totalItemPoints = '';
                                    }
                                    else {
                                        $logMessage = "Failed to log event on Audit Trail.";
                                        $quantity = '';
                                        $totalItemPoints = '';
                                        $logger->log($logger->logdate,"[ITEM REDEMPTION ERROR] ", $logMessage);
                                    }

                                    $errorCode = 58;
                                    Utilities::log("ReturnMessage: " . $transMsg . " ErrorCode: " . $errorCode);
                                    $this->_sendResponse(200, CJSON::encode(CommonController::retMsgRedemption($module, $redemption, $errorCode, $transMsg)));
                                    $logMessage = $transMsg;
                                    $logger->log($logger->logdate, " [REDEEMITEMS ERROR] ", $logMessage);
                                    $apiDetails = $transMsg;
                                    $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, $refID, $apiDetails, '', 2);
                                    if($isInserted == 0) {
                                        $logMessage = "Failed to insert to APILogs.";
                                        $logger->log($logger->logdate, " [REDEEMITEMS ERROR] ", $logMessage);
                                    }
                                    exit;
                                }
                            }
                        }
                    } else {
                        $transMsg = 'Transaction failed. Invalid Item or Coupon Quantity.';
                        if($rewardID == 1) {
                            $isLogged = $auditTrailModel->logEvent(AuditTrailModel::API_REDEEM_ITEMS, 'ReturnMessage: '.$transMsg, array('MID' => $MID, 'SessionID' => $mpSessionID));
                        }
                        else {
                            $isLogged = $auditTrailModel->logEvent(AuditTrailModel::API_REDEEM_ITEMS, 'ReturnMessage: '.$transMsg, array('MID' => $MID, 'SessionID' => $mpSessionID));
                        }
                        
                        if($isLogged > 0) {
                            $quantity = '';
                            $totalItemPoints = '';
                        }
                        else {
                            $logMessage = "Failed to log event on Audit Trail.";
                            $quantity = '';
                            $totalItemPoints = '';
                            $logger->log($logger->logdate,"[REDEEM ITEMS ERROR] ", $logMessage);
                        }
                        $errorCode = 59;
                        Utilities::log("ReturnMessage: " . $transMsg . " ErrorCode: " . $errorCode);
                        $this->_sendResponse(200, CJSON::encode(CommonController::retMsgRedemption($module, $redemption, $errorCode, $transMsg)));
                        $logMessage = $transMsg;
                        $logger->log($logger->logdate, " [REDEEMITEMS ERROR] ", $logMessage);
                        $apiDetails = $transMsg;
                        $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, $refID, $apiDetails, '', 2);
                        if($isInserted == 0) {
                            $logMessage = "Failed to insert to APILogs.";
                            $logger->log($logger->logdate, " [REDEEMITEMS ERROR] ", $logMessage);
                        }
                        exit;
                    }
                }
                else {
                    $transMsg = "MPSessionID does not exist.";
                    $errorCode = 13;
                    Utilities::log("ReturnMessage: " . $transMsg . " ErrorCode: " . $errorCode);
                    $this->_sendResponse(200, CJSON::encode(CommonController::retMsgRedemption($module, $redemption, $errorCode, $transMsg)));
                    $logMessage = 'MPSessionID does not exist.';
                    $logger->log($logger->logdate, " [REDEEMITEMS ERROR] ", $logMessage);
                    $apiDetails = 'REDEEMITEMS-Failed: There is no active session.';
                    $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, '', $apiDetails, '', 2);
                    if($isInserted == 0) {
                        $logMessage = "Failed to insert to APILogs.";
                        $logger->log($logger->logdate, " [REDEEMITEMS ERROR] ", $logMessage);
                    }
                    exit;
                }
            }
        }
        else {
            $transMsg = "One or more fields is not set or is blank.";
            $errorCode = 1;
            Utilities::log("ReturnMessage: " . $transMsg . " ErrorCode: " . $errorCode);
            $this->_sendResponse(200, CJSON::encode(CommonController::retMsgRedemption($module, $redemption, $errorCode, $transMsg)));
            $logMessage = 'One or more fields is not set or is blank.';
            $logger->log($logger->logdate, " [REDEEMITEMS ERROR] ", $logMessage);
            $apiDetails = 'REDEEMITEMS-Failed: Invalid input parameters.';
            $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, '', $apiDetails, '', 2);
            if($isInserted == 0) {
                $logMessage = "Failed to insert to APILogs.";
                $logger->log($logger->logdate, " [REDEEMITEMS ERROR] ", $logMessage);
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
        
        $logger = new ErrorLogger();
        $apiLogsModel = new APILogsModel();
        
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
        
        $profile = array('FirstName' => $firstname,'MiddleName' => $middlename, 
                                             'LastName' => $lastname, 'NickName' => $nickname, 
                                             'PermanentAdd' => $permanentAddress, 'MobileNo' => $mobileNumber, 
                                             'AlternateMobileNo' => $alternateMobileNumber, 
                                             'EmailAddress' => $emailAddress, 'AlternateEmail' => $alternateEmail,
                                             'Gender' => $gender, 'IDPresented' => $idPresented, 
                                             'IDNumber' => $idNumber, 'Nationality' => $nationality, 
                                             'Occupation' => $occupation, 'IsSmoker' => $isSmoker, 
                                             'Birthdate' => $birthDate, 'Age' => $age, 'CurrentPoints' => $currentPoints,
                                             'BonusPoints' => $bonusPoints, 'RedeemedPoints' => $redeemedPoints, 'LifetimePoints' => $lifetimePoints, 'CardNumber' => $cardNumber);
        
        if(isset($request['CardNumber']) && isset($request['MPSessionID'])) {
            if($request['CardNumber'] == '' || $request['MPSessionID'] == '') {
                $transMsg = "One or more fields is not set or is blank.";
                $errorCode = 1;
                Utilities::log("ReturnMessage: " . $transMsg . " ErrorCode: " . $errorCode);
                $this->_sendResponse(200, CJSON::encode(CommonController::retMsgGetProfile($module, $profile, $errorCode, $transMsg)));
                $logMessage = 'One or more fields is not set or is blank.';
                $logger->log($logger->logdate, " [GETPROFILE ERROR] ", $logMessage);
                $apiDetails = 'GETPROFILE-Failed: Invalid input parameters.';
                $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, '', $apiDetails, '', 2);
                if($isInserted == 0) {
                    $logMessage = "Failed to insert to APILogs.";
                    $logger->log($logger->logdate, " [GETPROFILE ERROR] ", $logMessage);
                }
                exit;
            }
            else {
                
                
                $cardNumber = trim($request['CardNumber']);
                $mpSessionID = trim($request['MPSessionID']);
                
                //start of declaration of models to be used
                $memberCardsModel = new MemberCardsModel();
                $memberInfoModel = new MemberInfoModel();
                $memberSessionsModel = new MemberSessionsModel();
                $cardsModel = new CardsModel();
                $auditTrailModel = new AuditTrailModel();
                //$logger = new ErrorLogger();
                
//                if(isset($session['SessionID'])) {
//                    $mpSessionID = $session['SessionID'];
//                }
//                else {
//                    $mpSessionID = 0;
//                }
//                
                $memberExist = $memberCardsModel->getMIDUsingCard($cardNumber);
                if($memberExist)
                    $MID = $memberExist['MID'];
                else {
                    $transMsg = "Card number does not exist.";
                    $errorCode = 61;
                    Utilities::log("ReturnMessage: " . $transMsg . " ErrorCode: " . $errorCode);
                    $this->_sendResponse(200, CJSON::encode(CommonController::retMsgGetProfile($module, $profile, $errorCode, $transMsg)));
                    $logMessage = 'MPSessionID does not exist.';
                    $logger->log($logger->logdate, " [GETPROFILE ERROR] ", $logMessage);
                    $apiDetails = 'GETPROFILE-Failed: There is no active session. MID = '.$MID;
                    $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, '', $apiDetails, '', 2);
                    if($isInserted == 0) {
                        $logMessage = "Failed to insert to APILogs.";
                        $logger->log($logger->logdate, " [GETPROFILE ERROR] ", $logMessage);
                    }
                    exit; 
                }
                
//                
//                if(isset($session['MID'])) {
//                    $MID = $session['MID'];
//                }
//                else {
//                    $MID = 0;
//                }
                             
                $isExist = $memberSessionsModel->checkIfSessionExist($MID, $mpSessionID);
                
                if($isExist['COUNT(*)'] > 0) {
                    $refID = $cardNumber;
                    if($MID == '' || $MID == null) {
                        $transMsg = "Account is Banned.";
                        $errorCode = 40;
                        Utilities::log("ReturnMessage: " . $transMsg . " ErrorCode: " . $errorCode);
                        $this->_sendResponse(200, CJSON::encode(CommonController::retMsgGetProfile($module, $profile, $errorCode, $transMsg)));
                        $logMessage = 'Account is banned.';
                        $logger->log($logger->logdate, " [GETPROFILE ERROR] ", $logMessage);
                        $apiDetails = 'GETPROFILE-Failed: Account is banned.';
                        $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, $refID, $apiDetails, '', 2);
                        if($isInserted == 0) {
                            $logMessage = "Failed to insert to APILogs.";
                            $logger->log($logger->logdate, " [GETPROFILE ERROR] ", $logMessage);
                        }
                        exit;
                    }
                    else {
                        $memberDetails = $cardsModel->getMemberInfoUsingCardNumber($cardNumber);
                        
                        if($memberDetails) {
                            $firstname = $memberDetails['FirstName'];
                            $middlename = $memberDetails['MiddleName'];
                            if($middlename == null)
                                $middlename = '';
                            $lastname = $memberDetails['LastName'];
                            $nickname = $memberDetails['NickName'];
                            if($nickname == null)
                                $nickname = '';
                            $permanentAddress = $memberDetails['Address1'];
                            $mobileNumber = $memberDetails['MobileNumber'];
                            $alternateMobileNumber = $memberDetails['AlternateMobileNumber'];
                            if($alternateMobileNumber == null)
                                $alternateMobileNumber = '';
                            $emailAddress = $memberDetails['Email'];
                            $alternateEmail = $memberDetails['AlternateEmail'];
                            if($alternateEmail == null)
                                $alternateEmail = '';
                            $gender = $memberDetails['Gender'];
                            if($gender == null)
                                $gender = '';
                            $idPresented = $memberDetails['IdentificationID'];
                            $idNumber = $memberDetails['IdentificationNumber'];
                            $nationality = $memberDetails['NationalityID'];
                            if($nationality == null)
                                $nationality = '';
                            $occupation = $memberDetails['OccupationID'];
                            if($occupation == null)
                                $occupation = '';
                            $isSmoker = $memberDetails['IsSmoker'];
                            if($isSmoker == null)
                                $isSmoker = '';
                            $birthDate = $memberDetails['Birthdate'];
                            $age = number_format((abs(strtotime($birthDate) - strtotime(date('Y-m-d'))) / 60 / 60 / 24 / 365), 0);
                            $currentPoints = $memberDetails['CurrentPoints'];
                            $bonusPoints = $memberDetails['BonusPoints'];
                            $redeemedPoints = $memberDetails['RedeemedPoints'];
                            $lifetimePoints = $memberDetails['LifetimePoints'];
                            //$session['Email'] = $emailAddress;
                            
                            $result = $auditTrailModel->logEvent(AuditTrailModel::API_GET_PROFILE, 'CardNumber: '.$cardNumber, array('MID' => $MID, 'SessionID' => $mpSessionID));
                            if($result == 0) {
                               $logMessage = "Failed to insert to Audittrail.";
                               $logger->log($logger->logdate, " [GETPROFILE ERROR] ", $logMessage); 
                            }
                            
                            $profile = array('FirstName' => $firstname,'MiddleName' => $middlename, 
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
                            $this->_sendResponse(200, CJSON::encode(CommonController::retMsgGetProfile($module, $profile, $errorCode, $transMsg)));
                            $logMessage = 'Get member profile is successful.';
                            $logger->log($logger->logdate, " [GETPROFILE SUCCESSFUL] ", $logMessage);
                            $apiDetails = 'GETPROFILE-Success: Get member profile is successful. MID = '.$MID;
                            $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, $refID, $apiDetails, '', 1);
                            if($isInserted == 0) {
                                $logMessage = "Failed to insert to APILogs.";
                                $logger->log($logger->logdate, " [GETPROFILE ERROR] ", $logMessage);
                            }
                            exit;
                        }
                        else {
                            $transMsg = "Account is Banned.";
                            $errorCode = 40;
                            Utilities::log("ReturnMessage: " . $transMsg . " ErrorCode: " . $errorCode);
                            $this->_sendResponse(200, CJSON::encode(CommonController::retMsgGetProfile($module, $profile, $errorCode, $transMsg)));
                            $logMessage = 'Account is banned.';
                            $logger->log($logger->logdate, " [GETPROFILE ERROR] ", $logMessage);
                            $apiDetails = 'GETPROFILE-Failed: Account is banned.';
                            $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, $refID, $apiDetails, '', 2);
                            if($isInserted == 0) {
                                $logMessage = "Failed to insert to APILogs.";
                                $logger->log($logger->logdate, " [GETPROFILE ERROR] ", $logMessage);
                            }
                            
                            //session_destroy();
                            exit;
                        }
                    }
                }
                else {
                    $transMsg = "MPSessionID does not exist.";
                    $errorCode = 13;
                    Utilities::log("ReturnMessage: " . $transMsg . " ErrorCode: " . $errorCode);
                    $this->_sendResponse(200, CJSON::encode(CommonController::retMsgGetProfile($module, $profile, $errorCode, $transMsg)));
                    $logMessage = 'MPSessionID does not exist.';
                    $logger->log($logger->logdate, " [GETPROFILE ERROR] ", $logMessage);
                    $apiDetails = 'GETPROFILE-Failed: There is no active session. MID = '.$MID;
                    $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, '', $apiDetails, '', 2);
                    if($isInserted == 0) {
                        $logMessage = "Failed to insert to APILogs.";
                        $logger->log($logger->logdate, " [GETPROFILE ERROR] ", $logMessage);
                    }
                    exit;
                    
                }   
            }
        }
        else {
            $transMsg = "One or more fields is not set or is blank.";
            $errorCode = 1;
            Utilities::log("ReturnMessage: " . $transMsg . " ErrorCode: " . $errorCode);
            $this->_sendResponse(200, CJSON::encode(CommonController::retMsgGetProfile($module, $profile, $errorCode, $transMsg)));
            $logMessage = 'One or more fields is not set or is blank.';
            $logger->log($logger->logdate, " [GETPROFILE ERROR] ", $logMessage);
            $apiDetails = 'GETPROFILE-Failed: Invalid input parameters.';
            $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, '', $apiDetails, '', 2);
            if($isInserted == 0) {
                $logMessage = "Failed to insert to APILogs.";
                $logger->log($logger->logdate, " [GETPROFILE ERROR] ", $logMessage);
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
        
        if($methodID == 3) {
            $apiMethod = 3;
            $apiModule = 'REGISTERMEMBER';
        }
        else {
            $apiMethod = 8;
            $apiModule = 'REDEEMITEMS';
        }
        
        //match to 09 or 639 in mobile number
        $match = substr($mobileNumber, 0, 3);
        if($match == "639") {
            $mnCount = count($mobileNumber);
            if(!$mnCount == 12) {
                $idType = $methodID == 1 ? "CouponRedemptionLogID: ": ($methodID == 2 ? "ItemRedemptionLogID: ": "");
                $logType = $methodID == 1 ? "[COUPON REDEMPTION ERROR] ": ($methodID == 2 ? "[ITEM REDEMPTION ERROR] ":"");
                $message = "Failed to send SMS: Invalid Mobile Number [".$idType." $lastInsertedID].";
                $logger->log($logger->logdate,$logType, $message);
                $apiDetails = $apiModule.'-Failed: SendSMS-Invalid Mobile Number. MobileNo = '.$mobileNumber;
                $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, '', $apiDetails, '', 2);
                if($isInserted == 0) {
                    $logMessage = "Failed to insert to APILogs.";
                    $logger->log($logger->logdate, " [".$apiModule." ERROR] ", $logMessage);
                }
                exit;
            }
            else {
                $templateID = $ref_SmsAPIMethodsModel->getSMSMethodTemplateID($methodID);
                $templateID = $templateID['SMSTemplateID'];
                $smsLastInsertedID = $smsRequestLogsModel->insertSMSRequestLogs($methodID, $mobileNumber, $redeemedDate, $couponSeries, $serialNumber, $quantity);
                if($smsLastInsertedID != 0 && $smsLastInsertedID != '') {
                    $trackingID = $prefixTrackingID.$smsLastInsertedID;
                    $apiURL = Yii::app()->params['SMSURI'];
                    $appID = Yii::app()->params['app_id'];
                    $membershipSmsAPI = new MembershipSmsAPI($apiURL, $appID);
                    if($couponSeries != '' && $methodID == 1)
                        $smsResult = $membershipSmsAPI->sendCouponRedemption($mobileNumber, $templateID, $couponSeries, $serialNumber, $quantity, $trackingID);
                    else
                        $smsResult = $membershipSmsAPI->sendItemRedemption ($mobileNumber, $templateID, $serialNumber, $trackingID, $redeemedPoints);
                    
                    if($smsResult['status'] != 1) {
                        $idType = $methodID == 1 ? "CouponRedemptionLogID: ": ($methodID == 2 ? "ItemRedemptionLogID: ": "");
                        $logType = $methodID == 1 ? "[COUPON REDEMPTION ERROR] ": ($methodID == 2 ? "[ITEM REDEMPTION ERROR] ":"");
                        $message = "Failed to send SMS [".$idType." $lastInsertedID].";
                        $logger->log($logger->logdate, $logType, $message);
                        $apiDetails = $apiModule.'-Failed: SendSMS.';
                        $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, '', $apiDetails, '', 2);
                        if($isInserted == 0) {
                            $logMessage = "Failed to insert to APILogs.";
                            $logger->log($logger->logdate, " [".$apiModule." ERROR] ", $logMessage);
                        }
                        exit;
                    }
                } else {
                    $idType = $methodID == 1 ? "CouponRedemptionLogID: ": ($methodID == 2 ? "ItemRedemptionLogID: ": "");
                    $logType = $methodID == 1 ? "[COUPON REDEMPTION ERROR] ": ($methodID == 2 ? "[ITEM REDEMPTION ERROR] ":"");
                    $message = "Failed to send SMS: Failed to log event on database [".$idType." $lastInsertedID].";
                    $logger->log($logger->logdate,$logType, $message);
                    $apiDetails = $apiModule.'-Failed: SendSMS-Failed to insert to smsrequestlogs.';
                    $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, '', $apiDetails, '', 2);
                    if($isInserted == 0) {
                        $logMessage = "Failed to insert to APILogs.";
                        $logger->log($logger->logdate, " [".$apiModule." ERROR] ", $logMessage);
                    }
                    exit;
                }
            }
        } else {
            $match = substr($mobileNumber, 0, 2);
            if($match == "09") {
                $mnCount = count($mobileNumber);
                if(!$mnCount == 11) {
                    $idType = $methodID == 1 ? "CouponRedemptionLogID: ": ($methodID == 2 ? "ItemRedemptionLogID: ": "");
                    $logType = $methodID == 1 ? "[COUPON REDEMPTION ERROR] ": ($methodID == 2 ? "[ITEM REDEMPTION ERROR] ":"");
                    $message = "Failed to send SMS: Invalid Mobile Number [".$idType." $lastInsertedID].";
                    $logger->log($logger->logdate,$logType, $message);
                    $apiDetails = $apiModule.'-Failed: SendSMS-Invalid Mobile Number. MobileNo = '.$mobileNumber;
                    $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, '', $apiDetails, '', 2);
                    if($isInserted == 0) {
                        $logMessage = "Failed to insert to APILogs.";
                        $logger->log($logger->logdate, " [".$apiModule." ERROR] ", $logMessage);
                    }
                    exit;
                }
                else {
                    $mobileNumber = str_replace("09", "639", $mobileNumber);
                    $templateID = $ref_SmsAPIMethodsModel->getSMSMethodTemplateID($methodID);
                    $templateID = $templateID['SMSTemplateID'];
                    $smsLastInsertedID = $smsRequestLogsModel->insertSMSRequestLogs($methodID, $mobileNumber, $redeemedDate, $couponSeries, $serialNumber, $quantity);
                    if($smsLastInsertedID != 0 && $smsLastInsertedID != ''){
                        $trackingID = $prefixTrackingID.$smsLastInsertedID;
                        $apiURL = Yii::app()->params['SMSURI'];
                        $appID = Yii::app()->params['app_id'];    
                        $membershipSmsAPI = new MembershipSmsAPI($apiURL, $appID);
                        if($couponSeries != '' && $methodID == 1){
                            $smsResult = $membershipSmsAPI->sendCouponRedemption($mobileNumber, $templateID, $couponSeries, $serialNumber, $quantity, $trackingID);
                        } else {
                            $smsResult = $membershipSmsAPI->sendItemRedemption($mobileNumber, $templateID, $serialNumber, $trackingID, $redeemedPoints);
                        }
                        
                        if($smsResult['status'] != 1){
                            $idType = $methodID == 1 ? "CouponRedemptionLogID: ": ($methodID == 2 ? "ItemRedemptionLogID: ": "");
                            $logType = $methodID == 1 ? "[COUPON REDEMPTION ERROR] ": ($methodID == 2 ? "[ITEM REDEMPTION ERROR] ":"");
                            $message = "Failed to send SMS [".$idType." $lastInsertedID].";
                            $logger->log($logger->logdate,$logType, $message);
                            $apiDetails = $apiModule.'-Failed: SendSMS.';
                            $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, '', $apiDetails, '', 2);
                            if($isInserted == 0) {
                                $logMessage = "Failed to insert to APILogs.";
                                $logger->log($logger->logdate, " [".$apiModule." ERROR] ", $logMessage);
                            }
                            exit;
                        }
                    } else {
                        $idType = $methodID == 1 ? "CouponRedemptionLogID: ": ($methodID == 2 ? "ItemRedemptionLogID: ": "");
                        $logType = $methodID == 1 ? "[COUPON REDEMPTION ERROR] ": ($methodID == 2 ? "[ITEM REDEMPTION ERROR] ":"");
                        $message = "Failed to send SMS: Error on logging event in database [".$idType." $lastInsertedID].";
                        $logger->log($logger->logdate,$logType, $message);
                        $apiDetails = $apiModule.'-Failed: SendSMS-Failed to insert to smsrequestlogs.';
                        $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, '', $apiDetails, '', 2);
                        if($isInserted == 0) {
                            $logMessage = "Failed to insert to APILogs.";
                            $logger->log($logger->logdate, " [".$apiModule." ERROR] ", $logMessage);
                        }
                        exit;
                    }
                }
            } else {
                $idType = $methodID == 1 ? "CouponRedemptionLogID: ": ($methodID == 2 ? "ItemRedemptionLogID: ": "");
                $logType = $methodID == 1 ? "[COUPON REDEMPTION ERROR] ": ($methodID == 2 ? "[ITEM REDEMPTION ERROR] ":"");
                $message = "Failed to send SMS: Invalid Mobile Number [".$idType." $lastInsertedID].";
                $logger->log($logger->logdate,$logType, $message);
                $apiDetails = $apiModule.'-Failed: SendSMS-Invalid mobile number. MobileNo = '.$mobileNumber;
                $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, '', $apiDetails, '', 2);
                if($isInserted == 0) {
                    $logMessage = "Failed to insert to APILogs.";
                    $logger->log($logger->logdate, " [".$apiModule." ERROR] ", $logMessage);
                }
                exit;
            }
        }
    }
    
    //@date 07-04-2014
    //@purpose retrieves genders available for selection
    public function actionGetGender() {
        //$request = $this->_readJsonRequest();
        
//        $transMsg = '';
//        $errorCode = '';
        $module = 'GetGender';
           
//        $transMsg = 'No Error, Transaction successful.';
//        $errorCode = 0;
        $gender = array(array('GenderID' => 1,'GenderDescription' => 'Male'), array('GenderID' => 2,'GenderDescription' => 'Female'));
        $this->_sendResponse(200, CJSON::encode(CommonController::retMsgGetGender($module, $gender)));
        //return $gender;
        
        
    }
    
    
    
    //@pupose retrieves the ID types available for selection
    public function actionGetIDPresented() {
//        $request = $this->_readJsonRequest();
//        
//        $transMsg = '';
//        $errorCode = '';
        $module = 'GetIDPresented';
        
//        if(isset($request['TPSessionID'])) {
//            if($request['TPSessionID'] == '') {
//                $transMsg = "One or more fields is not set or is blank.";
//                $errorCode = 1;
//                Utilities::log("ReturnMessage: " . $transMsg . " ErrorCode: " . $errorCode);
//                $this->_sendResponse(200, CJSON::encode(CommonController::retMsg($module, $transMsg, $errorCode)));
//            }
//            else {
                $refIdentifications = new Ref_IdentificationsModel();
                
                $idPresented = $refIdentifications->getIDPresentedList();
                
                for($itr = 0; $itr < count($idPresented); $itr++) {
                    $identificationID[$itr] = $idPresented[$itr]['IdentificationID'];
                    $identificationName[$itr] = $idPresented[$itr]['IdentificationName'];
                    $idPresented[$itr] = array('PresentedID' => $identificationID[$itr], 'PresentedIDDescription' => $identificationName[$itr]);
                }
                
//                var_dump($identificationID);
//                var_dump($identificationName);
//                exit;
                
//                $transMsg = 'No Error, Transaction successful.';
//                $errorCode = 0;
                //$gender = array(array('PresentedID' => 1,'GenderDescription' => 'Male'), array('GenderID' => 2,'GenderDescription' => 'Female'));
                $this->_sendResponse(200, CJSON::encode(CommonController::retMsgGetIDPresented($module, $idPresented)));
            }
//        }
//        else {
//            $transMsg = "One or more fields is not set or is blank.";
//            $errorCode = 1;
//            Utilities::log("ReturnMessage: " . $transMsg . " ErrorCode: " . $errorCode);
//            $this->_sendResponse(200, CJSON::encode(CommonController::retMsg($module, $transMsg, $errorCode)));
//        }
//        
//        
//        
//        //return $idPresented;
//    }
    
    //@purpose retrieves the nationalities available for selection
    public function actionGetNationality() {
//        $request = $this->_readJsonRequest();
//        
//        $transMsg = '';
//        $errorCode = '';
        $module = 'GetNationality';
        
//        if(isset($request['TPSessionID'])) {
//            if($request['TPSessionID'] == '') {
//                $transMsg = "One or more fields is not set or is blank.";
//                $errorCode = 1;
//                Utilities::log("ReturnMessage: " . $transMsg . " ErrorCode: " . $errorCode);
//                $this->_sendResponse(200, CJSON::encode(CommonController::retMsg($module, $transMsg, $errorCode)));
//            }
//            else {
                $refNationality = new Ref_NationalityModel();
                
                $nationality = $refNationality->getNationalityList();
                
                for($itr = 0; $itr < count($nationality); $itr++) {
                    $nationalityID[$itr] = $nationality[$itr]['NationalityID'];
                    $nationalityName[$itr] = $nationality[$itr]['Name'];
                    $nationality[$itr] = array('NationalityID' => $nationalityID[$itr], 'NationalityDescription' => $nationalityName[$itr]);
                }
                
//                var_dump($identificationID);
//                var_dump($identificationName);
//                exit;
                
//                $transMsg = 'No Error, Transaction successful.';
//                $errorCode = 0;
                //$gender = array(array('PresentedID' => 1,'GenderDescription' => 'Male'), array('GenderID' => 2,'GenderDescription' => 'Female'));
                $this->_sendResponse(200, CJSON::encode(CommonController::retMsgGetNationality($module, $nationality)));
//            }
//        }
//        else {
//            $transMsg = "One or more fields is not set or is blank.";
//            $errorCode = 1;
//            Utilities::log("ReturnMessage: " . $transMsg . " ErrorCode: " . $errorCode);
//            $this->_sendResponse(200, CJSON::encode(CommonController::retMsg($module, $transMsg, $errorCode)));
//        }
//        
//        
//        
//        //return $nationality;
    }
    
    //@purpose retrieves the occupations available for selection
    public function actionGetOccupation() {
//        $request = $this->_readJsonRequest();
//        
//        $transMsg = '';
////        $errorCode = '';
        $module = 'GetOccupation';
//        
//        if(isset($request['TPSessionID'])) {
//            if($request['TPSessionID'] == '') {
//                $transMsg = "One or more fields is not set or is blank.";
//                $errorCode = 1;
//                Utilities::log("ReturnMessage: " . $transMsg . " ErrorCode: " . $errorCode);
//                $this->_sendResponse(200, CJSON::encode(CommonController::retMsg($module, $transMsg, $errorCode)));
//            }
//            else {
                $refOccupations = new Ref_OccupationsModel();
                
                $occupation = $refOccupations->getOccupationList();
                
                
                for($itr = 0; $itr < count($occupation); $itr++) {
                    $occupationID[$itr] = $occupation[$itr]['OccupationID'];
                    $occupationName[$itr] = $occupation[$itr]['Name'];
                    $occupation[$itr] = array('OccupationID' => $occupationID[$itr], 'OccupationDescription' => $occupationName[$itr]);
                }
                
//                var_dump($occupation);
//                exit;
//                var_dump($identificationName);
//                exit;
                
//                $transMsg = 'No Error, Transaction successful.';
//                $errorCode = 0;
                //$gender = array(array('PresentedID' => 1,'GenderDescription' => 'Male'), array('GenderID' => 2,'GenderDescription' => 'Female'));
                $this->_sendResponse(200, CJSON::encode(CommonController::retMsgGetOccupation($module, $occupation)));
//            }
//        }
//        else {
//            $transMsg = "One or more fields is not set or is blank.";
//            $errorCode = 1;
//            Utilities::log("ReturnMessage: " . $transMsg . " ErrorCode: " . $errorCode);
//            $this->_sendResponse(200, CJSON::encode(CommonController::retMsg($module, $transMsg, $errorCode)));
//        }
        
        //return $occupation;
    }
    
    //@purpose retrieves member classification if smoker or non-smoker
    public function actionGetIsSmoker() {
//       $request = $this->_readJsonRequest();
//        
//        $transMsg = '';
//        $errorCode = '';
//        $isSmoker = '';
        $module = 'GetIsSmoker';
        
//        if(isset($request['TPSessionID'])) {
//            if($request['TPSessionID'] == '') {
//                $transMsg = "One or more fields is not set or is blank.";
//                $errorCode = 1;
//                Utilities::log("ReturnMessage: " . $transMsg . " ErrorCode: " . $errorCode);
//                $this->_sendResponse(200, CJSON::encode(CommonController::retMsg($module, $transMsg, $errorCode)));
//            }
//            else {     
//                $transMsg = 'No Error, Transaction successful.';
//                $errorCode = 0;
                $isSmoker = array(array('IsSmokerID' => 1,'IsSmokerDescription' => 'Smoker'),array('IsSmokerID' => 2,'IsSmokerDescription' => 'Non-smoker'));
                $this->_sendResponse(200, CJSON::encode(CommonController::retMsgGetIsSmoker($module, $isSmoker)));
//            }
//        }
//        else {
//            $transMsg = "One or more fields is not set or is blank.";
//            $errorCode = 1;
//            Utilities::log("ReturnMessage: " . $transMsg . " ErrorCode: " . $errorCode);
//            $this->_sendResponse(200, CJSON::encode(CommonController::retMsg($module, $transMsg, $errorCode)));
//        }
    }
    
    //@date 08-08-2014
    //@purpose retrieves referrer list
    public function actionGetReferrer() {
        $module = 'GetReferrer';
        
        $refReferrer = new Ref_ReferrerModel();
                
        $referrer = $refReferrer->getReferrerList();


        for($itr = 0; $itr < count($referrer); $itr++) {
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


        for($itr = 0; $itr < count($region); $itr++) {
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


        for($itr = 0; $itr < count($city); $itr++) {
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
        
        //$session=new CHttpSession();
//        //$session->open();
//        var_dump($session->GetIsStarted());
//        exit;
        $memberSessionsModel = new MemberSessionsModel();
        $logger = new ErrorLogger();
        $apiLogsModel = new APILogsModel();
        $auditTrailModel = new AuditTrailModel();
        $membersModel = new MembersModel();
        
//        
        if(isset($request['MPSessionID'])) {
            if($request['MPSessionID'] == '') {
                $logMessage = 'One or more fields is not set or is blank.';
                $transMsg = 'One or more fields is not set or is blank.';
                $errorCode = 1;
                Utilities::log("ReturnMessage: " . $transMsg . " ErrorCode: " . $errorCode);
                $this->_sendResponse(200, CJSON::encode(CommonController::retMsg($module, $errorCode, $transMsg)));
                $logger->log($logger->logdate, " [LOGOUT ERROR] ", $logMessage);
                $apiDetails = 'LOGOUT-Failed: Invalid input parameter.';
                $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, '', $apiDetails, '', 2);
                if($isInserted == 0) {
                    $logMessage = "Failed to insert to APILogs.";
                    $logger->log($logger->logdate, " [LOGOUT ERROR] ", $logMessage);
                }
                exit;
            }
            else {
                $mpSessionID = trim($request['MPSessionID']);
                $memberSession = $memberSessionsModel->getMID($mpSessionID);
                if($memberSession) {
                    $MID = $memberSession['MID'];
                    $refID = $MID;
                    $memberDetails = $membersModel->getMemberDetailsByMID($MID);
                    $username = $memberDetails['UserName'];
                }
                else
                {
                    $transMsg = "Session does not exist.";
                    $errorCode = 2;
                    $logMessage = 'Session does not exist.';
                    Utilities::log("ReturnMessage: " . $transMsg . " ErrorCode: " . $errorCode);
                    $this->_sendResponse(200, CJSON::encode(CommonController::retMsg($module, $errorCode, $transMsg)));
                    $logger->log($logger->logdate, " [LOGOUT ERROR] ", $logMessage);
                    $apiDetails = 'LOGOUT-Failed: There is no active session for this account.';
                    $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, '', $apiDetails, '', 2);
                    if($isInserted == 0) {
                        $logMessage = "Failed to insert to APILogs.";
                        $logger->log($logger->logdate, " [LOGOUT ERROR] ", $logMessage);
                    }
                    exit; 
                }
                $isDeleted = $memberSessionsModel->deleteSession($MID, $mpSessionID);
                if($isDeleted == 0) {
                    $transMsg = "Failed to delete session.";
                    $errorCode = 3;
                    $logMessage = 'Failed to delete session.';
                    Utilities::log("ReturnMessage: " . $transMsg . " ErrorCode: " . $errorCode);
                    $this->_sendResponse(200, CJSON::encode(CommonController::retMsg($module, $errorCode, $transMsg)));
                    $logger->log($logger->logdate, " [LOGOUT ERROR] ", $logMessage);
                    $apiDetails = 'LOGOUT-DeleteSession-Failed: Error in deleting session from membersessions table. [MID] = '.$MID.' [MPSessionID] = '.$mpSessionID;
                    $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, $refID, $apiDetails, '', 2);
                    if($isInserted == 0) {
                        $logMessage = "Failed to insert to APILogs.";
                        $logger->log($logger->logdate, " [LOGOUT ERROR] ", $logMessage);
                    }
                    exit;
                }
                
                $transMsg = "No error, transaction successful.";
                $logMessage = 'No error, transaction successful.';
                $errorCode = 0;
                Utilities::log("ReturnMessage: " . $transMsg . " ErrorCode: " . $errorCode);
                $this->_sendResponse(200, CJSON::encode(CommonController::retMsg($module, $errorCode, $transMsg)));
                $logger->log($logger->logdate, " [LOGOUT SUCCESSFUL] ", $logMessage);
                $apiDetails = 'Successful.';
                $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, $refID, $apiDetails, '', 1);
                if($isInserted == 0) {
                    $logMessage = "Failed to insert to APILogs.";
                    $logger->log($logger->logdate, " [LOGOUT ERROR] ", $logMessage);
                }
                $auditTrailModel->logEvent(AuditTrailModel::API_LOGOUT, 'Logout: '.$username, array('MID' => $MID, 'SessionID' => $mpSessionID));
                exit;
            }
        }
        else {
            $logMessage = 'One or more fields is not set or is blank.';
            $transMsg = 'One or more fields is not set or is blank.';
            $errorCode = 1;
            Utilities::log("ReturnMessage: " . $transMsg . " ErrorCode: " . $errorCode);
            $this->_sendResponse(200, CJSON::encode(CommonController::retMsg($module, $errorCode, $transMsg)));
            $logger->log($logger->logdate, " [LOGOUT ERROR] ", $logMessage);
            $apiDetails = 'LOGOUT-Failed: Invalid input parameter.';
            $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, '', $apiDetails, '', 2);
            if($isInserted == 0) {
                $logMessage = "Failed to insert to APILogs.";
                $logger->log($logger->logdate, " [LOGOUT ERROR] ", $logMessage);
            }
            exit;
        }
    }
    
//    public function actionChangePassword() {
//        $request = $this->_readJsonRequest();
//        $transMsg = '';
//        $errorCode = '';
//        $module = 'ChangePassword';
//        
//        if(isset($request['Username']) && isset($request['Password']) && isset($request['ConfirmPassword'])) {
//            if($request['Username'] == '' || $request['Password'] == '' || $request['ConfirmPassword'] == '' ) {
//                $transMsg = "One or more fields is not set or is blank.";
//                $errorCode = 1;
//                Utilities::log("ReturnMessage: " . $transMsg . " ErrorCode: " . $errorCode);
//                $this->_sendResponse(200, CJSON::encode(CommonController::retMsg($module, $transMsg, $errorCode)));
//                exit;
//            }
//            else if($request['Password'] != $request['ConfirmPassword']) {
//                $transMsg = "Password should be the same as confirm password.";
//                $errorCode = 20;
//                Utilities::log("ReturnMessage: " . $transMsg . " ErrorCode: " . $errorCode);
//                $this->_sendResponse(200, CJSON::encode(CommonController::retMsg($module, $transMsg, $errorCode)));
//                exit;
//            }
//            else {
//                $hashedCardNumber = $_GET['CardNumber'];
//                $cardNumber = base64_decode($hashedCardNumber);
//                
//                $membersModel = new MembersModel();
//                $memberCardsModel = new MemberCardsModel();
//                
//                $isAllowed = $membersModel->getForChangePasswordUsingCardNumber($cardNumber);
//                if($isAllowed)
//                    $IsForChange = $isAllowed['ForChangePassword'];
//                
//                $username = trim($request['Username']);
//                $password = trim($request['Password']);
//                $confirmPassword = trim($request['ConfirmPassword']);
//                
//                if($IsForChange == 1) {
//                    $result = $memberCardsModel->getMIDUsingCard($username);
//                    if($result)
//                        $MID = $result['MID'];
//                    
//                    $isUpdated = $membersModel->updatePasswordUsingMID($MID, $confirmPassword);
//                    if($isUpdated == 1) {
//                        $isSuccessful = $membersModel->updateForChangePasswordUsingMID($MID, 0);
//                        if($isSuccessful == 1) {
//                            $transMsg = "No error, Transaction successful.";
//                            $errorCode = 0;
//                            Utilities::log("ReturnMessage: " . $transMsg . " ErrorCode: " . $errorCode);
//                            $this->_sendResponse(200, CJSON::encode(CommonController::retMsg($module, $transMsg, $errorCode)));
//                            exit;
//                        }
//                        else
//                            exit;
//                    }
//                    else
//                        exit;
//                    
//                }
//                else {
//                    $transMsg = "Member account is not for change password.";
//                    $errorCode = 54;
//                    Utilities::log("ReturnMessage: " . $transMsg . " ErrorCode: " . $errorCode);
//                    $this->_sendResponse(200, CJSON::encode(CommonController::retMsg($module, $transMsg, $errorCode)));
//                    exit;
//                }
//                
//            }
//        }
//        else {
//            $transMsg = "One or more fields is not set or is blank.";
//                $errorCode = 1;
//                Utilities::log("ReturnMessage: " . $transMsg . " ErrorCode: " . $errorCode);
//                $this->_sendResponse(200, CJSON::encode(CommonController::retMsg($module, $transMsg, $errorCode)));
//                exit;
//        }
//        
//    }
    
    
    
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
    
    
    //@date 09-15-2014
    //@purpose member registration
    public function actionRegisterMemberBT() {
        $request = $this->_readJsonRequest();
        $transMsg = '';
        $errorCode = '';
        $module = 'RegisterMemberBT';
        $apiMethod = 18;
        
        $logger = new ErrorLogger();
        $apiLogsModel = new APILogsModel();
        
        
        if(isset($request['FirstName']) && isset($request['LastName']) && isset($request['MobileNo']) && isset($request['EmailAddress']) && isset($request['Birthdate'])) { //&& isset($request['Password']) && isset($request['IDPresented']) && isset($request['IDNumber'])) {
            if(($request['FirstName'] == '') || ($request['LastName'] == '') || ($request['MobileNo'] == '') || ($request['EmailAddress'] == '') || ($request['Birthdate'] == '')) { //|| ($request['Password'] == '') || ($request['IDPresented'] == '') || ($request['IDNumber'] == '')) {
                $transMsg = "One or more fields is not set or is blank.";
                $errorCode = 1;
                Utilities::log("ReturnMessage: " . $transMsg . " ErrorCode: " . $errorCode);
                $this->_sendResponse(200, CJSON::encode(CommonController::retMsgRegisterMemberBT($module,'','', $errorCode, $transMsg)));
                $logMessage = 'One or more fields is not set or is blank.';
                $logger->log($logger->logdate, " [REGISTERMEMBERBT ERROR] ", $logMessage);
                $apiDetails = 'REGISTERMEMBERBT-Failed: Invalid input parameters. ';
                $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, '', $apiDetails, '', 2);
                if($isInserted == 0) {
                    $logMessage = "Failed to insert to APILogs.";
                    $logger->log($logger->logdate, " [REGISTERMEMBERBT ERROR] ", $logMessage);
                }
                exit;
            }
            else if(strlen($request['FirstName']) < 2 || strlen($request['LastName']) < 2) {
                $transMsg = "Name should not be less than 2 characters long.";
                $errorCode = 14;
                Utilities::log("ReturnMessage: " . $transMsg . " ErrorCode: " . $errorCode);
                $this->_sendResponse(200, CJSON::encode(CommonController::retMsgRegisterMemberBT($module,'','', $errorCode, $transMsg)));
                $logMessage = 'Name should not be less than 2 characters long.';
                $logger->log($logger->logdate, " [REGISTERMEMBERBT ERROR] ", $logMessage);
                $apiDetails = 'REGISTERMEMBERBT-Failed: Invalid input parameters. ';
                $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, '', $apiDetails, '', 2);
                if($isInserted == 0) {
                    $logMessage = "Failed to insert to APILogs.";
                    $logger->log($logger->logdate, " [REGISTERMEMBERBT ERROR] ", $logMessage);
                }
                exit;
            }
            else if(preg_match("/^[A-Za-z\s]+$/", trim($request['FirstName'])) == 0 || preg_match("/^[A-Za-z\s]+$/", trim($request['LastName'])) == 0) {
            //else if(ctype_alpha($request['FirstName']) == FALSE || ctype_alpha($request['LastName']) == FALSE) {
                $transMsg = "Name should consist of letters and spaces only.";
                $errorCode = 17;
                Utilities::log("ReturnMessage: " . $transMsg . " ErrorCode: " . $errorCode);
                $this->_sendResponse(200, CJSON::encode(CommonController::retMsgRegisterMemberBT($module,'','', $errorCode, $transMsg)));
                $logMessage = 'Name should consist of letters only.';
                $logger->log($logger->logdate, " [REGISTERMEMBERBT ERROR] ", $logMessage);
                $apiDetails = 'REGISTERMEMBERBT-Failed: Invalid input parameters. ';
                $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, '', $apiDetails, '', 2);
                if($isInserted == 0) {
                    $logMessage = "Failed to insert to APILogs.";
                    $logger->log($logger->logdate, " [REGISTERMEMBERBT ERROR] ", $logMessage);
                }
                exit;
            }
            else if(strlen($request['MobileNo']) < 9) {
                $transMsg = "Mobile number should not be less than 9 digits long.";
                $errorCode = 15;
                Utilities::log("ReturnMessage: " . $transMsg . " ErrorCode: " . $errorCode);
                $this->_sendResponse(200, CJSON::encode(CommonController::retMsgRegisterMemberBT($module,'','', $errorCode, $transMsg)));
                $logMessage = 'Mobile number should not be less than 9 digits long.';
                $logger->log($logger->logdate, " [REGISTERMEMBERBT ERROR] ", $logMessage);
                $apiDetails = 'REGISTERMEMBERBT-Failed: Invalid input parameters. ';
                $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, '', $apiDetails, '', 2);
                if($isInserted == 0) {
                    $logMessage = "Failed to insert to APILogs.";
                    $logger->log($logger->logdate, " [REGISTERMEMBERBT ERROR] ", $logMessage);
                }
                exit;
            }
            else if((substr($request['MobileNo'],0, 2) != '09' && substr($request['MobileNo'],0, 3) != '639')) {
                $transMsg = "Mobile number should begin with either '09' or '639'.";
                $errorCode = 69;
                Utilities::log("ReturnMessage: " . $transMsg . " ErrorCode: " . $errorCode);
                $this->_sendResponse(200, CJSON::encode(CommonController::retMsgRegisterMemberBT($module,'','', $errorCode, $transMsg)));
                $logMessage = "Mobile number should begin with either '09' or '639'.";
                $logger->log($logger->logdate, " [REGISTERMEMBERBT ERROR] ", $logMessage);
                $apiDetails = 'REGISTERMEMBERBT-Failed: Invalid input parameters. ';
                $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, '', $apiDetails, '', 2);
                if($isInserted == 0) {
                    $logMessage = "Failed to insert to APILogs.";
                    $logger->log($logger->logdate, " [REGISTERMEMBERBT ERROR] ", $logMessage);
                }
                exit;
            }
            else if(!is_numeric($request['MobileNo'])) {
                $transMsg = "Mobile number should consist of numbers only.";
                $errorCode = 16;
                Utilities::log("ReturnMessage: " . $transMsg . " ErrorCode: " . $errorCode);
                $this->_sendResponse(200, CJSON::encode(CommonController::retMsgRegisterMemberBT($module,'','', $errorCode, $transMsg)));
                $logMessage = 'Mobile number should consist of numbers only.';
                $logger->log($logger->logdate, " [REGISTERMEMBERBT ERROR] ", $logMessage);
                $apiDetails = 'REGISTERMEMBERBT-Failed: Invalid input parameters. ';
                $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, '', $apiDetails, '', 2);
                if($isInserted == 0) {
                    $logMessage = "Failed to insert to APILogs.";
                    $logger->log($logger->logdate, " [REGISTERMEMBERBT ERROR] ", $logMessage);
                }
                exit;
            }
            else if(!Utilities::validateEmail($request['EmailAddress'])) {
                $transMsg = "Invalid Email Address.";
                $errorCode = 5;
                Utilities::log("ReturnMessage: " . $transMsg . " ErrorCode: " . $errorCode);
                $this->_sendResponse(200, CJSON::encode(CommonController::retMsgRegisterMemberBT($module,'','', $errorCode, $transMsg)));
                $logMessage = 'Invalid Email Address.';
                $logger->log($logger->logdate, " [REGISTERMEMBERBT ERROR] ", $logMessage);
                $apiDetails = 'REGISTERMEMBERBT-Failed: Invalid input parameters. ';
                $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, '', $apiDetails, '', 2);
                if($isInserted == 0) {
                    $logMessage = "Failed to insert to APILogs.";
                    $logger->log($logger->logdate, " [REGISTERMEMBERBT ERROR] ", $logMessage);
                }
                exit;
            }
            else if($this->_isRealDate($request['Birthdate']) == FALSE) {
                $transMsg = "Please input a valid Date (yyyy-mm-dd).";
                $errorCode = 80;
                Utilities::log("ReturnMessage: " . $transMsg . " ErrorCode: " . $errorCode);
                $this->_sendResponse(200, CJSON::encode(CommonController::retMsgRegisterMemberBT($module,'','', $errorCode, $transMsg)));
                $logMessage = 'Please input a valid Date.';
                $logger->log($logger->logdate, " [REGISTERMEMBERBT ERROR] ", $logMessage);
                $apiDetails = 'REGISTERMEMBERBT-Failed: Invalid input parameters. ';
                $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, '', $apiDetails, '', 2);
                if($isInserted == 0) {
                    $logMessage = "Failed to insert to APILogs.";
                    $logger->log($logger->logdate, " [REGISTERMEMBERBT ERROR] ", $logMessage);
                }
                exit;   
            }
//            else if(ctype_alnum($request['Password']) == FALSE || ctype_alnum($request['IDNumber']) == FALSE ) {
//                $transMsg = "Password and ID Number should consist of letters and numbers only.";
//                $errorCode = 18;
//                Utilities::log("ReturnMessage: " . $transMsg . " ErrorCode: " . $errorCode);
//                $this->_sendResponse(200, CJSON::encode(CommonController::retMsgRegisterMemberBT($module,'','', $errorCode, $transMsg)));
//                $logMessage = 'Password and ID Number should consist of letters and numbers only.';
//                $logger->log($logger->logdate, " [REGISTERMEMBERBT ERROR] ", $logMessage);
//                $apiDetails = 'REGISTERMEMBERBT-Failed: Invalid input parameters. ';
//                $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, '', $apiDetails, '', 2);
//                if($isInserted == 0) {
//                    $logMessage = "Failed to insert to APILogs.";
//                    $logger->log($logger->logdate, " [REGISTERMEMBERBT ERROR] ", $logMessage);
//                }
//                exit;
//            }
//            else if($request['Password'] != '' && strlen($request['Password']) < 5) {
//                $transMsg = "Password should not be less than 5 characters long.";
//                $errorCode = 19;
//                Utilities::log("ReturnMessage: " . $transMsg . " ErrorCode: " . $errorCode);
//                $this->_sendResponse(200, CJSON::encode(CommonController::retMsgRegisterMemberBT($module,'','', $errorCode, $transMsg)));
//                $logMessage = 'Password should not be less than 5 characters long.';
//                $logger->log($logger->logdate, " [REGISTERMEMBERBT ERROR] ", $logMessage);
//                $apiDetails = 'REGISTERMEMBERBT-Failed: Invalid input parameters. ';
//                $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, '', $apiDetails, '', 2);
//                if($isInserted == 0) {
//                    $logMessage = "Failed to insert to APILogs.";
//                    $logger->log($logger->logdate, " [REGISTERMEMBERBT ERROR] ", $logMessage);
//                }
//                exit;
//            }
//            else if($request['IDPresented'] != 1 && $request['IDPresented'] != 2 && $request['IDPresented'] != 3 && $request['IDPresented'] != 4 && $request['IDPresented'] != 5 && $request['IDPresented'] != 6 && $request['IDPresented'] != 7 && $request['IDPresented'] != 8 && $request['IDPresented'] != 9) {
//                $transMsg = "Please input a valid ID Presented (1 to 9).";
//                $errorCode = 78;
//                Utilities::log("ReturnMessage: " . $transMsg . " ErrorCode: " . $errorCode);
//                $this->_sendResponse(200, CJSON::encode(CommonController::retMsgRegisterMemberBT($module,'','', $errorCode, $transMsg)));
//                $logMessage = 'Please input a valid ID Presented (1 to 9).';
//                $logger->log($logger->logdate, " [REGISTERMEMBERBT ERROR] ", $logMessage);
//                $apiDetails = 'REGISTERMEMBERBT-Failed: Invalid input parameters. ';
//                $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, '', $apiDetails, '', 2);
//                if($isInserted == 0) {
//                    $logMessage = "Failed to insert to APILogs.";
//                    $logger->log($logger->logdate, " [REGISTERMEMBERBT ERROR] ", $logMessage);
//                }
//                exit;
//            }
            else {
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
                //$age = number_format((abs(strtotime($birthdate) - strtotime(date('Y-m-d'))) / 60 / 60 / 24 / 365), 0);
//                $password = trim($request['Password']);
//                $idPresented = trim($request['IDPresented']);
//                $idNumber = trim($request['IDNumber']);
                $tz = new DateTimeZone("Asia/Taipei");
                $age = DateTime::createFromFormat('Y-m-d', $birthdate, $tz)->diff(new DateTime('now', $tz))->y;
                
                $refID = $firstname.' '.$lastname;

                //check if member is blacklisted
                $isBlackListed = $blackListsModel->checkIfBlackListed($firstname, $lastname, $birthdate, 3);
                //check if email is active and existing in live membership db
                $activeEmail = $membershipTempModel->checkIfActiveVerifiedEmail($emailAddress);
                

                if($activeEmail['COUNT(MID)'] > 0) {
                    $transMsg = "Sorry, " . $emailAddress . " already belongs to an existing account. Please enter another email address.";
                    $errorCode = 21;
                    Utilities::log("ReturnMessage: " . $transMsg . " ErrorCode: " . $errorCode);
                    $this->_sendResponse(200, CJSON::encode(CommonController::retMsgRegisterMemberBT($module,'','', $errorCode, $transMsg)));
                    $logMessage = 'Sorry, ' . $emailAddress . ' already belongs to an existing account. Please enter another email address.';
                    $logger->log($logger->logdate, " [REGISTERMEMBERBT ERROR] ", $logMessage);
                    $apiDetails = 'REGISTERMEMBERBT-Failed: Email is already used. EmailAddress = '.$emailAddress;
                    $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, $refID, $apiDetails, '', 2);
                    if($isInserted == 0) {
                        $logMessage = "Failed to insert to APILogs.";
                        $logger->log($logger->logdate, " [REGISTERMEMBERBT ERROR] ", $logMessage);
                    }
                    exit;
                }
                else if($isBlackListed['Count'] > 0) {
                    $transMsg = "Registration cannot proceed. Please contact Customer Service.";
                    $errorCode = 22;
                    Utilities::log("ReturnMessage: " . $transMsg . " ErrorCode: " . $errorCode);
                    $this->_sendResponse(200, CJSON::encode(CommonController::retMsgRegisterMemberBT($module,'','', $errorCode, $transMsg)));
                    $logMessage = 'Registration cannot proceed. Please contact Customer Service.';
                    $logger->log($logger->logdate, " [REGISTERMEMBERBT ERROR] ", $logMessage);
                    $apiDetails = 'REGISTERMEMBERBT-Failed: Player is blacklisted. Name = '.$firstname.' '.$lastname.', Birthdate = '.$birthdate;
                    $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, $refID, $apiDetails, '', 2);
                    if($isInserted == 0) {
                        $logMessage = "Failed to insert to APILogs.";
                        $logger->log($logger->logdate, " [REGISTERMEMBERBT ERROR] ", $logMessage);
                    }
                    exit;
                }
                else if($age < 21) {
                    $transMsg = "Must be at least 21 years old to register.";
                    $errorCode = 89;
                    Utilities::log("ReturnMessage: " . $transMsg . " ErrorCode: " . $errorCode);
                    $this->_sendResponse(200, CJSON::encode(CommonController::retMsgRegisterMemberBT($module,'','', $errorCode, $transMsg)));
                    $logMessage = 'Must be at least 21 years old to register.';
                    $logger->log($logger->logdate, " [REGISTERMEMBERBT ERROR] ", $logMessage);
                    $apiDetails = 'REGISTERMEMBERBT-Failed: Player is under 21. Name = '.$firstname.' '.$lastname.', Birthdate = '.$birthdate;
                    $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, $refID, $apiDetails, '', 2);
                    if($isInserted == 0) {
                        $logMessage = "Failed to insert to APILogs.";
                        $logger->log($logger->logdate, " [REGISTERMEMBERBT ERROR] ", $logMessage);
                    }
                    exit;
                }
                else {
                    //check if email is already verified in temp table
                    $tempEmail = $membershipTempModel->checkTempVerifiedEmail($emailAddress);

                    if($tempEmail['COUNT(a.MID)'] > 0) {

                        $transMsg = "Email is already verified. Please choose a different email address.";
                        $errorCode = 52;
                        Utilities::log("ReturnMessage: " . $transMsg . " ErrorCode: " . $errorCode);
                        $this->_sendResponse(200, CJSON::encode(CommonController::retMsgRegisterMemberBT($module,'','', $errorCode, $transMsg)));
                        $logMessage = 'Email is already verified. Please choose a different email address.';
                        $logger->log($logger->logdate, " [REGISTERMEMBERBT ERROR] ", $logMessage);
                        $apiDetails = 'REGISTERMEMBERBT-Failed: Email is already verified. Email = '.$emailAddress;
                        $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, $refID, $apiDetails, '', 2);
                        if($isInserted == 0) {
                            $logMessage = "Failed to insert to APILogs.";
                            $logger->log($logger->logdate, " [REGISTERMEMBERBT ERROR] ", $logMessage);
                        }
                        exit;
                    }
                    else {
                        $lastInsertedMID = $membershipTempModel->registerBT($emailAddress, $firstname, $lastname, $mobileNumber, $birthdate); // ,$password, $idPresented, $idNumber);
                        
                        if($lastInsertedMID > 0) {
                            $couponBatchID = Yii::app()->params["couponBatchID"];
//                                if(isset($session['MID'])) {
//                                    $ID = $session['MID'];
//                                    $mpSessionID = $session['SessionID'];
//                                }
                      //      else {
                                $MID = $lastInsertedMID;
                                $mpSessionID = '';
                               // $emailAddress = 'guest';
                           // }

                                $memberInfos = $membershipTempModel->getTempMemberInfoForSMS($lastInsertedMID);
                                
                                //match to 09 or 639 in mobile number
                                $match = substr($memberInfos['MobileNumber'], 0, 3);
                                if($match == "639"){
                                    $mncount = count($memberInfos["MobileNumber"]);
                                    if(!$mncount == 12){
                                        $message = "Failed to send SMS. Invalid Mobile Number.";
                                        $logger->log($logger->logdate,"[REGISTERMEMBERBT ERROR] ", $message);
                                        $apiDetails = "REGISTERMEMBERBT-Failed: Failed to send SMS. Invalid Mobile Number [MID = $lastInsertedMID].";
                                        $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, $refID, $apiDetails, '', 2);
                                        if($isInserted == 0) {
                                            $logMessage = "Failed to insert to APILogs.";
                                            $logger->log($logger->logdate, " [REGISTERMEMBERBT ERROR] ", $logMessage);
                                        }

                                    } else {
     
                                        $coupons = $couponsModel->getCoupon($couponBatchID);
            
                                        $couponNumber = $coupons['CouponCode'];
                                        $expiryDate = $coupons['ValidToDate'];
                                        $templateid = $ref_SMSApiMethodsModel->getSMSMethodTemplateID(Ref_SMSApiMethodsModel::PLAYER_REGISTRATION);
                                        
                                        $methodid = Ref_SMSApiMethodsModel::PLAYER_REGISTRATION;
                                        
                                        $mobileno = $memberInfos["MobileNumber"];
                                        if($coupons) {
                                            $templateidbt = $ref_SMSApiMethodsModel->getSMSMethodTemplateID(Ref_SMSApiMethodsModel::PLAYER_REGISTRATION_BT);
                                            $templateidbt = $templateidbt['SMSTemplateID'];
                                            $methodidbt = Ref_SMSApiMethodsModel::PLAYER_REGISTRATION_BT;
                                            $smslastinsertedidbt = $smsRequestLogsModel->insertSMSRequestLogs($methodidbt, $mobileno, $memberInfos["DateCreated"]);
                                        }
                                        else {
                                               
                                            $smslastinsertedidbt = 0; 
                                        }
                                        $smslastinsertedid = $smsRequestLogsModel->insertSMSRequestLogs($methodid, $mobileno, $memberInfos["DateCreated"]);
                                        
                                        if(($smslastinsertedid != 0 && $smslastinsertedid != '') && ($smslastinsertedidbt != 0 && $smslastinsertedidbt != '') ){
                                            $trackingid = "SMSR".$smslastinsertedid;
                                            $trackingidbt = "SMSR".$smslastinsertedidbt;
                                            $apiURL = Yii::app()->params["SMSURI"];    
                                            $app_id = Yii::app()->params["app_id"];    
                                            $membershipSMSApi = new MembershipSmsAPI($apiURL, $app_id);
                                            $smsresult = $membershipSMSApi->sendRegistration($mobileno, $templateid['SMSTemplateID'], $memberInfos["DateCreated"], $memberInfos["TemporaryAccountCode"], $trackingid);
                                            $smsresult2 = $membershipSMSApi->sendRegistrationBT($mobileno, $templateidbt['SMSTemplateID'], $expiryDate, $couponNumber, $trackingidbt);
                                            
                                            
                                            if(isset($smsresult['status']) && isset($smsresult2['status'])){
                                                if($smsresult['status'] != 1 && $smsresult2['status'] != 1){
                                                    $message = "Failed to send SMS.";
                                                    $logger->log($logger->logdate,"[REGISTERMEMBERBT ERROR] ", $message);
                                                    $apiDetails = "REGISTERMEMBERBT-Failed: Failed to send SMS. [MID = $lastInsertedMID].";
                                                    $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, $refID, $apiDetails, '', 2);
                                                    if($isInserted == 0) {
                                                        $logMessage = "Failed to insert to APILogs.";
                                                        $logger->log($logger->logdate, " [REGISTERMEMBERBT ERROR] ", $logMessage);
                                                    }

                                                }
                                                else {
                                                    $isUpdated = $coupons->updateCouponStatus($couponBatchID, $couponNumber, $MID);
                                                    if(!$isUpdated) {
                                                        $logMessage = "Failed to update coupon status.";
                                                        $logger->log($logger->logdate, " [REGISTERMEMBERBT ERROR] ", $logMessage);
                                                        exit;
                                                    }
                                                    $helpers = new Helpers();
                                                    $helpers->sendEmailBT($emailAddress, $firstname . ' ' . $lastname, $couponNumber, $expiryDate);
                                                    $transMsg = "You have successfully registered! An active Temporary Account will be sent to your email address or mobile number, which can be used to start session or credit points in the absence of Membership Card. Please note that your Registered Account and Temporary Account will be activated only after 24 hours.";
                                                    $errorCode = 0;
                                                    Utilities::log("ReturnMessage: " . $transMsg . " ErrorCode: " . $errorCode);
                                                    $this->_sendResponse(200, CJSON::encode(CommonController::retMsgRegisterMemberBT($module,$couponNumber,$expiryDate, $errorCode, nl2br($transMsg))));
                                                    $logMessage = 'Registration is successful.';
                                                    $logger->log($logger->logdate, " [REGISTERMEMBERBT SUCCESSFUL] ", $logMessage);
                                                    $apiDetails = 'REGISTERMEMBERBT-Success: Registration is successful. MID = '.$lastInsertedMID;
                                                    $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, $refID, $apiDetails, '', 1);
                                                    if($isInserted == 0) {
                                                        $logMessage = "Failed to insert to APILogs.";
                                                        $logger->log($logger->logdate, " [REGISTERMEMBERBT ERROR] ", $logMessage);
                                                    }

                                                }
                                            }
                                            else {
                                                $transMsg = 'Failed to get response from membershipsms api.';
                                                $errorCode = 90;
                                                Utilities::log("ReturnMessage: " . $transMsg . " ErrorCode: " . $errorCode);
                                                $this->_sendResponse(200, CJSON::encode(CommonController::retMsgRegisterMemberBT($module,'','', $errorCode, nl2br($transMsg))));
                                                $logMessage = 'Failed to get response from membershipsms api.';
                                                $logger->log($logger->logdate, " [REGISTERMEMBERBT ERROR] ", $logMessage);
                                                $apiDetails = 'REGISTERMEMBERBT-Failed: Failed to get response from membershipsms api. MID = '.$lastInsertedMID;
                                                $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, $refID, $apiDetails, '', 2);
                                                if($isInserted == 0) {
                                                    $logMessage = "Failed to insert to APILogs.";
                                                    $logger->log($logger->logdate, " [REGISTERMEMBERBT ERROR] ", $logMessage);
                                                }
                                            }
                                        } else {
                                            $message = "Failed to send SMS: Error on logging event in database.";
                                            $logger->log($logger->logdate,"[REGISTERMEMBERBT ERROR] ", $message);
                                            $apiDetails = "REGISTERMEMBERBT-Failed: Failed to send SMS. Error on logging event in database. [MID = $lastInsertedMID].";
                                            $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, $refID, $apiDetails, '', 2);
                                            if($isInserted == 0) {
                                                $logMessage = "Failed to insert to APILogs.";
                                                $logger->log($logger->logdate, " [REGISTERMEMBERBT ERROR] ", $logMessage);
                                            }
                                            
                                            $errorCode = 88;
                                            Utilities::log("ReturnMessage: " . $message . " ErrorCode: " . $errorCode);
                                            $this->_sendResponse(200, CJSON::encode(CommonController::retMsgRegisterMemberBT($module,'','', $errorCode, $message)));
                                            
                                           
                                           

                                        }
                                    }
                                } else {
                                    $match = substr($memberInfos["MobileNumber"], 0, 2);
                                    if($match == "09"){
                                        $mncount = count($memberInfos["MobileNumber"]);

                                        if(!$mncount == 11){
                                             $message = "Failed to send SMS: Invalid Mobile Number.";
                                             $logger->log($logger->logdate,"[REGISTERMEMBERBT ERROR] ", $message);
                                             $apiDetails = "REGISTERMEMBERBT-Failed: Failed to send SMS. Invalid Mobile Number [MID = $lastInsertedMID].";
                                             $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, $refID, $apiDetails, '', 2);
                                             if($isInserted == 0) {
                                                $logMessage = "Failed to insert to APILogs.";
                                                $logger->log($logger->logdate, " [REGISTERMEMBERBT ERROR] ", $logMessage);
                                             }

                                         } else {
     
                                            $coupons = $couponsModel->getCoupon($couponBatchID);
                                            
                                            $couponNumber = $coupons['CouponCode'];
                                            $expiryDate = $coupons['ValidToDate'];
                                            $expiryDate = date("Y-m-d", strtotime($expiryDate));
                                            $cpNumber = $memberInfos["MobileNumber"];
                                            $mobileno = $this->formatMobileNumber($cpNumber);
                                            $templateid = $ref_SMSApiMethodsModel->getSMSMethodTemplateID(Ref_SMSApiMethodsModel::PLAYER_REGISTRATION);
                                            $templateid = $templateid['SMSTemplateID'];
                                            
                                            if($coupons) {
                                                $templateidbt = $ref_SMSApiMethodsModel->getSMSMethodTemplateID(Ref_SMSApiMethodsModel::PLAYER_REGISTRATION_BT);
                                                $templateidbt = $templateidbt['SMSTemplateID'];
                                                $methodidbt = Ref_SMSApiMethodsModel::PLAYER_REGISTRATION_BT;
                                                $smslastinsertedidbt = $smsRequestLogsModel->insertSMSRequestLogs($methodidbt, $mobileno, $memberInfos["DateCreated"]);
                                            }
                                            else {
                                               
                                               $smslastinsertedidbt = 0; 
                                            }
                                            
                                            
                                            
                                            $methodid = Ref_SMSApiMethodsModel::PLAYER_REGISTRATION;
                                            
                                            $smslastinsertedid = $smsRequestLogsModel->insertSMSRequestLogs($methodid, $mobileno, $memberInfos["DateCreated"]);
                                            
                                            if(($smslastinsertedid != 0 && $smslastinsertedid != '') && ($smslastinsertedidbt != 0 && $smslastinsertedidbt != '') ){
                                                $trackingid = "SMSR".$smslastinsertedid;
                                                $trackingidbt = "SMSR".$smslastinsertedidbt;
                                                $apiURL = Yii::app()->params['SMSURI'];   
                                                $app_id = Yii::app()->params['app_id'];  
                                                $membershipSMSApi = new MembershipSmsAPI($apiURL, $app_id);
                                                $smsresult = $membershipSMSApi->sendRegistration($mobileno, $templateid, $memberInfos["DateCreated"], $memberInfos["TemporaryAccountCode"], $trackingid);
                                                $smsresult2 = $membershipSMSApi->sendRegistrationBT($mobileno, $templateidbt, $expiryDate, $couponNumber, $trackingidbt);
                                                
                                                if(isset($smsresult['status']) && isset($smsresult2['status'])){
                                                    if($smsresult['status'] != 1 && $smsresult2['status'] != 1){

//                                                        $message = "Failed to send SMS.";
//                                                        $logger->log($logger->logdate,"[REGISTERMEMBERBT ERROR] ", $message);
//                                                        $apiDetails = "REGISTERMEMBERBT-Failed: Failed to send SMS. [MID = $lastInsertedMID].";
//                                                        $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, $refID, $apiDetails, '', 2);
//                                                        if($isInserted == 0) {
//                                                            $logMessage = "Failed to insert to APILogs.";
//                                                            $logger->log($logger->logdate, " [REGISTERMEMBERBT ERROR] ", $logMessage);
//                                                        }
                                                        $transMsg = 'Failed to get response from membershipsms api.';
                                                    $errorCode = 90;
                                                    Utilities::log("ReturnMessage: " . $transMsg . " ErrorCode: " . $errorCode);
                                                    $this->_sendResponse(200, CJSON::encode(CommonController::retMsgRegisterMemberBT($module,'','', $errorCode, nl2br($transMsg))));
                                                    $logMessage = 'Failed to get response from membershipsms api.';
                                                    $logger->log($logger->logdate, " [REGISTERMEMBERBT ERROR] ", $logMessage);
                                                    $apiDetails = 'REGISTERMEMBERBT-Failed: Failed to get response from membershipsms api. MID = '.$lastInsertedMID;
                                                    $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, $refID, $apiDetails, '', 2);
                                                    if($isInserted == 0) {
                                                        $logMessage = "Failed to insert to APILogs.";
                                                        $logger->log($logger->logdate, " [REGISTERMEMBERBT ERROR] ", $logMessage);
                                                    }

                                                    }
                                                    else {
                                                        $isUpdated = $couponsModel->updateCouponStatus($couponBatchID, $couponNumber, $MID);
                                                        if(!$isUpdated) {
                                                            $logMessage = "Failed to update coupon status.";
                                                            $logger->log($logger->logdate, " [REGISTERMEMBERBT ERROR] ", $logMessage);
                                                            exit;
                                                        }
                                                        $helpers = new Helpers();
                                                        $helpers->sendEmailBT($emailAddress, $firstname . ' ' . $lastname, $couponNumber, $expiryDate);
                                                        
                                                        $transMsg = "You have successfully registered! An active Temporary Account will be sent to your email address or mobile number, which can be used to start session or credit points in the absence of Membership Card. Please note that your Registered Account and Temporary Account will be activated only after 24 hours.";
                                                        $errorCode = 0;
                                                        Utilities::log("ReturnMessage: " . $transMsg . " ErrorCode: " . $errorCode);
                                                        $this->_sendResponse(200, CJSON::encode(CommonController::retMsgRegisterMemberBT($module, $couponNumber, $expiryDate, $errorCode, nl2br($transMsg))));
                                                        $logMessage = 'Registration is successful.';
                                                        $logger->log($logger->logdate, " [REGISTERMEMBERBT SUCCESSFUL] ", $logMessage);
                                                        $apiDetails = 'REGISTERMEMBERBT-Success: Registration is successful. MID = '.$lastInsertedMID;
                                                        //var_dump($apiMethod, $refID, $apiDetails);
                                                        $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, $refID, $apiDetails, '', 1);
                                                        
                                                        if($isInserted == 0) {
                                                            $logMessage = "Failed to insert to APILogs.";
                                                            $logger->log($logger->logdate, " [REGISTERMEMBERBT ERROR] ", $logMessage);
                                                        }

                                                    }
                                                }
                                                else {
                                                    $transMsg = 'Failed to get response from membershipsms api.';
                                                    $errorCode = 90;
                                                    Utilities::log("ReturnMessage: " . $transMsg . " ErrorCode: " . $errorCode);
                                                    $this->_sendResponse(200, CJSON::encode(CommonController::retMsgRegisterMemberBT($module,'','', $errorCode, nl2br($transMsg))));
                                                    $logMessage = 'Failed to get response from membershipsms api.';
                                                    $logger->log($logger->logdate, " [REGISTERMEMBERBT ERROR] ", $logMessage);
                                                    $apiDetails = 'REGISTERMEMBERBT-Failed: Failed to get response from membershipsms api. MID = '.$lastInsertedMID;
                                                    $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, $refID, $apiDetails, '', 2);
                                                    if($isInserted == 0) {
                                                        $logMessage = "Failed to insert to APILogs.";
                                                        $logger->log($logger->logdate, " [REGISTERMEMBERBT ERROR] ", $logMessage);
                                                    }
                                                }  
                                                
                                            } else {
                                                $message = "Failed to send SMS: Error on logging event in database.";
                                                $logger->log($logger->logdate,"[BTREGISTRATION ERROR] ", $message);
                                                $apiDetails = "REGISTERMEMBERBT-Failed: Failed to send SMS. Error on logging event in database [MID = $lastInsertedMID].";
                                                $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, $refID, $apiDetails, '', 2);
                                                if($isInserted == 0) {
                                                    $logMessage = "Failed to insert to APILogs.";
                                                    $logger->log($logger->logdate, " [REGISTERMEMBERBT ERROR] ", $logMessage);
                                                }
                                                $errorCode = 88;
                                                Utilities::log("ReturnMessage: " . $message . " ErrorCode: " . $errorCode);
                                                $this->_sendResponse(200, CJSON::encode(CommonController::retMsgRegisterMemberBT($module,'','', $errorCode, $message)));
                                                

                                            }
                                         }
                                    } else {
                                        $message = "Failed to send SMS: Invalid Mobile Number.";
                                        $logger->log($logger->logdate,"[BTREGISTRATION ERROR] ", $message);
                                        $apiDetails = "REGISTERMEMBERBT-Failed: Failed to send SMS. Invalid Mobile Number [MID = $lastInsertedMID].";
                                        $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, $refID, $apiDetails, '', 2);
                                        if($isInserted == 0) {
                                            $logMessage = "Failed to insert to APILogs.";
                                            $logger->log($logger->logdate, " [REGISTERMEMBERBT ERROR] ", $logMessage);
                                        }

                                    }
                                }

                            $auditTrailModel->logEvent(AuditTrailModel::API_REGISTER_MEMBER_BT, 'Email: '.$emailAddress, array('MID' => $MID, 'SessionID' => $mpSessionID));

                        }
                        else {
                            //check if email is already verified in temp table
                            $tempEmail = $membershipTempModel->checkTempVerifiedEmail($emailAddress);
                            if($tempEmail['COUNT(a.MID)'] > 0) {
                                $transMsg = "Email is already verified. Please choose a different email address.";
                                $errorCode = 52;
                                Utilities::log("ReturnMessage: " . $transMsg . " ErrorCode: " . $errorCode);
                                $this->_sendResponse(200, CJSON::encode(CommonController::retMsgRegisterMemberBT($module,'','', $errorCode, $transMsg)));
                                $logMessage = 'Email is already verified. Please choose a different email address.';
                                $logger->log($logger->logdate, " [REGISTERMEMBERBT ERROR] ", $logMessage);
                                $apiDetails = 'REGISTERMEMBERBT-Failed: Email is already verified. Email = '.$emailAddress;
                                $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, $refID, $apiDetails, '', 2);
                                if($isInserted == 0) {
                                    $logMessage = "Failed to insert to APILogs.";
                                    $logger->log($logger->logdate, " [REGISTERMEMBERBT ERROR] ", $logMessage);
                                }
                                exit;
                            }
                            else {
                                $lastInsertedMID = $membershipTempModel->registerBT($emailAddress, $firstname, $lastname, $mobileNumber, $birthdate);

                                if($lastInsertedMID > 0) {


                                        $ID = 0;
                                        $mpSessionID = '';

                                        $memberInfos = $membershipTempModel->getTempMemberInfoForSMS($lastInsertedMID);

                                        //match to 09 or 639 in mobile number
                                        $match = substr($memberInfos['MobileNumber'], 0, 3);
                                        if($match == "639"){
                                            $mncount = count($memberInfos["MobileNumber"]);
                                            if(!$mncount == 12){
                                                $message = "Failed to send SMS. Invalid Mobile Number.";
                                                $logger->log($logger->logdate,"[REGISTERMEMBERBT ERROR] ", $message);
                                                $apiDetails = "REGISTERMEMBERBT-Failed: Failed to send SMS. Invalid Mobile Number [MID = $lastInsertedMID].";
                                                $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, $refID, $apiDetails, '', 2);
                                                if($isInserted == 0) {
                                                    $logMessage = "Failed to insert to APILogs.";
                                                    $logger->log($logger->logdate, " [REGISTERMEMBERBT ERROR] ", $logMessage);
                                                }

                                            } else {
                                                $coupons = $couponsModel->getCoupon($couponBatchID);
                                            
                                                $couponNumber = $coupons['CouponCode'];
                                                $expiryDate = $coupons['ValidToDate'];
                                                $expiryDate = date("Y-m-d", strtotime($expiryDate));
                                                $templateid = $ref_SMSApiMethodsModel->getSMSMethodTemplateID(Ref_SMSApiMethodsModel::PLAYER_REGISTRATION);
                                                
                                                $methodid = Ref_SMSApiMethodsModel::PLAYER_REGISTRATION;
                                                
                                                $mobileno = $memberInfos["MobileNumber"];
                                                if($coupons) {
                                                    $templateidbt = $ref_SMSApiMethodsModel->getSMSMethodTemplateID(Ref_SMSApiMethodsModel::PLAYER_REGISTRATION_BT);
                                                    $templateidbt = $templateidbt['SMSTemplateID'];
                                                    $methodidbt = Ref_SMSApiMethodsModel::PLAYER_REGISTRATION_BT;
                                                    $smslastinsertedidbt = $smsRequestLogsModel->insertSMSRequestLogs($methodidbt, $mobileno, $memberInfos["DateCreated"]);
                                                }
                                                else {
                                               
                                                    $smslastinsertedidbt = 0; 
                                                }
                                                $smslastinsertedid = $smsRequestLogsModel->insertSMSRequestLogs($methodid, $mobileno, $memberInfos["DateCreated"]);
                                                
                                                if(($smslastinsertedid != 0 && $smslastinsertedid != '') && ($smslastinsertedidbt != 0 && $smslastinsertedidbt != '') ){
                                                    $trackingid = "SMSR".$smslastinsertedid;
                                                    $trackingidbt = "SMSR".$smslastinsertedidbt;
                                                    $apiURL = Yii::app()->params["SMSURI"];    
                                                    $app_id = Yii::app()->params["app_id"];    
                                                    $membershipSMSApi = new MembershipSmsAPI($apiURL, $app_id);
                                                    $smsresult = $membershipSMSApi->sendRegistration($mobileno, $templateid['SMSTemplateID'], $memberInfos["DateCreated"], $memberInfos["TemporaryAccountCode"], $trackingid);
                                                    $smsresult2 = $membershipSMSApi->sendRegistrationBT($mobileno, $templateidbt['SMSTemplateID'], $expiryDate, $couponNumber, $trackingidbt);
                                                    if(isset($smsresult['status']) && isset($smsresult2['status'])){
                                                        if($smsresult['status'] != 1 && $smsresult2['status'] != 1){
                                                            $message = "Failed to send SMS.";
                                                            $logger->log($logger->logdate,"[REGISTERMEMBERBT ERROR] ", $message);
                                                            $apiDetails = "REGISTERMEMBERBT-Failed: Failed to send SMS. [MID = $lastInsertedMID].";
                                                            $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, $refID, $apiDetails, '', 2);
                                                            if($isInserted == 0) {
                                                                $logMessage = "Failed to insert to APILogs.";
                                                                $logger->log($logger->logdate, " [REGISTERMEMBERBT ERROR] ", $logMessage);
                                                            }

                                                        }
                                                        else {
                                                            $isUpdated = $couponsModel->updateCouponStatus($couponBatchID, $couponNumber, $MID);
                                                            if(!$isUpdated) {
                                                                $logMessage = "Failed to update coupon status.";
                                                                $logger->log($logger->logdate, " [REGISTERMEMBERBT ERROR] ", $logMessage);
                                                                exit;
                                                            }
                                                            $helpers = new Helpers();
                                                            $helpers->sendEmailBT($emailAddress, $firstname . ' ' . $lastname, $couponNumber, $expiryDate);
                                                            
                                                            $transMsg = "You have successfully registered! An active Temporary Account will be sent to your email address or mobile number, which can be used to start session or credit points in the absence of Membership Card. Please note that your Registered Account and Temporary Account will be activated only after 24 hours.";
                                                            $errorCode = 0;
                                                            Utilities::log("ReturnMessage: " . $transMsg . " ErrorCode: " . $errorCode);
                                                            $this->_sendResponse(200, CJSON::encode(CommonController::retMsgRegisterMemberBT($module, $errorCode, nl2br($transMsg))));
                                                            $logMessage = 'Registration is successful.';
                                                            $logger->log($logger->logdate, " [REGISTERMEMBERBT SUCCESSFUL] ", $logMessage);
                                                            $apiDetails = 'REGISTERMEMBERBT-Success: Registration is successful. MID = '.$lastInsertedMID;
                                                            $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, $refID, $apiDetails, '', 1);
                                                            if($isInserted == 0) {
                                                                $logMessage = "Failed to insert to APILogs.";
                                                                $logger->log($logger->logdate, " [REGISTERMEMBERBT ERROR] ", $logMessage);
                                                            }

                                                        }
                                                    }
                                                    else {
                                                        $transMsg = 'Failed to get response from membershipsms api.';
                                                        $errorCode = 90;
                                                        Utilities::log("ReturnMessage: " . $transMsg . " ErrorCode: " . $errorCode);
                                                        $this->_sendResponse(200, CJSON::encode(CommonController::retMsgRegisterMemberBT($module,'','', $errorCode, nl2br($transMsg))));
                                                        $logMessage = 'Failed to get response from membershipsms api.';
                                                        $logger->log($logger->logdate, " [REGISTERMEMBERBT ERROR] ", $logMessage);
                                                        $apiDetails = 'REGISTERMEMBERBT-Failed: Failed to get response from membershipsms api. MID = '.$lastInsertedMID;
                                                        $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, $refID, $apiDetails, '', 2);
                                                        if($isInserted == 0) {
                                                            $logMessage = "Failed to insert to APILogs.";
                                                            $logger->log($logger->logdate, " [REGISTERMEMBERBT ERROR] ", $logMessage);
                                                        }
                                                    }
                                                } else {
                                                    $message = "Failed to send SMS: Error on logging event in database.";
                                                    $logger->log($logger->logdate,"[REGISTERMEMBERBT ERROR] ", $message);
                                                    $apiDetails = "REGISTERMEMBERBT-Failed: Failed to send SMS. Error on logging event in database. [MID = $lastInsertedMID].";
                                                    $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, $refID, $apiDetails, '', 2);
                                                    if($isInserted == 0) {
                                                        $logMessage = "Failed to insert to APILogs.";
                                                        $logger->log($logger->logdate, " [REGISTERMEMBERBT ERROR] ", $logMessage);
                                                    }
                                                    $errorCode = 88;
                                                    Utilities::log("ReturnMessage: " . $message . " ErrorCode: " . $errorCode);
                                                    $this->_sendResponse(200, CJSON::encode(CommonController::retMsgRegisterMemberBT($module,'','', $errorCode, $message)));

                                                }
                                            }
                                        } else {
                                            $match = substr($memberInfos["MobileNumber"], 0, 2);
                                            if($match == "09"){
                                                $mncount = count($memberInfos["MobileNumber"]);
                                                if(!$mncount == 11){
                                                     $message = "Failed to send SMS: Invalid Mobile Number.";
                                                     $logger->log($logger->logdate,"[REGISTERMEMBERBT ERROR] ", $message);
                                                     $apiDetails = "REGISTERMEMBERBT-Failed: Failed to send SMS. Invalid Mobile Number [MID = $lastInsertedMID].";
                                                     $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, $refID, $apiDetails, '', 2);
                                                     if($isInserted == 0) {
                                                        $logMessage = "Failed to insert to APILogs.";
                                                        $logger->log($logger->logdate, " [REGISTERMEMBERBT ERROR] ", $logMessage);
                                                     }

                                                 } else {
                                                    $coupons = $couponsModel->getCoupon($couponBatchID);
                                            
                                                    $couponNumber = $coupons['CouponCode'];
                                                    $expiryDate = $coupons['ValidToDate'];
                                                    $expiryDate = date("Y-m-d", strtotime($expiryDate));
                                                    $mobileno = str_replace("09", "639", $memberInfos["MobileNumber"]);
                                                    $templateid = $ref_SMSApiMethodsModel->getSMSMethodTemplateID(Ref_SMSApiMethodsModel::PLAYER_REGISTRATION);
                                                    
                                                    $methodid = Ref_SMSApiMethodsModel::PLAYER_REGISTRATION;
                                                    
                                                    $smslastinsertedid = $smsRequestLogsModel->insertSMSRequestLogs($methodid, $mobileno, $memberInfos["DateCreated"]);
                                                    if($coupons) {
                                                        $templateidbt = $ref_SMSApiMethodsModel->getSMSMethodTemplateID(Ref_SMSApiMethodsModel::PLAYER_REGISTRATION_BT);
                                                        $templateidbt = $templateidbt['SMSTemplateID'];
                                                        $methodidbt = Ref_SMSApiMethodsModel::PLAYER_REGISTRATION_BT;
                                                        $smslastinsertedidbt = $smsRequestLogsModel->insertSMSRequestLogs($methodidbt, $mobileno, $memberInfos["DateCreated"]);
                                                    }
                                                    else {
                                               
                                                        $smslastinsertedidbt = 0; 
                                                    }
                                                    if(($smslastinsertedid != 0 && $smslastinsertedid != '') && ($smslastinsertedidbt != 0 && $smslastinsertedidbt != '') ){
                                                        $trackingid = "SMSR".$smslastinsertedid;
                                                        $trackingidbt = "SMSR".$smslastinsertedidbt;
                                                        $apiURL = Yii::app()->params['SMSURI'];   
                                                        $app_id = Yii::app()->params['app_id'];  
                                                        $membershipSMSApi = new MembershipSmsAPI($apiURL, $app_id);
                                                        $smsresult = $membershipSMSApi->sendRegistration($mobileno, $templateid['SMSTemplateID'], $memberInfos["DateCreated"], $memberInfos["TemporaryAccountCode"], $trackingid);
                                                        $smsresult2 = $membershipSMSApi->sendRegistrationBT($mobileno, $templateidbt['SMSTemplateID'], $expiryDate, $couponNumber, $trackingidbt);
                                                        
                                                        if(isset($smsresult['status']) && isset($smsresult2['status'])){
                                                            if($smsresult['status'] != 1 && $smsresult2['status'] != 1){
//                                                                $message = "Failed to send SMS.";
//                                                                $logger->log($logger->logdate,"[REGISTERMEMBERBT ERROR] ", $message);
//                                                                $apiDetails = "REGISTERMEMBERBT-Failed: Failed to send SMS. [MID = $lastInsertedMID].";
//                                                                $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, $refID, $apiDetails, '', 2);
//                                                                if($isInserted == 0) {
//                                                                    $logMessage = "Failed to insert to APILogs.";
//                                                                    $logger->log($logger->logdate, " [REGISTERMEMBERBT ERROR] ", $logMessage);
//                                                                }
                                                                $transMsg = 'Failed to get response from membershipsms api.';
                                                    $errorCode = 90;
                                                    Utilities::log("ReturnMessage: " . $transMsg . " ErrorCode: " . $errorCode);
                                                    $this->_sendResponse(200, CJSON::encode(CommonController::retMsgRegisterMemberBT($module,'','', $errorCode, nl2br($transMsg))));
                                                    $logMessage = 'Failed to get response from membershipsms api.';
                                                    $logger->log($logger->logdate, " [REGISTERMEMBERBT ERROR] ", $logMessage);
                                                    $apiDetails = 'REGISTERMEMBERBT-Failed: Failed to get response from membershipsms api. MID = '.$lastInsertedMID;
                                                    $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, $refID, $apiDetails, '', 2);
                                                    if($isInserted == 0) {
                                                        $logMessage = "Failed to insert to APILogs.";
                                                        $logger->log($logger->logdate, " [REGISTERMEMBERBT ERROR] ", $logMessage);
                                                    }

                                                            }
                                                            else {
                                                                $isUpdated = $couponsModel->updateCouponStatus($couponBatchID, $couponNumber, $MID);
                                                                if(!$isUpdated) {
                                                                    $logMessage = "Failed to update coupon status.";
                                                                    $logger->log($logger->logdate, " [REGISTERMEMBERBT ERROR] ", $logMessage);
                                                                    exit;
                                                                }
                                                                $helpers = new Helpers();
                                                                $helpers->sendEmailBT($emailAddress, $firstname . ' ' . $lastname, $couponNumber, $expiryDate);
                                                                $transMsg = "You have successfully registered! An active Temporary Account will be sent to your email address or mobile number, which can be used to start session or credit points in the absence of Membership Card. Please note that your Registered Account and Temporary Account will be activated only after 24 hours.";
                                                                $errorCode = 0;
                                                                Utilities::log("ReturnMessage: " . $transMsg . " ErrorCode: " . $errorCode);
                                                                $this->_sendResponse(200, CJSON::encode(CommonController::retMsgRegisterMemberBT($module, $errorCode, nl2br($transMsg))));
                                                                $logMessage = 'Registration is successful.';
                                                                $logger->log($logger->logdate, " [REGISTERMEMBERBT SUCCESSFUL] ", $logMessage);
                                                                $apiDetails = 'REGISTERMEMBERBT-Success: Registration is successful. MID = '.$lastInsertedMID;
                                                                $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, $refID, $apiDetails, '', 1);
                                                                if($isInserted == 0) {
                                                                    $logMessage = "Failed to insert to APILogs.";
                                                                    $logger->log($logger->logdate, " [REGISTERMEMBERBT ERROR] ", $logMessage);
                                                                }

                                                            }
                                                        }
                                                        else {
                                                            $transMsg = 'Failed to get response from membershipsms api.';
                                                            $errorCode = 90;
                                                            Utilities::log("ReturnMessage: " . $transMsg . " ErrorCode: " . $errorCode);
                                                            $this->_sendResponse(200, CJSON::encode(CommonController::retMsgRegisterMemberBT($module,'','', $errorCode, nl2br($transMsg))));
                                                            $logMessage = 'Failed to get response from membershipsms api.';
                                                            $logger->log($logger->logdate, " [REGISTERMEMBERBT ERROR] ", $logMessage);
                                                            $apiDetails = 'REGISTERMEMBERBT-Failed: Failed to get response from membershipsms api. MID = '.$lastInsertedMID;
                                                            $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, $refID, $apiDetails, '', 2);
                                                            if($isInserted == 0) {
                                                                $logMessage = "Failed to insert to APILogs.";
                                                                $logger->log($logger->logdate, " [REGISTERMEMBERBT ERROR] ", $logMessage);
                                                            }
                                                        }
                                                    } else {
                                                        $message = "Failed to send SMS: Error on logging event in database.";
                                                        $logger->log($logger->logdate,"[REGISTERMEMBERBT ERROR] ", $message);
                                                        $apiDetails = "REGISTERMEMBERBT-Failed: Failed to send SMS. Error on logging event in database [MID = $lastInsertedMID].";
                                                        $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, $refID, $apiDetails, '', 2);
                                                        if($isInserted == 0) {
                                                            $logMessage = "Failed to insert to APILogs.";
                                                            $logger->log($logger->logdate, " [REGISTERMEMBERBT ERROR] ", $logMessage);
                                                        }
                                                        $errorCode = 88;
                                                        Utilities::log("ReturnMessage: " . $message . " ErrorCode: " . $errorCode);
                                                        $this->_sendResponse(200, CJSON::encode(CommonController::retMsgRegisterMemberBT($module,'','', $errorCode, $message)));

                                                    }
                                                 }
                                            } else {
                                                $message = "Failed to send SMS: Invalid Mobile Number.";
                                                $logger->log($logger->logdate,"[REGISTERMEMBERBT ERROR] ", $message);
                                                $apiDetails = "REGISTERMEMBERBT-Failed: Failed to send SMS. Invalid Mobile Number [MID = $lastInsertedMID].";
                                                $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, $refID, $apiDetails, '', 2);
                                                if($isInserted == 0) {
                                                    $logMessage = "Failed to insert to APILogs.";
                                                    $logger->log($logger->logdate, " [REGISTERMEMBERBT ERROR] ", $logMessage);
                                                }

                                            }
                                        }


                                    $auditTrailModel->logEvent(AuditTrailModel::API_REGISTER_MEMBER_BT, 'Email: '.$emailAddress, array('ID' => $ID));


                                }
                                else {
                                    if(strpos($lastInsertedMID, " Integrity constraint violation: 1062 Duplicate entry") > 0) {
                                        $transMsg = "Sorry, " . $emailAddress . "already belongs to an existing account. Please enter another email address.";
                                        $errorCode = 21;
                                        Utilities::log("ReturnMessage: " . $transMsg . " ErrorCode: " . $errorCode);
                                        $this->_sendResponse(200, CJSON::encode(CommonController::retMsgRegisterMemberBT($module,'','', $errorCode, $transMsg)));
                                        $logMessage = "Sorry, " . $emailAddress . "already belongs to an existing account. Please enter another email address.";
                                        $logger->log($logger->logdate, " [REGISTERMEMBERBT ERROR] ", $logMessage);
                                        $apiDetails = 'REGISTERMEMBERBT-Failed: Email already exists. Please choose a different email address. Email = '.$emailAddress;
                                        $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, $refID, $apiDetails, '', 2);
                                        if($isInserted == 0) {
                                            $logMessage = "Failed to insert to APILogs.";
                                            $logger->log($logger->logdate, " [REGISTERMEMBERBT ERROR] ", $logMessage);
                                        }
                                        exit;
                                    }
                                    else {
                                        $transMsg = "Registration failed.";
                                        $errorCode = 53;
                                        Utilities::log("ReturnMessage: " . $transMsg . " ErrorCode: " . $errorCode);
                                        $this->_sendResponse(200, CJSON::encode(CommonController::retMsgRegisterMemberBT($module,'','', $errorCode, $transMsg)));
                                        $logMessage = "Registration failed.";
                                        $logger->log($logger->logdate, " [REGISTERMEMBERBT ERROR] ", $logMessage);
                                        $apiDetails = 'REGISTERMEMBERBT-Failed: Registration failed.';
                                        $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, $refID, $apiDetails, '', 2);
                                        if($isInserted == 0) {
                                            $logMessage = "Failed to insert to APILogs.";
                                            $logger->log($logger->logdate, " [REGISTERMEMBERBT ERROR] ", $logMessage);
                                        }
                                        exit;
                                    }
                                }

                            }
                        }

                    }
                }
            }
        }
        else {
            $transMsg = "One or more fields is not set or is blank.";
            $errorCode = 1;
            Utilities::log("ReturnMessage: " . $transMsg . " ErrorCode: " . $errorCode);
            $this->_sendResponse(200, CJSON::encode(CommonController::retMsgRegisterMemberBT($module,'','', $errorCode, $transMsg)));
            $logMessage = 'One or more fields is not set or is blank.';
            $logger->log($logger->logdate, " [REGISTERMEMBERBT ERROR] ", $logMessage);
            $apiDetails = 'REGISTERMEMBERBT-Failed: Invalid input parameters.';
            $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, '', $apiDetails, '', 2);
            if($isInserted == 0) {
                $logMessage = "Failed to insert to APILogs.";
                $logger->log($logger->logdate, " [REGISTERMEMBERBT ERROR] ", $logMessage);
            }
            exit;
        }
    }
    
    
    
    //    //@date 07-04-2014
//    //@purpose generates a new session for authentication purposes
//    public function actionAuthenticateSession($username, $password) {
//        $request = $this->_readJsonRequest();
//        
//        $transMsg = '';
//        $errorCode = '';
//        $module = 'AuthenticateSession';
//        
//        //check if username & password is inputted
//        if(isset($request['Username']) && isset($request['Password'])) {
//            if (($request['Username'] == '') || ($request['Password'] == '')) {
//                $transMsg = "One or more fields is not set or is blank.";
//                $errorCode = 1;
//                Utilities::log("ReturnMessage: " . $transMsg . " ErrorCode: " . $errorCode);
//                $this->_sendResponse(200, CJSON::encode(CommonController::retMsg($module, $transMsg, $errorCode)));
//            }
//            else {
//                if (Utilities::validateEmail($request['Username']) && ctype_alnum($request['Password'])) {
//                    
//                    $username = trim($request['Username']);
//                    $password = trim($request['Password']);
//                    
//                    //start of declaration of models to be used
//                    //$membersModel = new MembersModel();
//                    $memberSessionsModel = new MemberSessionsModel();
//                    //$memberCardsModel = new MemberCardsModel();
//                    //$cardsModel = new CardsModel();
//                    //$auditTrailModel = new AuditTrailModel();
//                    
//                    $members = $this->_authenticate($username, $password);
//                    //$credentials = $membersModel->getLoginInfo($username, $password);
//                    
//                    $MID = $members['MID'];
//                    //$mpSessionID = $credentials['SessionID'];
////                    $cardTypeID = $members['CardTypeID'];
//                    //$isVip = $members['IsVip'];
////                    $status = $members['Status'];
//                    
//                    if($members) {
//                        $activeSession = $memberSessionsModel->checkSession($MID);
//                        $remoteIP = $_SERVER['REMOTE_ADDR'];
//                        $mpSessionID = session_id();
//                        
//                        if($activeSession > 0) {
//                            $result = $memberSessionsModel->updateSession($mpSessionID, $MID, $remoteIP);
//                        }
//                        else {
//                            $result = $memberSessionsModel->insertMemberSession($MID, $mpSessionID, $remoteIP);
//                        }
//                        
//                        
//                        if($result > 0) {                           
//                           
////                            $memberSessions = $memberSessionsModel->getMemberSessions($MID);
////                            $mpSessionid = $memberSessions['SessionID'];
////                            $endDate = $memberSessions['DateEnded'];
////                            
////                            $memberCards = $memberCardsModel->getActiveMemberCardInfo($MID);
////                            
////                            $cardNumber = $memberCards['CardNumber'];
////                            
////                            $cards = $cardsModel->getCardInfo($cardNumber);
////                            
////                            $cardTypeID = $cards['CardTypeID'];
////                            
////                            $session['SessionID'] = $mpSessionid;
////                            $session['MID'] = $MID;
////                            $session['UserName'] = $username;
////                            $session['CardTypeID'] = $cardTypeID;
////                            $session['DateEnded'] = $endDate;
////                            $session['Password'] = $password;
////                            
////                            $auditTrailModel->logEvent(AuditTrailModel::LOGIN, $username, array('MID' => $MID, 'SessionID' => $mpSessionid));
//                            
//                            $isUpdated = $memberSessionsModel->updateTransactionDate($MID, $mpSessionID);
//                            if($isUpdated > 0) {
//                                $transMsg = 'No Error, Transaction successful.';
//                                $errorCode = 0;
//                                $this->_sendResponse(200, CJSON::encode(CommonController::retMsg($module, $transMsg, $errorCode, $mpSessionID)));
//                            }
//                            else {
//                                $transMsg = 'Transaction failed.';
//                                $errorCode = 4;
//                                Utilities::log("ReturnMessage: " . $transMsg . " ErrorCode: " . $errorCode);
//                                $this->_sendResponse(200, CJSON::encode(CommonController::retMsg($module, $transMsg, $errorCode)));
//                            }  
//                        }
//                        else {
//                            $transMsg = 'Transaction failed.';
//                            $errorCode = 4;
//                            Utilities::log("ReturnMessage: " . $transMsg . " ErrorCode: " . $errorCode);
//                            $this->_sendResponse(200, CJSON::encode(CommonController::retMsg($module, $transMsg, $errorCode)));
//                        }
//                    }
//                    else {
//                        $transMsg = 'Member not found';
//                        $errorCode = 3;
//                        Utilities::log("ReturnMessage: " . $transMsg . " ErrorCode: " . $errorCode);
//                        $this->_sendResponse(200, CJSON::encode(CommonController::retMsg($module, $transMsg, $errorCode)));
//                    }
//                }
//                else {
//                        $transMsg = 'Invalid input.';
//                        $errorCode = 2;
//                        Utilities::log("ReturnMessage: " . $transMsg . " ErrorCode: " . $errorCode);
//                        $this->_sendResponse(200, CJSON::encode(CommonController::retMsg($module, $transMsg, $errorCode)));
//                }
//            }
//        }
//        else {
//            $transMsg = "One or more fields is not set or is blank.";
//            $errorCode = 1;
//            Utilities::log("ReturnMessage: " . $transMsg . " ErrorCode: " . $errorCode);
//            $this->_sendResponse(200, CJSON::encode(CommonController::retMsg($module, $transMsg, $errorCode)));
//        }
//    }
//    
//    //@purpose gets the currently active session of the logged in member
//    public function actionGetActiveSession() {
//        $request = $this->_readJsonRequest();
//        
//        $transMsg = '';
//        $errorCode = '';
//        $module = 'GetActiveSession';
//        
//        //check if MP session id is inputted
//        if(isset($request['MPSessionID'])) {
//            if (($request['MPsessionID'] == '')) {
//                $transMsg = "One or more fields is not set or is blank.";
//                $errorCode = 1;
//                Utilities::log("ReturnMessage: " . $transMsg . " ErrorCode: " . $errorCode);
//                $this->_sendResponse(200, CJSON::encode(CommonController::retMsg($module, $transMsg, $errorCode)));
//            }
//            else {
//                if(ctype_alnum($request['Password'])) {
//                    $mpSessionID = trim($request['MPSessionID']);
//                    
//                    $memberSessionsModel = new MemberSessionsModel();
//                    
//                    if(isset($session['MID'])) {
//                        $MID = $session['MID'];
//                    }
//                    else {
//                        $MID = 0;
//                    }
//                    
//                    $isExist = $memberSessionsModel->checkIfSessionExist($MID, $mpSessionID);
//                    if(count($isExist) > 0) {
//                        $activeSession = $memberSessionsModel->getMemberSessions($MID);
//                        $dateStarted = $activeSession['DateStarted'];
//                        $transactionDate = $activeSession['TransactionDate'];
//                        //$activeSessionID = $activeSession['SessionID'];
//
//                        if($activeSession > 0) {
//                            //$isValid = $memberSessionsModel->checkIfValidSession($MID, $mpSessionID);
//                            //get the difference in minutes of date started and transaction date
//                            $diffDate = (int)strtotime($transactionDate) - (int)strtotime($dateStarted);
//                            $years = floor($diffDate / (365*60*60*24));
//                            $months = floor(($diffDate - $years * 365*60*60*24) / (30*60*60*24));
//                            $days = floor(($diffDate - $years * 365*60*60*24 - $months*30*60*60*24)/ (60*60*24)); //actual day difference
//                            $noofmins = round(abs($diffDate)/60,2); //actual minute difference
//                            
//                            if($diffDate > 30) {
//                                $transMsg = 'Session is expired.';
//                                $errorCode = 41;
//                                Utilities::log("ReturnMessage: " . $transMsg . " ErrorCode: " . $errorCode);
//                                $this->_sendResponse(200, CJSON::encode(CommonController::retMsg($module, $transMsg, $errorCode)));
//                            }
//                            else {
//                                $transMsg = 'Session is valid.';
//                                $errorCode = 42;
//                                $this->_sendResponse(200, CJSON::encode(CommonController::retMsg($module, $transMsg, $errorCode)));
//                            }
//                            
//                        }
//                    }   
//                    else {
//                        session_destroy();
//                        $transMsg = "Not connected.";
//                        $errorCode = 13;
//                        Utilities::log("ReturnMessage: " . $transMsg . " ErrorCode: " . $errorCode);
//                        $this->_sendResponse(200, CJSON::encode(CommonController::retMsg($module, $transMsg, $errorCode)));
//                    }
//                    
//                }
//                else {
//                    $transMsg = 'Invalid input.';
//                    $errorCode = 2;
//                    Utilities::log("ReturnMessage: " . $transMsg . " ErrorCode: " . $errorCode);
//                    $this->_sendResponse(200, CJSON::encode(CommonController::retMsg($module, $transMsg, $errorCode)));
//                }
//            } 
//        }
//        else {
//            $transMsg = "One or more fields is not set or is blank.";
//            $errorCode = 1;
//            Utilities::log("ReturnMessage: " . $transMsg . " ErrorCode: " . $errorCode);
//            $this->_sendResponse(200, CJSON::encode(CommonController::retMsg($module, $transMsg, $errorCode)));
//        }       
//        
////        if(isset($session['UserName'])) {
////            $username = $session['UserName'];
////        }
////        else {
////            $username = '';
////        }
////        
////        if(isset($session['Password'])) {
////            $password = $session['Password'];
////        }
////        else {
////            $password = '';
////        }
//               
//    }
}

?>
