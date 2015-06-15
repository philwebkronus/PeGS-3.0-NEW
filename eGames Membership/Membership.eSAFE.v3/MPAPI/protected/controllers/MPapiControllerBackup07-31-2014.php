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
        }
        

        $retVal = '';
        $strPass = md5($password);

        if(is_array($result) && count($result) > 0) {
            $mid = $result['MID'];
            

            switch($result['Status']) {
                case 1:
                    if($result['Password'] != $strPass) {
                        //$transMsg = "Incorrect Password.";
                        $logMessage = 'Password inputted is incorrect.';
                        //$errorCode = 32;
                        //Utilities::log("ReturnMessage: " . $transMsg . " ErrorCode: " . $errorCode);
                        //$this->_sendResponse(200, CJSON::encode(CommonController::retMsg($module, $transMsg, $errorCode)));
                        $logger->log($logger->logdate, " [LOGIN ERROR] ", $logMessage);
                        $apiDetails = 'LOGIN-Authenticate-Failed: Incorrect password.';
                        $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, '', $apiDetails, '', 2);
                        if($isInserted == 0) {
                            $logMessage = "Failed to insert to APILogs.";
                            $logger->log($logger->logdate, " [LOGIN ERROR] ", $logMessage);
                        }
                        //$retVal = "ReturnMessage: " . $transMsg . " ErrorCode: " . $errorCode;
                    }
                    else
                        $retVal = $result;
                    break;
                case 0:
                    $logMessage = 'Account is Inactive';
//                    $transMsg = "Account is Inactive.";
//                    $errorCode = 33;
//                    Utilities::log("ReturnMessage: " . $transMsg . " ErrorCode: " . $errorCode);
//                    $this->_sendResponse(200, CJSON::encode(CommonController::retMsg($module, $errorCode, $transMsg)));
                    $logger->log($logger->logdate, " [LOGIN ERROR] ", $logMessage);
                    $apiDetails = 'LOGIN-Authenticate-Failed: Member account is inactive.';
                    $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, '', $apiDetails, '', 2);
                    if($isInserted == 0) {
                        $logMessage = "Failed to insert to APILogs.";
                        $logger->log($logger->logdate, " [LOGIN ERROR] ", $logMessage);
                    }
                    break;
                case 2:
                    $logMessage = 'Account is Suspended.';
//                    $transMsg = "Account is Suspended.";
//                    $errorCode = 34;
//                    Utilities::log("ReturnMessage: " . $transMsg . " ErrorCode: " . $errorCode);
//                    $this->_sendResponse(200, CJSON::encode(CommonController::retMsg($module,  $errorCode, $transMsg)));
                    $logger->log($logger->logdate, " [LOGIN ERROR] ", $logMessage);
                    $apiDetails = 'LOGIN-Authenticate-Failed: Member account is suspended.';
                    $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, '', $apiDetails, '', 2);
                    if($isInserted == 0) {
                        $logMessage = "Failed to insert to APILogs.";
                        $logger->log($logger->logdate, " [LOGIN ERROR] ", $logMessage);
                    }
                    break;
                case 3:
                    $logMessage = 'Account is Locked (Login Attempts).';
//                    $transMsg = "Account is Locked (Login Attempts).";
//                    $errorCode = 35;
//                    Utilities::log("ReturnMessage: " . $transMsg . " ErrorCode: " . $errorCode);
                    //$this->_sendResponse(200, CJSON::encode(CommonController::retMsg($module,$errorCode,$transMsg)));
                    $logger->log($logger->logdate, " [LOGIN ERROR] ", $logMessage);
                    $apiDetails = 'LOGIN-Authenticate-Failed: Member account is locked (login attempts).';
                    $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, '', $apiDetails, '', 2);
                    if($isInserted == 0) {
                        $logMessage = "Failed to insert to APILogs.";
                        $logger->log($logger->logdate, " [LOGIN ERROR] ", $logMessage);
                    }
                    break;
                case 4:
                    $logMessage = 'Account is Locked (By Admin).';
//                    $transMsg = "Account is Locked (By Admin).";
//                    $errorCode = 36;
//                    Utilities::log("ReturnMessage: " . $transMsg . " ErrorCode: " . $errorCode);
//                    $this->_sendResponse(200, CJSON::encode(CommonController::retMsg($module, $errorCode, $transMsg)));
                    $logger->log($logger->logdate, " [LOGIN ERROR] ", $logMessage);
                    $apiDetails = 'LOGIN-Authenticate-Failed: Member account is locked (admin).';
                    $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, '', $apiDetails, '', 2);
                    if($isInserted == 0) {
                        $logMessage = "Failed to insert to APILogs.";
                        $logger->log($logger->logdate, " [LOGIN ERROR] ", $logMessage);
                    }
                    break;
                case 5:
                    $logMessage = 'Account is Locked (By Admin).';
//                    $transMsg = "Account is Locked (By Admin).";
//                    $errorCode = 36;
//                    Utilities::log("ReturnMessage: " . $transMsg . " ErrorCode: " . $errorCode);
//                    $this->_sendResponse(200, CJSON::encode(CommonController::retMsg($module, $errorCode, $transMsg)));
                    $logger->log($logger->logdate, " [LOGIN ERROR] ", $logMessage);
                    $apiDetails = 'LOGIN-Authenticate-Failed: Member account is locked (admin).';
                    $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, '', $apiDetails, '', 2);
                    if($isInserted == 0) {
                        $logMessage = "Failed to insert to APILogs.";
                        $logger->log($logger->logdate, " [LOGIN ERROR] ", $logMessage);
                    }
                    break;
                case 6:
                    $logMessage = 'Account is Terminated.';
//                    $transMsg = "Account is Terminated.";
//                    $errorCode = 37;
//                    Utilities::log("ReturnMessage: " . $transMsg . " ErrorCode: " . $errorCode);
//                    $this->_sendResponse(200, CJSON::encode(CommonController::retMsg($module, $errorCode, $transMsg)));
                    $logger->log($logger->logdate, " [LOGIN ERROR] ", $logMessage);
                    $apiDetails = 'LOGIN-Authenticate-Failed: Member account is terminated.';
                    $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, '', $apiDetails, '', 2);
                    if($isInserted == 0) {
                        $logMessage = "Failed to insert to APILogs.";
                        $logger->log($logger->logdate, " [LOGIN ERROR] ", $logMessage);
                    }
                    break;
                default:
                    $logMessage = 'Account is Invalid.';
//                    $transMsg = "Account is Invalid.";
//                    $errorCode = 38;
//                    Utilities::log("ReturnMessage: " . $transMsg . " ErrorCode: " . $errorCode);
//                    $this->_sendResponse(200, CJSON::encode(CommonController::retMsg($module, $errorCode, $transMsg)));
                    $logger->log($logger->logdate, " [LOGIN ERROR] ", $logMessage);
                    $apiDetails = 'LOGIN-Authenticate-Failed: Member account is invalid.';
                    $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, '', $apiDetails, '', 2);
                    if($isInserted == 0) {
                        $logMessage = "Failed to insert to APILogs.";
                        $logger->log($logger->logdate, " [LOGIN ERROR] ", $logMessage);
                    }
                    break;
            }

        }
        else if(is_string($result)) {
            $transMsg = "Card is Banned.";
            $errorCode = 11;
            Utilities::log("ReturnMessage: " . $transMsg . " ErrorCode: " . $errorCode);
            $this->_sendResponse(200, CJSON::encode(CommonController::retMsgLogin($module, '', '', '', $errorCode, $transMsg)));
            $logMessage = 'Card is Banned.';
            $logger->log($logger->logdate, " [LOGIN ERROR] ", $logMessage);
            $apiDetails = 'LOGIN-Authenticate-Failed: Member card is banned.';
            $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, '', $apiDetails, '', 2);
            if($isInserted == 0) {
                $logMessage = "Failed to insert to APILogs.";
                $logger->log($logger->logdate, " [LOGIN ERROR] ", $logMessage);
            }
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
            $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, '', $apiDetails, '', 2);
            if($isInserted == 0) {
                $logMessage = "Failed to insert to APILogs.";
                $logger->log($logger->logdate, " [LOGIN ERROR] ", $logMessage);
            }
            exit;
        }
        else {
//            $transMsg = "Account is Invalid.";
//            $errorCode = 38;
//            Utilities::log("ReturnMessage: " . $transMsg . " ErrorCode: " . $errorCode);
//            $this->_sendResponse(200, CJSON::encode(CommonController::retMsg($module, $transMsg, $errorCode)));
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
                $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, '', $apiDetails, '', 2);
                if($isInserted == 0) {
                    $logMessage = "Failed to insert to APILogs.";
                    $logger->log($logger->logdate, " [LOGIN ERROR] ", $logMessage);
                }
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
                $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, '', $apiDetails, '', 2);
                if($isInserted == 0) {
                    $logMessage = "Failed to insert to APILogs.";
                    $logger->log($logger->logdate, " [LOGIN ERROR] ", $logMessage);
                }
                exit;
            }
        }
        

//        var_dump($retVal);
//        exit;
        return $retVal;
    }
    
    //@purpose serves as input for the username & password of the member to access the portal
    public function actionLogin() {
        $request = $this->_readJsonRequest();
        
        $transMsg = '';
        $errorCode = '';
        $module = 'Login';
        $apiMethod = 1;
        
        $logger = new ErrorLogger();
        $apiLogsModel = new APILogsModel();
        
        //DelExpiredSession::deleteExpiredSession();
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
                if (Utilities::validateEmail($request['Username']) && ctype_alnum($request['Password'])) {
                    
                    $username = trim($request['Username']);
                    $password = trim($request['Password']);
                    
                    //start of declaration of models to be used
                    
                    $memberSessionsModel = new MemberSessionsModel();
                    $memberCardsModel = new MemberCardsModel();
                    $cardsModel = new CardsModel();
                    $auditTrailModel = new AuditTrailModel();
                    
                    
                    $members = $this->_authenticate($username, $password);
                    
                    //$credentials = $membersModel->getLoginInfo($username, $password);
                    
                    $MID = $members['MID'];
                    //$mpSessionID = $credentials['SessionID'];
//                    $cardTypeID = $members['CardTypeID'];
                    $isVIP = $members['IsVIP'];
//                    $status = $members['Status'];
                    
                    if($members) {
                        $activeSession = $memberSessionsModel->checkSession($MID);
                        
                        $remoteIP = $_SERVER['REMOTE_ADDR'];
                        session_start();
                        $mpSessionID = session_id();
                        
                        if($activeSession['COUNT(MemberSessionID)'] > 0) {
                            $result = $memberSessionsModel->updateSession($mpSessionID, $MID, $remoteIP);
                        }
                        else {
                            
                            $result = $memberSessionsModel->insertMemberSession($MID, $mpSessionID, $remoteIP);
                        }
                        
                        if($result > 0) {                           
                           
                            $memberSessions = $memberSessionsModel->getMemberSessions($MID);
                            
                            
                            $mpSessionid = $memberSessions['SessionID'];
                            $endDate = $memberSessions['DateEnded'];
                            
                            $memberCards = $memberCardsModel->getActiveMemberCardInfo($MID);
                            
                            
                            
                            $cardNumber = $memberCards['CardNumber'];
                            
                            $cards = $cardsModel->getCardInfo($cardNumber);
                            
                            $cardTypeID = $cards['CardTypeID'];
                            
//                            Yii::app()->session['SessionID'] = $mpSessionid;
//                            Yii::app()->session['MID'] = $MID;
//                            Yii::app()->session['UserName'] = $username;
//                            Yii::app()->session['CardTypeID'] = $cardTypeID;
//                            Yii::app()->session['DateEnded'] = $endDate;
//                            Yii::app()->session['Password'] = $password;
                            
                            
                            
                            $isSuccessful = $auditTrailModel->logEvent(AuditTrailModel::LOGIN, $username, array('MID' => $MID, 'SessionID' => $mpSessionID, 'AID' => $MID));
                            if($isSuccessful == 0) {
                                $logMessage = 'Failed to log event on Audittrail.';
                                $logger->log($logger->logdate, " [LOGIN FAILED] ", $logMessage);
//                                $transMsg = "Failed to log event on Audit Trail.";
//                                $errorCode = 25;
//                                Utilities::log("ReturnMessage: " . $transMsg . " ErrorCode: " . $errorCode);
//                                $this->_sendResponse(200, CJSON::encode(CommonController::retMsg($module, $transMsg, $errorCode)));
                            }
                            $isUpdated = $memberSessionsModel->updateTransactionDate($MID, $mpSessionID);
                            if($isUpdated > 0) {
                                $transMsg = 'No Error, Transaction successful.';
                                $logMessage = 'Login successful.';
                                $errorCode = 0;
                                $this->_sendResponse(200, CJSON::encode(CommonController::retMsgLogin($module, $mpSessionID, $cardTypeID, $isVIP, $errorCode, $transMsg)));
                                $logger->log($logger->logdate, " [LOGIN SUCCESS] ", $logMessage);
                                $apiDetails = 'LOGIN-UpdateTransDate-Success: '.'Username: '.$username.' MID = '.$MID.' SessionID = '.$mpSessionID;
                                $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, $apiMethod.'-'.$cardNumber.'-'.$logger->logdate, $apiDetails, '', 1);
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
                                $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, $apiMethod.'-'.$cardNumber.'-'.$logger->logdate, $apiDetails, '', 2);
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
                            $apiDetails = 'LOGIN-Insert/UpdateMemberSession-Failed: '.'Username: '.$username.' MID = '.$MID.' SessionID = '.$mpSessionID;
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
                //$logger = new ErrorLogger();
                
//                $activeSession = $this->_getActiveSession();
//                if($activeSession) {
//                    $mid = $activeSession['MID'];
//                    $sessionID = $activeSession['SessionID'];
                    
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
                                $isSuccessful = $auditTrailModel->logEvent(AuditTrailModel::FORGOT_PASSWORD, $emailCardNumber, array('MID' => $MID,'SessionID' => ''));
                                if($isSuccessful == 0) {
                                    $logMessage = "Failed to insert to Audittrail.";
                                    $logger->log($logger->logdate, " [FORGOTPASSWORD ERROR] ", $logMessage);
                                }
                                $apiDetails = 'FORGOTPASSWORD-UpdateChangePass-Success: '.'Username: '.$emailCardNumber.' MID = '.$MID;
                                $apiLogsModel->insertAPIlogs($apiMethod, $apiMethod.'-'.$ubCard.'-'.$logger->logdate, $apiDetails, '', 1);
                                if($isInserted == 0) {
                                    $logMessage = "Failed to insert to APILogs.";
                                    $logger->log($logger->logdate, " [FORGOTPASSWORD ERROR] ", $logMessage);
                                }
                                $isUpdated = $memberSessionsModel->updateTransactionDate($MID);
                                if($isUpdated > 0) {
                                    $transMsg = 'No Error, Transaction successful.';
                                    $errorCode = 0;
                                    $this->_sendResponse(200, CJSON::encode(CommonController::retMsg($module, $errorCode, $transMsg)));
                                    //$logger->log($logger->logdate, " [FORGOTPASSWORD SUCCESS] ", $logMessage);
                                    $apiDetails = 'FORGOTPASSWORD-UpdateTransDate-Success: '.' MID = '.$MID;
                                    $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, $apiMethod.'-'.$ubCard.'-'.$logger->logdate, $apiDetails, '', 1);
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
                                    $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, $apiMethod.'-'.$ubCard.'-'.$logger->logdate, $apiDetails, '', 2);
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
                                $apiDetails = 'FORGOTPASSWORD-UpdateChangePass-Failed: '.'Username: '.$emailCardNumber.' MID = '.$MID;
                                $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, $apiMethod.'-'.$ubCard.'-'.$logger->logdate, $apiDetails, '', 2);
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
                            $apiDetails = 'FORGOTPASSWORD-Failed: Email is not found in db '.'Username: '.$emailCardNumber.' MID = '.$MID;
                            $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, $apiMethod.'-'.$ubCard.'-'.$logger->logdate, $apiDetails, '', 2);
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
                                        $isSuccessful = $auditTrailModel->logEvent(AuditTrailModel::FORGOT_PASSWORD, $emailCardNumber, array('MID' => $MID,'SessionID' => ''));
                                        if($isSuccessful == 0) {
                                            $logMessage = "Failed to insert to Audittrail.";
                                            $logger->log($logger->logdate, " [FORGOTPASSWORD ERROR] ", $logMessage);
                                        }
                                        $apiDetails = 'FORGOTPASSWORD-UpdateChangePass-Success: '.'Username: '.$emailCardNumber.' MID = '.$MID;
                                        $apiLogsModel->insertAPIlogs($apiMethod, $apiMethod.'-'.$ubCard.'-'.$logger->logdate, $apiDetails, '', 1);
                                        if($isInserted == 0) {
                                            $logMessage = "Failed to insert to APILogs.";
                                            $logger->log($logger->logdate, " [FORGOTPASSWORD ERROR] ", $logMessage);
                                        }
                                        $isUpdated = $memberSessionsModel->updateTransactionDate($MID);
                                        if($isUpdated > 0) {
                                            $transMsg = 'No Error, Transaction successful.';
                                            $errorCode = 0;
                                            $this->_sendResponse(200, CJSON::encode(CommonController::retMsg($module, $errorCode, $transMsg)));
                                            //$logger->log($logger->logdate, " [FORGOTPASSWORD SUCCESS] ", $logMessage);
                                            $apiDetails = 'FORGOTPASSWORD-UpdateTransDate-Success: '.' MID = '.$MID;
                                            $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, $apiMethod.'-'.$ubCard.'-'.$logger->logdate, $apiDetails, '', 1);
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
                                            $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, $apiMethod.'-'.$ubCard.'-'.$logger->logdate, $apiDetails, '', 2);
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
                                        $apiDetails = 'FORGOTPASSWORD-UpdateChangePass-Failed: '.'Username: '.$emailCardNumber.' MID = '.$MID;
                                        $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, $apiMethod.'-'.$ubCard.'-'.$logger->logdate, $apiDetails, '', 2);
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
                                    $apiDetails = 'FORGOTPASSWORD-Failed: Email not found in db '.'Username: '.$emailCardNumber.' MID = '.$MID;
                                    $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, $apiMethod.'-'.$ubCard.'-'.$logger->logdate, $apiDetails, '', 2);
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
                                    $errorCode = 7;
                                }
                                else if($transMsg == 'Membership Card is Deactivated') {
                                    $errorCode = 8;
                                }
                                else if($transMsg == 'Membership Card is Newly Migrated') {
                                    $errorCode = 9;
                                }
                                else if($transMsg == 'Membership Card is Temporarily Migrated') {
                                    $errorCode = 10;
                                }
                                else if($transMsg == 'Membership Card is Banned') {
                                    $errorCode = 11;
                                }
                                else {
                                    $errorCode = 6;
                                }
                                
                                $logMessage = $transMsg;
                                Utilities::log("ReturnMessage: " . $transMsg . " ErrorCode: " . $errorCode);
                                $this->_sendResponse(200, CJSON::encode(CommonController::retMsg($module,  $errorCode, $transMsg)));
                                $logger->log($logger->logdate, " [FORGOTPASSWORD ERROR] ", $logMessage);
                                $apiDetails = 'FORGOTPASSWORD-Failed: '.$transMsg.'.'.'Status = '.$data['Status'].' MID = '.$MID;
                                $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, $apiMethod.'-'.$ubCard.'-'.$logger->logdate, $apiDetails, '', 2);
                                if($isInserted == 0) {
                                    $logMessage = "Failed to insert to APILogs.";
                                    $logger->log($logger->logdate, " [FORGOTPASSWORD ERROR] ", $logMessage);
                                }
                                exit;
                            }
                        }
                        else {
                            if($isCardExist == 0) {
                                $transMsg = "Invalid Card Number.";
                                $logMessage = 'Invalid Card Number.';
                                $errorCode = 6;
                                Utilities::log("ReturnMessage: " . $transMsg . " ErrorCode: " . $errorCode);
                                $this->_sendResponse(200, CJSON::encode(CommonController::retMsg($module, $errorCode,$transMsg)));
                                $logger->log($logger->logdate, " [FORGOTPASSWORD ERROR] ", $logMessage);
                                $apiDetails = 'FORGOTPASSWORD-Failed: Membership card is invalid. '.'Username: '.$emailCardNumber.'MID = '.$MID;
                                $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, $apiMethod.'-'.$ubCard.'-'.$logger->logdate, $apiDetails, '', 2);
                                if($isInserted == 0) {
                                    $logMessage = "Failed to insert to APILogs.";
                                    $logger->log($logger->logdate, " [FORGOTPASSWORD ERROR] ", $logMessage);
                                }
                                exit;
                            }
                            else {
                                $transMsg = "Card is Inactive.";
                                $logMessage = 'Card is Inactive.';
                                $errorCode = 7;
                                Utilities::log("ReturnMessage: " . $transMsg . " ErrorCode: " . $errorCode);
                                $this->_sendResponse(200, CJSON::encode(CommonController::retMsg($module, $errorCode, $transMsg)));
                                $logger->log($logger->logdate, " [FORGOTPASSWORD ERROR] ", $logMessage);
                                $apiDetails = 'FORGOTPASSWORD-Failed: Membership card is inactive. '.'Username: '.$emailCardNumber.'MID = '.$MID;
                                $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, $apiMethod.'-'.$ubCard.'-'.$logger->logdate, $apiDetails, '', 2);
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
                        $apiDetails = 'FORGOTPASSWORD-Failed: Invalid card number. '.'Username: '.$emailCardNumber.'MID = '.$MID;
                        $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, $apiMethod.'-'.$ubCard.'-'.$logger->logdate, $apiDetails, '', 2);
                        if($isInserted == 0) {
                            $logMessage = "Failed to insert to APILogs.";
                            $logger->log($logger->logdate, " [FORGOTPASSWORD ERROR] ", $logMessage);
                        }
                        exit;
                    }
//                else {
//                    session_destroy();
//                    $transMsg = "Not connected.";
//                    $errorCode = 13;
//                    Utilities::log("ReturnMessage: " . $transMsg . " ErrorCode: " . $errorCode);
//                    $this->_sendResponse(200, CJSON::encode(CommonController::retMsg($module, $transMsg, $errorCode)));
//                    $logMessage = 'Not connected.';
//                    $logger->log($logger->logdate, " [FORGOTPASSWORD ERROR] ", $logMessage);
//                    $apiDetails = 'FORGOTPASSWORD-GetActiveSession-Failed: There is no active session. '.'Username: '.$emailCardNumber;
//                    $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, $apiMethod.'-'.$ubCard.'-'.$logger->logdate, $apiDetails, '', 2);
//                    if($isInserted == 0) {
//                        $logMessage = "Failed to insert to APILogs.";
//                        $logger->log($logger->logdate, " [FORGOTPASSWORD ERROR] ", $logMessage);
//                    }
//                    exit;
//                }   
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
            $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, $apiMethod.'-'.$ubCard.'-'.$logger->logdate, $apiDetails, '', 2);
            if($isInserted == 0) {
                $logMessage = "Failed to insert to APILogs.";
                $logger->log($logger->logdate, " [FORGOTPASSWORD ERROR] ", $logMessage);
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
            else if(is_numeric($request['MobileNo'] == FALSE) || is_numeric($request['AlternateMobileNo'] == FALSE)) {
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
            else if(ctype_alpha($request['FirstName']) == FALSE || ctype_alpha($request['MiddleName']) == FALSE ||
                    ctype_alpha($request['LastName']) == FALSE || ctype_alpha($request['NickName']) == FALSE) {
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
                             
//                $isExist = $memberSessionsModel->checkIfSessionExist($MID, $sessionID);
//                if(count($isExist) > 0) {
//                    //check if from old to newly migrated card
//                    if(!is_null($email)) {
//                        $tempMID = $membershipTempModel->getMID($email);
//                        
//                        if(empty($tempMID))
//                            $tempMID = 0;
//                        else
//                            $mid = $tempMID;
//                    }
//                    else
//                        $mid = $MID;
                    
                    $emailAddress = trim($request['EmailAddress']);
                    $firstname = trim($request['FirstName']);
                    $middlename = trim($request['MiddleName']);
                    if($middlename == '')
                        $middlename = '';
                    $lastname = trim($request['LastName']);
                    $nickname = trim($request['NickName']);
                    if($nickname == '')
                        $nickname = '';
                    $password = md5(trim($request['Password']));
//                    $confirmPassword = md5(trim($request['ConfirmPassword']));
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
                    $nationalityID = trim($request['Nationality']);
                    if($nationalityID == '')
                        $nationalityID = '';
                    $occupationID = trim($request['Occupation']);
                    if($occupationID == '')
                        $occupationID = '';
                    $isSmoker = trim($request['IsSmoker']);
                    if($isSmoker == '')
                        $isSmoker = '';
                    $referralCode = trim($request['ReferralCode']);
                    if($referralCode == '')
                        $referralCode = '';
                   
                    //$mpSessionID = trim($request['MPSessionID']);
                    
//                    $membersArray = array('UserName' => $emailAddress, 'Password' => $password);
//                    $memberInfoArray = array('FirstName' => $firstname, 'MiddleName' => $middlename,
//                                             'LastName' => $lastname, 'Address1' => $permanentAddress, 
//                                             'IdentificationNumber' => $idNumber, 'IdentificationID' => $idPresented,
//                                             'NickName' => $nickname, 'MobileNo' => $mobileNumber,
//                                             'AlternateMobileNo' => $alternateMobileNumber,
//                                             'Email' => $emailAddress, 'AlternateEmail' => $alternateEmail,
//                                             'Birthdate' => $birthdate, 'NationalityID' => $nationalityID,
//                                             'OccupationID' => $occupationID, 'ReferrerCode' => $referralCode,
//                                             'Gender' => $gender, 'IsSmoker' => $isSmoker);
                    
//                    $tempHasEmailCount = $membershipTempModel->checkIfEmailExistsWithMID($mid, $emailAddress);
//                    if(is_null($tempHasEmailCount))
//                        $tempHasEmailCount = 0;      
//                    else
//                        $tempHasEmailCount = $tempHasEmailCount['COUNT'];
                    
                    //check if member is blacklisted
                    $isBlackListed = $blackListsModel->checkIfBlackListed($firstname, $lastname, $birthdate, 3);
                    //check if email is active and existing in live membership db
                    $activeEmail = $membershipTempModel->checkIfActiveVerifiedEmail($emailAddress);
                    if($activeEmail['COUNT(MID)'] > 0) {
                        $transMsg = "Sorry, " . $emailAddress . " already belongs to an existing account. Please enter another email address.";
                        $errorCode = 21;
                        Utilities::log("ReturnMessage: " . $transMsg . " ErrorCode: " . $errorCode);
                        $this->_sendResponse(200, CJSON::encode(CommonController::retMsgRegisterMember($module, $errorCode, $transMsg)));
                        $logMessage = 'Sorry, ' . $emailAddress . 'already belongs to an existing account. Please enter another email address.';
                        $logger->log($logger->logdate, " [REGISTERMEMBER ERROR] ", $logMessage);
                        $apiDetails = 'REGISTERMEMBER-Failed: Email is already used. EmailAddress = '.$emailAddress;
                        $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, '', $apiDetails, '', 2);
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
                        $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, '', $apiDetails, '', 2);
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
                            $errorCode = 22;
                            Utilities::log("ReturnMessage: " . $transMsg . " ErrorCode: " . $errorCode);
                            $this->_sendResponse(200, CJSON::encode(CommonController::retMsgRegisterMember($module, $errorCode, $transMsg)));
                            $logMessage = 'Email is already verified. Please choose a different email address.';
                            $logger->log($logger->logdate, " [REGISTERMEMBER ERROR] ", $logMessage);
                            $apiDetails = 'REGISTERMEMBER-Failed: Email is already verified. Email = '.$emailAddress;
                            $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, '', $apiDetails, '', 2);
                            if($isInserted == 0) {
                                $logMessage = "Failed to insert to APILogs.";
                                $logger->log($logger->logdate, " [REGISTERMEMBER ERROR] ", $logMessage);
                            }
                            exit;
                        }
                        else {
                            $lastInsertedMID = $membershipTempModel->register($emailAddress, $firstname, $middlename, $lastname, $nickname, $password, $permanentAddress, $mobileNumber, $alternateMobileNumber, $alternateEmail, $idNumber, $idPresented, $gender, $referralCode, $birthdate, $occupationID, $nationalityID, $isSmoker);
                            if($lastInsertedMID > 0) {
//                                if(isset(Yii::app()->session['MID'])) {
//                                    $ID = Yii::app()->session['MID'];
//                                    $sessionID = Yii::app()->session['SessionID'];
//                                }
                          //      else {
                                    $ID = 0;
                                    $sessionID = '';
                                   // $emailAddress = 'guest';
                               // }
                                
                                $memberInfos = $membershipTempModel->getTempMemberInfoForSMS($lastInsertedMID);
                                
                                //match to 09 or 639 in mobile number
                                $match = substr($memberInfos['MobileNo'], 0, 3);
                                if($match == "639"){
                                    $mncount = count($memberInfos["MobileNo"]);
                                    if(!$mncount == 12){
                                        $message = "Failed to send SMS. Invalid Mobile Number.";
                                        $logger->log($logger->logdate,"[REGISTERMEMBER ERROR] ", $message);
                                        $apiDetails = "REGISTERMEMBER-Failed: Failed to send SMS. Invalid Mobile Number [MID = $lastInsertedMID].";
                                        $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, '', $apiDetails, '', 2);
                                        if($isInserted == 0) {
                                            $logMessage = "Failed to insert to APILogs.";
                                            $logger->log($logger->logdate, " [REGISTERMEMBER ERROR] ", $logMessage);
                                        }
                                        exit;
                                    } else {
                                        $templateid = $ref_SMSApiMethodsModel->getSMSMethodTemplateID(Ref_SMSApiMethodsModel::PLAYER_REGISTRATION);
                                        $methodid = Ref_SMSApiMethodsModel::PLAYER_REGISTRATION;
                                        $mobileno = $memberInfos["MobileNo"];
                                        $smslastinsertedid = $smsRequestLogsModel->insertSMSRequestLogs($methodid, $mobileno, $memberInfos["DateCreated"]);
                                        if($smslastinsertedid != 0 && $smslastinsertedid != ''){
                                            $trackingid = "SMSR".$smslastinsertedid;
                                            $apiURL = Yii::app()->params["SMSURI"];    
                                            $app_id = Yii::app()->params["app_id"];    
                                            $membershipSMSApi = new MembershipSmsAPI($apiURL, $app_id);
                                            $smsresult = $membershipSMSApi->sendRegistration($mobileno, $templateid, $memberInfos["DateCreated"], $memberInfos["TemporaryAccountCode"], $trackingid);
                                            
//                                            if($smsresult['status'] != 1){
//                                                $message = "Failed to send SMS.";
//                                                $logger->log($logger->logdate,"[REGISTERMEMBER ERROR] ", $message);
//                                                $apiDetails = "REGISTERMEMBER-Failed: Failed to send SMS. [MID = $lastInsertedMID].";
//                                                $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, '', $apiDetails, '', 2);
//                                                if($isInserted == 0) {
//                                                    $logMessage = "Failed to insert to APILogs.";
//                                                    $logger->log($logger->logdate, " [REGISTERMEMBER ERROR] ", $logMessage);
//                                                }
//                                                exit;
//                                            }
//                                            else {
                                                $transMsg = "No error, Transaction successful.";
                                                $errorCode = 0;
                                                Utilities::log("ReturnMessage: " . $transMsg . " ErrorCode: " . $errorCode);
                                                $this->_sendResponse(200, CJSON::encode(CommonController::retMsgRegisterMember($module, $errorCode, $transMsg)));
                                                $logMessage = 'Registration is successful.';
                                                $logger->log($logger->logdate, " [REGISTERMEMBER SUCCESS] ", $logMessage);
                                                $apiDetails = 'REGISTERMEMBER-Success: Registration is successful. MID = '.$lastInsertedMID;
                                                $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, '', $apiDetails, '', 1);
                                                if($isInserted == 0) {
                                                    $logMessage = "Failed to insert to APILogs.";
                                                    $logger->log($logger->logdate, " [REGISTERMEMBER ERROR] ", $logMessage);
                                                }
                                                exit;
                                            //}
                                        } else {
                                            $message = "Failed to send SMS: Error on logging event in database.";
                                            $logger->log($logger->logdate,"[REGISTERMEMBER ERROR] ", $message);
                                            $apiDetails = "REGISTERMEMBER-Failed: Failed to send SMS. Error on logging event in database. [MID = $lastInsertedMID].";
                                            $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, '', $apiDetails, '', 2);
                                            if($isInserted == 0) {
                                                $logMessage = "Failed to insert to APILogs.";
                                                $logger->log($logger->logdate, " [REGISTERMEMBER ERROR] ", $logMessage);
                                            }
                                            exit;
                                        }
                                    }
                                } else {
                                    $match = substr($memberInfos["MobileNo"], 0, 2);
                                    if($match == "09"){
                                        $mncount = count($memberInfos["MobileNo"]);
                                        
                                        if(!$mncount == 11){
                                             $message = "Failed to send SMS: Invalid Mobile Number.";
                                             $logger->log($logger->logdate,"[REGISTERMEMBER ERROR] ", $message);
                                             $apiDetails = "REGISTERMEMBER-Failed: Failed to send SMS. Invalid Mobile Number [MID = $lastInsertedMID].";
                                             $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, '', $apiDetails, '', 2);
                                             if($isInserted == 0) {
                                                $logMessage = "Failed to insert to APILogs.";
                                                $logger->log($logger->logdate, " [REGISTERMEMBER ERROR] ", $logMessage);
                                             }
                                             exit;
                                         } else {
                                            $mobileno = str_replace("09", "639", $memberInfos["MobileNo"]);
                                            $templateid = $ref_SMSApiMethodsModel->getSMSMethodTemplateID(Ref_SMSApiMethodsModel::PLAYER_REGISTRATION);
                                            $methodid = Ref_SMSApiMethodsModel::PLAYER_REGISTRATION;
                                            $smslastinsertedid = $smsRequestLogsModel->insertSMSRequestLogs($methodid, $mobileno, $memberInfos["DateCreated"]);
                                            if($smslastinsertedid != 0 && $smslastinsertedid != ''){
                                                
                                                $trackingid = "SMSR".$smslastinsertedid;
                                                $apiURL = Yii::app()->params['SMSURI'];   
                                                $app_id = Yii::app()->params['app_id'];  
                                                $membershipSMSApi = new MembershipSmsAPI($apiURL, $app_id);
                                                $smsresult = $membershipSMSApi->sendRegistration($mobileno, $templateid, $memberInfos["DateCreated"], $memberInfos["TemporaryAccountCode"], $trackingid);
//                                                
//                                                if($smsresult['status'] != 1){
//                                                    
//                                                    $message = "Failed to send SMS.";
//                                                    $logger->log($logger->logdate,"[REGISTERMEMBER ERROR] ", $message);
//                                                    $apiDetails = "REGISTERMEMBER-Failed: Failed to send SMS. [MID = $lastInsertedMID].";
//                                                    $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, '', $apiDetails, '', 2);
//                                                    if($isInserted == 0) {
//                                                        $logMessage = "Failed to insert to APILogs.";
//                                                        $logger->log($logger->logdate, " [REGISTERMEMBER ERROR] ", $logMessage);
//                                                    }
//                                                    exit;
//                                                }
//                                                else {
                                                    $transMsg = "No error, Transaction successful.";
                                                    $errorCode = 0;
                                                    Utilities::log("ReturnMessage: " . $transMsg . " ErrorCode: " . $errorCode);
                                                    $this->_sendResponse(200, CJSON::encode(CommonController::retMsgRegisterMember($module, $errorCode, $transMsg)));
                                                    $logMessage = 'Registration is successful.';
                                                    $logger->log($logger->logdate, " [REGISTERMEMBER SUCCESS] ", $logMessage);
                                                    $apiDetails = 'REGISTERMEMBER-Success: Registration is successful. MID = '.$lastInsertedMID;
                                                    $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, '', $apiDetails, '', 1);
                                                    if($isInserted == 0) {
                                                        $logMessage = "Failed to insert to APILogs.";
                                                        $logger->log($logger->logdate, " [REGISTERMEMBER ERROR] ", $logMessage);
                                                    }
                                                    exit;
                                               // }
                                            } else {
                                                $message = "Failed to send SMS: Error on logging event in database.";
                                                $logger->log($logger->logdate,"[REGISTRATION ERROR] ", $message);
                                                $apiDetails = "REGISTERMEMBER-Failed: Failed to send SMS. Error on logging event in database [MID = $lastInsertedMID].";
                                                $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, '', $apiDetails, '', 2);
                                                if($isInserted == 0) {
                                                    $logMessage = "Failed to insert to APILogs.";
                                                    $logger->log($logger->logdate, " [REGISTERMEMBER ERROR] ", $logMessage);
                                                }
                                                exit;
                                            }
                                         }
                                    } else {
                                        $message = "Failed to send SMS: Invalid Mobile Number.";
                                        $logger->log($logger->logdate,"[REGISTRATION ERROR] ", $message);
                                        $apiDetails = "REGISTERMEMBER-Failed: Failed to send SMS. Invalid Mobile Number [MID = $lastInsertedMID].";
                                        $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, '', $apiDetails, '', 2);
                                        if($isInserted == 0) {
                                            $logMessage = "Failed to insert to APILogs.";
                                            $logger->log($logger->logdate, " [REGISTERMEMBER ERROR] ", $logMessage);
                                        }
                                        exit;
                                    }
                                }
                                
                                $auditTrailModel->logEvent(AuditTrailModel::PLAYER_REGISTRATION, $emailAddress, array('ID' => $ID, 'SessionID' => $sessionID) );
                            }
                            else {
                                //check if email is already verified in temp table
                                $tempEmail = $membershipTempModel->checkTempVerifiedEmail($emailAddress);
                                if($tempEmail['COUNT(a.MID)'] > 0) {
                                    $transMsg = "Email is already verified. Please choose a different email address.";
                                    $errorCode = 22;
                                    Utilities::log("ReturnMessage: " . $transMsg . " ErrorCode: " . $errorCode);
                                    $this->_sendResponse(200, CJSON::encode(CommonController::retMsgRegisterMember($module, $errorCode, $transMsg)));
                                    $logMessage = 'Email is already verified. Please choose a different email address.';
                                    $logger->log($logger->logdate, " [REGISTERMEMBER ERROR] ", $logMessage);
                                    $apiDetails = 'REGISTERMEMBER-Failed: Email is already verified. Email = '.$emailAddress;
                                    $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, '', $apiDetails, '', 2);
                                    if($isInserted == 0) {
                                        $logMessage = "Failed to insert to APILogs.";
                                        $logger->log($logger->logdate, " [REGISTERMEMBER ERROR] ", $logMessage);
                                    }
                                    exit;
                                }
                                else {
                                    $lastInsertedMID = $membershipTempModel->register($emailAddress, $firstname, $middlename, $lastname, $nickname, $password, $permanentAddress, $mobileNumber, $alternateMobileNumber, $alternateEmail, $idNumber, $idPresented, $gender, $referralCode, $birthdate, $occupationID, $nationalityID, $isSmoker);
                                    
                                    if($lastInsertedMID > 0) {
                                        
                                        $ID = 0;
                                        $sessionID = '';
                                        
                                        $memberInfos = $membershipTempModel->getTempMemberInfoForSMS($lastInsertedMID);

                                        //match to 09 or 639 in mobile number
                                        $match = substr($memberInfos['MobileNo'], 0, 3);
                                        if($match == "639"){
                                            $mncount = count($memberInfos["MobileNo"]);
                                            if(!$mncount == 12){
                                                $message = "Failed to send SMS. Invalid Mobile Number.";
                                                $logger->log($logger->logdate,"[REGISTERMEMBER ERROR] ", $message);
                                                $apiDetails = "REGISTERMEMBER-Failed: Failed to send SMS. Invalid Mobile Number [MID = $lastInsertedMID].";
                                                $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, '', $apiDetails, '', 2);
                                                if($isInserted == 0) {
                                                    $logMessage = "Failed to insert to APILogs.";
                                                    $logger->log($logger->logdate, " [REGISTERMEMBER ERROR] ", $logMessage);
                                                }
                                                exit;
                                            } else {
                                                $templateid = $ref_SMSApiMethodsModel->getSMSMethodTemplateID(Ref_SMSApiMethodsModel::PLAYER_REGISTRATION);
                                                $methodid = Ref_SMSApiMethodsModel::PLAYER_REGISTRATION;
                                                $mobileno = $memberInfos["MobileNo"];
                                                $smslastinsertedid = $smsRequestLogsModel->insertSMSRequestLogs($methodid, $mobileno, $memberInfos["DateCreated"]);
                                                if($smslastinsertedid != 0 && $smslastinsertedid != ''){
                                                    $trackingid = "SMSR".$smslastinsertedid;
                                                    $apiURL = Yii::app()->params["SMSURI"];    
                                                    $app_id = Yii::app()->params["app_id"];    
                                                    $membershipSMSApi = new MembershipSmsAPI($apiURL, $app_id);
//                                                    $smsresult = $membershipSMSApi->sendRegistration($mobileno, $templateid, $memberInfos["DateCreated"], $memberInfos["TemporaryAccountCode"], $trackingid);
//                                                    if($smsresult['status'] != 1){
//                                                        $message = "Failed to send SMS.";
//                                                        $logger->log($logger->logdate,"[REGISTERMEMBER ERROR] ", $message);
//                                                        $apiDetails = "REGISTERMEMBER-Failed: Failed to send SMS. [MID = $lastInsertedMID].";
//                                                        $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, '', $apiDetails, '', 2);
//                                                        if($isInserted == 0) {
//                                                            $logMessage = "Failed to insert to APILogs.";
//                                                            $logger->log($logger->logdate, " [REGISTERMEMBER ERROR] ", $logMessage);
//                                                        }
//                                                        exit;
//                                                    }
//                                                    else {
                                                        $transMsg = "No error, Transaction successful.";
                                                        $errorCode = 0;
                                                        Utilities::log("ReturnMessage: " . $transMsg . " ErrorCode: " . $errorCode);
                                                        $this->_sendResponse(200, CJSON::encode(CommonController::retMsgRegisterMember($module, $errorCode, $transMsg)));
                                                        $logMessage = 'Registration is successful.';
                                                        $logger->log($logger->logdate, " [REGISTERMEMBER SUCCESS] ", $logMessage);
                                                        $apiDetails = 'REGISTERMEMBER-Success: Registration is successful. MID = '.$lastInsertedMID;
                                                        $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, '', $apiDetails, '', 1);
                                                        if($isInserted == 0) {
                                                            $logMessage = "Failed to insert to APILogs.";
                                                            $logger->log($logger->logdate, " [REGISTERMEMBER ERROR] ", $logMessage);
                                                        }
                                                        exit;
                                                   // }
                                                } else {
                                                    $message = "Failed to send SMS: Error on logging event in database.";
                                                    $logger->log($logger->logdate,"[REGISTERMEMBER ERROR] ", $message);
                                                    $apiDetails = "REGISTERMEMBER-Failed: Failed to send SMS. Error on logging event in database. [MID = $lastInsertedMID].";
                                                    $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, '', $apiDetails, '', 2);
                                                    if($isInserted == 0) {
                                                        $logMessage = "Failed to insert to APILogs.";
                                                        $logger->log($logger->logdate, " [REGISTERMEMBER ERROR] ", $logMessage);
                                                    }
                                                    exit;
                                                }
                                            }
                                        } else {
                                            $match = substr($memberInfos["MobileNo"], 0, 2);
                                            if($match == "09"){
                                                $mncount = count($memberInfos["MobileNo"]);
                                                if(!$mncount == 11){
                                                     $message = "Failed to send SMS: Invalid Mobile Number.";
                                                     $logger->log($logger->logdate,"[REGISTERMEMBER ERROR] ", $message);
                                                     $apiDetails = "REGISTERMEMBER-Failed: Failed to send SMS. Invalid Mobile Number [MID = $lastInsertedMID].";
                                                     $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, '', $apiDetails, '', 2);
                                                     if($isInserted == 0) {
                                                        $logMessage = "Failed to insert to APILogs.";
                                                        $logger->log($logger->logdate, " [REGISTERMEMBER ERROR] ", $logMessage);
                                                     }
                                                     exit;
                                                 } else {
                                                    $mobileno = str_replace("09", "639", $memberInfos["MobileNo"]);
                                                    $templateid = $ref_SMSApiMethodsModel->getSMSMethodTemplateID(Ref_SMSApiMethodsModel::PLAYER_REGISTRATION);
                                                    $methodid = Ref_SMSApiMethodsModel::PLAYER_REGISTRATION;
                                                    $smslastinsertedid = $smsRequestLogsModel->insertSMSRequestLogs($methodid, $mobileno, $memberInfos["DateCreated"]);
                                                    if($smslastinsertedid != 0 && $smslastinsertedid != ''){
                                                        $trackingid = "SMSR".$smslastinsertedid;
                                                        $apiURL = Yii::app()->params['SMSURI'];   
                                                        $app_id = Yii::app()->params['app_id'];  
                                                        $membershipSMSApi = new MembershipSmsAPI($apiURL, $app_id);
//                                                        $smsresult = $membershipSMSApi->sendRegistration($mobileno, $templateid, $memberInfos["DateCreated"], $memberInfos["TemporaryAccountCode"], $trackingid);
//                                                        if($smsresult['status'] != 1){
//                                                            $message = "Failed to send SMS.";
//                                                            $logger->log($logger->logdate,"[REGISTERMEMBER ERROR] ", $message);
//                                                            $apiDetails = "REGISTERMEMBER-Failed: Failed to send SMS. [MID = $lastInsertedMID].";
//                                                            $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, '', $apiDetails, '', 2);
//                                                            if($isInserted == 0) {
//                                                                $logMessage = "Failed to insert to APILogs.";
//                                                                $logger->log($logger->logdate, " [REGISTERMEMBER ERROR] ", $logMessage);
//                                                            }
//                                                            exit;
//                                                        }
//                                                        else {
                                                            $transMsg = "No error, Transaction successful.";
                                                            $errorCode = 0;
                                                            Utilities::log("ReturnMessage: " . $transMsg . " ErrorCode: " . $errorCode);
                                                            $this->_sendResponse(200, CJSON::encode(CommonController::retMsgRegisterMember($module, $errorCode, $transMsg)));
                                                            $logMessage = 'Registration is successful.';
                                                            $logger->log($logger->logdate, " [REGISTERMEMBER SUCCESS] ", $logMessage);
                                                            $apiDetails = 'REGISTERMEMBER-Success: Registration is successful. MID = '.$lastInsertedMID;
                                                            $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, '', $apiDetails, '', 1);
                                                            if($isInserted == 0) {
                                                                $logMessage = "Failed to insert to APILogs.";
                                                                $logger->log($logger->logdate, " [REGISTERMEMBER ERROR] ", $logMessage);
                                                            }
                                                            exit;
                                                      //  }
                                                    } else {
                                                        $message = "Failed to send SMS: Error on logging event in database.";
                                                        $logger->log($logger->logdate,"[REGISTRATION ERROR] ", $message);
                                                        $apiDetails = "REGISTERMEMBER-Failed: Failed to send SMS. Error on logging event in database [MID = $lastInsertedMID].";
                                                        $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, '', $apiDetails, '', 2);
                                                        if($isInserted == 0) {
                                                            $logMessage = "Failed to insert to APILogs.";
                                                            $logger->log($logger->logdate, " [REGISTERMEMBER ERROR] ", $logMessage);
                                                        }
                                                        exit;
                                                    }
                                                 }
                                            } else {
                                                $message = "Failed to send SMS: Invalid Mobile Number.";
                                                $logger->log($logger->logdate,"[REGISTRATION ERROR] ", $message);
                                                $apiDetails = "REGISTERMEMBER-Failed: Failed to send SMS. Invalid Mobile Number [MID = $lastInsertedMID].";
                                                $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, '', $apiDetails, '', 2);
                                                if($isInserted == 0) {
                                                    $logMessage = "Failed to insert to APILogs.";
                                                    $logger->log($logger->logdate, " [REGISTERMEMBER ERROR] ", $logMessage);
                                                }
                                                exit;
                                            }
                                        }

                                        $auditTrailModel->logEvent(AuditTrailModel::PLAYER_REGISTRATION, $emailAddress, array('ID' => $ID, 'SessionID' => $sessionID) );
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
                                            $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, '', $apiDetails, '', 2);
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
                                            $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, '', $apiDetails, '', 2);
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
            else if(is_numeric($request['MobileNo']) == FALSE || is_numeric($request['AlternateMobileNo']) == FALSE) {
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
            else if(!Utilities::validateEmail($request['EmailAddress']) || !Utilities::validateEmail($request['AlternateEmail']) ) {
                $transMsg = "Invalid Email Address.";
                $errorCode = 5;
                Utilities::log("ReturnMessage: " . $transMsg . " ErrorCode: " . $errorCode);
                $this->_sendResponse(200, CJSON::encode(CommonController::retMsgUpdateProfile($module, $transMsg, $errorCode)));
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
            else {
                
                //start of declaration of models to be used
                $memberInfoModel = new MemberInfoModel();
                $memberCardsModel = new MemberCardsModel();
                $memberSessionsModel = new MemberSessionsModel();
                $membershipTempModel = new MembershipTempModel();
                $membersModel = new MembersModel();
                $logger = new ErrorLogger();
                $auditTrailModel = new AuditTrailModel();
                
//                if(isset(Yii::app()->session['SessionID'])) {
//                    $sessionID = Yii::app()->session['SessionID'];
//                }
//                else {
//                    $sessionID = 0;
//                }
//                
//                if(isset(Yii::app()->session['MID'])) {
//                    $MID = Yii::app()->session['MID'];
//                }
//                else {
//                    $MID = 0;
//                }
//                
//                if(isset(Yii::app()->session['Email'])) {
//                    $email = Yii::app()->session['Email'];
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
                //$confirmPassword = md5(trim($request['ConfirmPassword']));
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
                    $transMsg = "Not connected.";
                    $errorCode = 13;
                    Utilities::log("ReturnMessage: " . $transMsg . " ErrorCode: " . $errorCode);
                    $this->_sendResponse(200, CJSON::encode(CommonController::retMsgUpdateProfile($module, $errorCode, $transMsg)));
                    $logMessage = 'Not connected.';
                    $logger->log($logger->logdate, " [LISTITEMS ERROR] ", $logMessage);
                    $apiDetails = 'UPDATEPROFILE-Failed: There is no active session. MID = '.$MID;
                    $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, '', $apiDetails, '', 2);
                    if($isInserted == 0) {
                        $logMessage = "Failed to insert to APILogs.";
                        $logger->log($logger->logdate, " [LISTITEMS ERROR] ", $logMessage);
                    }
                    exit; 
                }
                
                $isExist = $memberSessionsModel->checkIfSessionExist($MID, $mpSessionID);
                
                if(count($isExist) > 0) {
                    //check if from old to newly migrated card
                    if(!is_null($emailAddress)) {
                        $tempMID = $membershipTempModel->getMID($emailAddress);
                        if(empty($tempMID))
                            $tempMID['MID'] = 0;
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
                        $transMsg = "Sorry, " . $emailAddress . "already belongs to an existing account. Please enter another email address.";
                        $errorCode = 21;
                        Utilities::log("ReturnMessage: " . $transMsg . " ErrorCode: " . $errorCode);
                        $this->_sendResponse(200, CJSON::encode(CommonController::retMsgUpdateProfile($module, $transMsg, $errorCode)));
                        $logMessage = 'Sorry, ' . $emailAddress . 'already belongs to an existing account. Please enter another email address.';
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
                        //proceed with the updating of member profile
                        $result = $memberInfoModel->updateProfile($firstname, $middlename, $lastname, $nickname, $mid, $permanentAddress, $mobileNumber, $alternateMobileNumber, $emailAddress, $alternateEmail, $birthdate, $nationalityID, $occupationID, $idNumber, $idPresented, $gender, $isSmoker);
                        
                        if($result > 0) {
                            $result2 = $membersModel->updateMemberUsername($mid, $emailAddress, $password);
                            
                            if($result2 > 0) {
                                $result3 = $membershipTempModel->updateTempEmail($MID, $emailAddress);
                                
                                if($result3 > 0) {
                                    $result4 = $membershipTempModel->updateTempMemberUsername($MID, $emailAddress, $password);
                                    
                                    if($result4 > 0) {
                                        $isSuccessful = $auditTrailModel->logEvent(AuditTrailModel::UPDATE_PROFILE, $emailAddress, array('MID' => $MID, 'SessionID' => $mpSessionID));
                                        if($isSuccessful == 0) {
                                            $logMessage = "Failed to insert to Audittrail.";
                                            $logger->log($logger->logdate, " [UPDATEPROFILE ERROR] ", $logMessage);
                                        }
                                        unset(Yii::app()->session['Email']);
                                        Yii::app()->session['Email'] = $emailAddress;
                                        
                                        $result5 = $memberInfoModel->updateProfileDateUpdated($MID, $mid);
                                        $result6 = $membershipTempModel->updateTempProfileDateUpdated($MID, $mid);
                                        
                                        if($result5 > 0 && $result6 > 0) {
                                            $transMsg = 'No Error, Transaction successful.';
                                            $errorCode = 0;
                                            $this->_sendResponse(200, CJSON::encode(CommonController::retMsgUpdateProfile($module, $transMsg, $errorCode)));
                                            $logMessage = 'Update profile successful.';
                                            $logger->log($logger->logdate, " [UPDATEPROFILE SUCCESS] ", $logMessage);
                                            $apiDetails = 'UPDATEPROFILE-UpdateProfile/TempDateUpdated-Success: Username = '.$emailAddress.' MID = '.$MID;
                                            $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, '', $apiDetails, '', 1);
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
                                            $this->_sendResponse(200, CJSON::encode(CommonController::retMsgUpdateProfile($module, $transMsg, $errorCode)));
                                            $logMessage = 'Transaction failed.';
                                            $logger->log($logger->logdate, " [UPDATEPROFILE ERROR] ", $logMessage);
                                            $apiDetails = 'UPDATEPROFILE-UpdateProfile/TempDateUpdated-Failed: Username = '.$emailAddress.' MID = '.$MID;
                                            $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, '', $apiDetails, '', 2);
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
                                        $this->_sendResponse(200, CJSON::encode(CommonController::retMsgUpdateProfile($module, $transMsg, $errorCode)));
                                        $logMessage = 'Transaction failed.';
                                        $logger->log($logger->logdate, " [UPDATEPROFILE ERROR] ", $logMessage);
                                        $apiDetails = 'UPDATEPROFILE-UpdateTempMemberUsername-Failed: Username = '.$emailAddress.' MID = '.$MID;
                                        $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, '', $apiDetails, '', 2);
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
                                    $this->_sendResponse(200, CJSON::encode(CommonController::retMsgUpdateProfile($module, $transMsg, $errorCode)));
                                    $logMessage = 'Transaction failed.';
                                    $logger->log($logger->logdate, " [UPDATEPROFILE ERROR] ", $logMessage);
                                    $apiDetails = 'UPDATEPROFILE-UpdateTempEmail-Failed: Username = '.$emailAddress.' MID = '.$MID;
                                    $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, '', $apiDetails, '', 2);
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
                                $this->_sendResponse(200, CJSON::encode(CommonController::retMsgUpdateProfile($module, $transMsg, $errorCode)));
                                $logMessage = 'Transaction failed.';
                                $logger->log($logger->logdate, " [UPDATEPROFILE ERROR] ", $logMessage);
                                $apiDetails = 'UPDATEPROFILE-UpdateMemberUsername-Failed: Username = '.$emailAddress.' MID = '.$mid;
                                $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, '', $apiDetails, '', 2);
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
                            $this->_sendResponse(200, CJSON::encode(CommonController::retMsgUpdateProfile($module, $transMsg, $errorCode)));
                            $logMessage = 'Transaction failed.';
                            $logger->log($logger->logdate, " [UPDATEPROFILE ERROR] ", $logMessage);
                            $apiDetails = 'UPDATEPROFILE-UpdateProfileMemberInfo-Failed: Name = '.$firstname.' '.$middlename.' '.$lastname.' MID = '.$mid;
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
                    $transMsg = "Not connected.";
                    $errorCode = 13;
                    Utilities::log("ReturnMessage: " . $transMsg . " ErrorCode: " . $errorCode);
                    $this->_sendResponse(200, CJSON::encode(CommonController::retMsgUpdateProfile($module, $transMsg, $errorCode)));
                    $logMessage = 'Not connected.';
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
                $this->_sendResponse(200, CJSON::encode(CommonController::retMsgCheckPoints($module, '',  $errorCode, $transMsg)));
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
                            
                //start of declaration of models to be used
                $memberCardsModel = new MemberCardsModel();
                $auditTrailModel = new AuditTrailModel();
                
                //$data = $memberCardsModel->getMemberDetailsByCard($cardNumber);
                //$cardTypeID = $data[0]['CardTypeID'];
//                $status = $data[0]['Status'];
//                
                $memberPoints = $memberCardsModel->getMemberPointsAndStatus($cardNumber);
                
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
                        case 2: $message = 'Card is deactivated';
                        break;
                        case 7: $message = 'Card is already Migrated';
                        break;
                        case 8: $message = 'Card is already Migrated';
                        break;
                        case 9: $message = 'Card is Banned';
                        break;
                        default: $message = 'Card is invalid';
                        break;
                    }
                    
                    $isSuccessful = $auditTrailModel->logEvent(AuditTrailModel::CHECK_POINTS, $cardNumber, array());
                    if($isSuccessful == 0) {
                        $logMessage = "Failed to insert to Audittrail.";
                        $logger->log($logger->logdate, " [CHECKPOINTS ERROR] ", $logMessage);
                    }
                    
                    //$transMsg = 'No Error, Transaction successful.';
                    $transMsg = $message;
                    $errorCode = $status;
                    $this->_sendResponse(200, CJSON::encode(CommonController::retMsgCheckPoints($module, $currentPoints, $errorCode, $transMsg)));
                    $logMessage = 'Check Points is successful.';
                    $logger->log($logger->logdate, " [CHECKPOINTS SUCCESSFUL] ", $logMessage);
                    $apiDetails = 'CHECKPOINTS-Successful: Card Number = .'.$cardNumber;
                    $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, '', $apiDetails, '', 2);
                    if($isInserted == 0) {
                        $logMessage = "Failed to insert to APILogs.";
                        $logger->log($logger->logdate, " [CHECKPOINTS ERROR] ", $logMessage);
                    }
                    exit;
                    
                }
                else {
                    $transMsg = "Invalid Card Number";
                    $errorCode = 6;
                    Utilities::log("ReturnMessage: " . $transMsg . " ErrorCode: " . $errorCode);
                    $this->_sendResponse(200, CJSON::encode(CommonController::retMsgCheckPoints($module, '', $errorCode, $transMsg)));
                    $logMessage = 'Invalid Card Number.';
                    $logger->log($logger->logdate, " [CHECKPOINTS ERROR] ", $logMessage);
                    $apiDetails = 'CHECKPOINTS-Failed: Membership card is invalid. Card Number = '.$cardNumber;
                    $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, '', $apiDetails, '', 2);
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
            $this->_sendResponse(200, CJSON::encode(CommonController::retMsgCheckPoints($module, '', $errorCode, $transMsg)));
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
        
        $logger = new ErrorLogger();
        $apiLogsModel = new APILogsModel();
        
        if(isset($request['MPSessionID']) && isset($request['PlayerClassID'])) {
            if($request['MPSessionID'] == '' || $request['PlayerClassID'] == '') {
                $transMsg = "One or more fields is not set or is blank.";
                $errorCode = 1;
                Utilities::log("ReturnMessage: " . $transMsg . " ErrorCode: " . $errorCode);
                $this->_sendResponse(200, CJSON::encode(CommonController::retMsgListItems($module, '', $errorCode, $transMsg)));
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
                
//                if(isset(Yii::app()->session['SessionID'])) {
//                    $sessionID = Yii::app()->session['SessionID'];
//                }
//                else {
//                    $sessionID = 0;
//                }
                
                $memberSessions = $memberSessionsModel->getMID($mpSessionID);
                                
                
//                if(isset(Yii::app()->session['MID'])) {
//                    $MID = Yii::app()->session['MID'];
//                }
//                else {
//                    $MID = 0;
//                }
                
//                var_dump($MID);
//                //var_dump($sessionID);
//                exit;
                if($memberSessions)
                    $MID = $memberSessions['MID'];
                else {
                    $transMsg = "Not connected.";
                    $errorCode = 13;
                    Utilities::log("ReturnMessage: " . $transMsg . " ErrorCode: " . $errorCode);
                    $this->_sendResponse(200, CJSON::encode(CommonController::retMsgListItems($module, '', $errorCode, $transMsg)));
                    $logMessage = 'Not connected.';
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
                
                    $rewardoffers = $rewardItemsModel->getAllRewardOffersBasedOnPlayerClassification($playerClassID);
//                    var_dump($rewardoffers);
//                    exit;
                    //$rewardname = $rewardoffers['ProductName'];
                    for($itr = 0; $itr < count($rewardoffers); $itr++) {
                        //$rewardname[$itr] = $rewardoffers[$itr]["ItemName"];
                        preg_match('/\((.*?)\)/', $rewardoffers[$itr]["ProductName"], $rewardoffers[$itr]["ProductName"]);
                        if (is_array($rewardname) && isset($rewardname[1])) {
                            unset($rewardoffers[$itr]["ProductName"]);
                            $rewardoffers[$itr]["ProductName"] = $rewardname[1];

                        }
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
                        $mysterySubtext[$itr] = $mysteryAbout = $rewardoffers[$itr]["MysterySubtext"];
                        
                        $itemsList[$itr] = array('RewardID' => $rewardID[$itr], 'RewardItemID' => $rewardItemID[$itr], 'Description' => $description[$itr], 'AvailableItemCount' => $availableItemCount[$itr], 
                                       'ProductName' => $productName[$itr], 'PartnerName' => $partnerName[$itr], 'Points' => $points[$itr], 'ThumbnailLimitedImage' => $thumbnailLimitedImage[$itr], 
                                       'ECouponImage' => $eCouponImage[$itr], 'LearnMoreLimitedImage' => $learnMoreLimitedImage[$itr], 'LearnMoreOutOfStockImage' => $learnMoreOutOfStockImage[$itr],
                                       'ThumbnailOutOfStockImage' => $thumbnailOutOfStockImage[$itr], 'PromoName' => $promoName[$itr], 'IsMystery' => $isMystery[$itr],
                                       'MysteryName' => $mysteryName[$itr], 'MysteryAbout' => $mysteryAbout[$itr], 'MysteryTerms' => $mysteryTerms[$itr], 'MysterySubtext' => $mysterySubtext[$itr]);
                    }
                    
                    
                    
                    $isSuccessful = $auditTrailModel->logEvent(AuditTrailModel::LIST_ITEMS, $playerClassID, array('MID' => $MID,'SessionID' => $mpSessionID));
                    
                    if($isSuccessful == 0) {
                        $logMessage = "Failed to insert to Audittrail.";
                        $logger->log($logger->logdate, " [LISTITEMS ERROR] ", $logMessage);
                    }

                    $transMsg = 'No Error, Transaction successful.';
                    $errorCode = 0;
//                    $itemsList = array('RewardID' => $rewardID, 'RewardItemID' => $rewardItemID, 'Description' => $description, 'AvailableItemCount' => $availableItemCount, 
//                                       'ProductName' => $productName, 'PartnerName' => $partnerName, 'Points' => $points, 'ThumbnailLimitedImage' => $thumbnailLimitedImage, 
//                                       'ECouponImage' => $eCouponImage, 'LearnMoreLimitedImage' => $learnMoreLimitedImage, 'LearnMoreOutOfStockImage' => $learnMoreOutOfStockImage,
//                                       'ThumbnailOutOfStockImage' => $thumbnailOutOfStockImage, 'PromoName' => $promoName, 'IsMystery' => $isMystery,
//                                       'MysteryName' => $mysteryName, 'MysteryAbout' => $mysteryAbout, 'MysteryTerms' => $mysteryTerms, 'MysterySubtext' => $mysterySubtext);
                    $this->_sendResponse(200, CJSON::encode(CommonController::retMsgListItems($module, $itemsList, $errorCode, $transMsg)));
                    $logMessage = 'List Items is successful.';
                    $logger->log($logger->logdate, " [LISTITEMS SUCCESSFUL] ", $logMessage);
                    $apiDetails = 'LISTITEMS-Successful: Player Classification = .'.$playerClassID;
                    $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, '', $apiDetails, '', 1);
                    if($isInserted == 0) {
                        $logMessage = "Failed to insert to APILogs.";
                        $logger->log($logger->logdate, " [LISTITEMS ERROR] ", $logMessage);
                    }
                    exit;
                }
                else {
                    $transMsg = "Not connected.";
                    $errorCode = 13;
                    Utilities::log("ReturnMessage: " . $transMsg . " ErrorCode: " . $errorCode);
                    $this->_sendResponse(200, CJSON::encode(CommonController::retMsgListItems($module, '', $errorCode, $transMsg)));
                    $logMessage = 'Not connected.';
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
            $this->_sendResponse(200, CJSON::encode(CommonController::retMsgListItems($module, '', $errorCode, $transMsg)));
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
        
        $logger = new ErrorLogger();
        $apiLogsModel = new APILogsModel();
        
        if(isset($request['CardNumber']) && isset($request['RewardItemID']) && isset($request['Quantity']) 
           && isset($request['RewardID']) && isset($request['Source']) && isset($request['MPSessionID'])) {
            if(($request['CardNumber'] == '') || ($request['RewardItemID'] == '') || ($request['RewardID'] == '') || ($request['Quantity'] == '') 
                || ($request['Source'] == '') || ($request['MPSessionID'] == '')) {
                $transMsg = "One or more fields is not set or is blank.";
                $errorCode = 1;
                Utilities::log("ReturnMessage: " . $transMsg . " ErrorCode: " . $errorCode);
                $this->_sendResponse(200, CJSON::encode(CommonController::retMsg($module, $errorCode, $transMsg)));
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
                
                
//                if($rewardID == 1) {
//                    $qty = $quantity;
//                }
//                else {
//                    $qty = $itemQuantity;
//                }
                
                $result = $memberSessionsModel->getMID($mpSessionID);
                if($result)
                    $MID = $result['MID'];
                else {
                    $transMsg = "Not connected.";
                    $errorCode = 13;
                    Utilities::log("ReturnMessage: " . $transMsg . " ErrorCode: " . $errorCode);
                    $this->_sendResponse(200, CJSON::encode(CommonController::retMsg($module, $errorCode, $transMsg)));
                    $logMessage = 'Not connected.';
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
                    $mobileNumber = $memberInfo['MobileNo'];
                else {
                    $transMsg = "No member found for that account.";
                    $errorCode = 55;
                    Utilities::log("ReturnMessage: " . $transMsg . " ErrorCode: " . $errorCode);
                    $this->_sendResponse(200, CJSON::encode(CommonController::retMsg($module, $errorCode, $transMsg)));
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
                    if($source == 1) {
                        $result = $memberCardsModel->getMemberPointsAndStatus($cardNumber);
                        $currentPoints = $result['CurrentPoints'];
                    }
                    else {
                        $transMsg = "Please select 1 as source.";
                        $errorCode = 23;
                        Utilities::log("ReturnMessage: " . $transMsg . " ErrorCode: " . $errorCode);
                        $this->_sendResponse(200, CJSON::encode(CommonController::retMsg($module, $transMsg, $errorCode)));
                        $logMessage = 'Please select 1 as source.';
                        $logger->log($logger->logdate, " [REDEEMITEMS ERROR] ", $logMessage);
                        $apiDetails = 'REDEEMITEMS-Failed: Invalid input parameters.';
                        $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, '', $apiDetails, '', 2);
                        if($isInserted == 0) {
                            $logMessage = "Failed to insert to APILogs.";
                            $logger->log($logger->logdate, " [REDEEMITEMS ERROR] ", $logMessage);
                        }
                        exit;
                    }
                    
                    if($quantity > 0) {
                        $totalItemPoints = $quantity * $currentPoints;
                        if($currentPoints < $totalItemPoints) {
                            $logMessage = 'Transaction failed. Card has insufficient points.';
                            $logger->log($logger->logdate, " [REDEEMITEMS ERROR] ", $logMessage);
                            $apiDetails = 'REDEEMITEMS-Failed: Card has insufficient points. CurrentPoints = '.$currentPoints;
                            $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, '', $apiDetails, '', 2);
                            if($isInserted == 0) {
                                $logMessage = "Failed to insert to APILogs.";
                                $logger->log($logger->logdate, " [REDEEMITEMS ERROR] ", $logMessage);
                            }
                            
//                            $transMsg = "Transaction failed. Card has insufficient points.";
//                            $errorCode = 24;
//                            Utilities::log("ReturnMessage: " . $transMsg . " ErrorCode: " . $errorCode);
//                            $this->_sendResponse(200, CJSON::encode(CommonController::retMsg($module, $transMsg, $errorCode)));
                            if($rewardID == 1) {
                                $result = $auditTrailModel->logEvent(AuditTrailModel::PLAYER_ITEM_REDEMPTION, $transMsg, array('MID' => $MID, 'SessionID' => $sessionID));
                            }
                            else {
                                $result = $auditTrailModel->logEvent(AuditTrailModel::PLAYER_REDEMPTION, $transMsg, array('MID' => $MID, 'SessionID' => $sessionID));
                            }
                            
                            if($result > 0) {
                                $transMsg = "Transaction failed. Card has insufficient points.";
                                $errorCode = 24;
                                Utilities::log("ReturnMessage: " . $transMsg . " ErrorCode: " . $errorCode);
                                $this->_sendResponse(200, CJSON::encode(CommonController::retMsg($module, $transMsg, $errorCode)));
                                $quantity = '';
                                $totalItemPoints = '';
                                $logMessage = 'Transaction failed. Card has insufficient points.';
                                $logger->log($logger->logdate, " [REDEEMITEMS ERROR] ", $logMessage);
                                $apiDetails = 'REDEEMITEMS-Failed: Card has insufficient points. CurrentPoints = '.$currentPoints;
                                $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, '', $apiDetails, '', 2);
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
                                $transMsg = "Failed to log event on Audit Trail. "." $logType";
                                $errorCode = 25;
                                
                                $logger->log($logger->logdate,$logType, $logMessage);
                                Utilities::log("ReturnMessage: " . $transMsg . " ErrorCode: " . $errorCode);
                                $this->_sendResponse(200, CJSON::encode(CommonController::retMsg($module, $transMsg, $errorCode)));
                            }
                            
                        }
                        else {
                            $isCoupon = ($rewardID == 1 || $rewardID == "1") ? false:true;
                            
                            if($isCoupon) {
                                //Check if the available coupon is greater than or match with the quantity avail by the player.
                                $availableCoupon = $raffleCouponsModel->getAvailableCoupons($rewardItemID, $qty);
                                if(count($availableCoupon) == $qty) {
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
                                            $this->_sendResponse(200, CJSON::encode(CommonController::retMsg($module, $transMsg, $errorCode)));
                                            $logMessage = 'Transaction failed. Card has insufficient points.';
                                            $logger->log($logger->logdate, " [REDEEMITEMS ERROR] ", $logMessage);
                                            $apiDetails = 'REDEEMITEMS-Failed: Card has insufficient points. CurrentPoints = '.$currentPoints;
                                            $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, '', $apiDetails, '', 2);
                                            if($isInserted == 0) {
                                                $logMessage = "Failed to insert to APILogs.";
                                                $logger->log($logger->logdate, " [REDEEMITEMS ERROR] ", $logMessage);
                                            }
                                            //exit;
                                            $result = $auditTrailModel->logEvent(AuditTrailModel::PLAYER_REDEMPTION, $transMsg, array('MID' => $MID, 'SessionID' => $sessionID));
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
//                                                Utilities::log("ReturnMessage: " . $transMsg . " ErrorCode: " . $errorCode);
//                                                $this->_sendResponse(200, CJSON::encode(CommonController::retMsg($module, $transMsg, $errorCode)));
                                                
                                            }
                                        }
                                        else {
                                            $pendingRedemption = $pendingRedemptionModel->checkPendingRedemption($MID);
                                            
                                            //check if there is pending redemption, if there is, throw an error message
                                            if($pendingRedemption) {
                                                $transMsg = "Transaction failed. Card has a pending redemption.";
                                                $errorCode = 26;
                                                Utilities::log("ReturnMessage: " . $transMsg . " ErrorCode: " . $errorCode);
                                                $this->_sendResponse(200, CJSON::encode(CommonController::retMsg($module, $transMsg, $errorCode)));
                                                $logMessage = 'Transaction failed. Card has a pending redemption.';
                                                $logger->log($logger->logdate, " [REDEEMITEMS ERROR] ", $logMessage);
                                                $apiDetails = 'REDEEMITEMS-Failed: Card has a pending redemption. MID = '.$MID;
                                                $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, '', $apiDetails, '', 2);
                                                if($isInserted == 0) {
                                                    $logMessage = "Failed to insert to APILogs.";
                                                    $logger->log($logger->logdate, " [REDEEMITEMS ERROR] ", $logMessage);
                                                }
                                                
                                                $result = $auditTrailModel->logEvent(AuditTrailModel::PLAYER_REDEMPTION, $transMsg, array('MID' => $MID, 'SessionID' => $sessionID));
                                                
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
                                                exit;
                                            }
                                            else {
                                                //process coupon redemption
                                                $resultArray = Processing::processCouponRedemption($MID, $rewardItemID, $qty, $totalItemPoints, $cardNumber, 1, $redeemedDate);
                                                if($resultArray != 0 && $resultArray != '') {
                                                    $oldCurrentPoints = number_format($resultArray['OldCurrentPoints']);
                                                    $redeemedPoints = number_format($totalItemPoints);
                                                    $rewardItem = $rewardItemsModel->getItemDetails($rewardItemID);
                                                    $itemName = $rewardItem['ItemName'];
                                                    $message = "CP: ".$oldCurrentPoints.", Item: ".$itemName.", RP: ".$redeemedPoints.", Series: ".Yii::app()->Session['RewardOfferCopy']['CouponSeries'];
                                                }    
                                                $isLogged = $auditTrailModel->logEvent(AuditTrailModel::PLAYER_REDEMPTION, $message, array('MID'=>$MID, 'SessionID'=>$sessionID));
                                                if($isLogged == 0) {
                                                    $logMessage = "Failed to log event on Audit Trail.";
                                                    $logger->log($logger->logdate,"[COUPON REDEMPTION ERROR] ", $logMessage);
                                                }
                                                    
                                                $quantity = '';
                                                $totalItemPoints = '';

                                                if($resultArray == 0) {
                                                    $transMsg = "Transaction failed.";
                                                    $errorCode = 4;
                                                    Utilities::log("ReturnMessage: " . $transMsg . " ErrorCode: " . $errorCode);
                                                    $this->_sendResponse(200, CJSON::encode(CommonController::retMsg($module, $transMsg, $errorCode)));
                                                    $logMessage = 'Transaction failed.';
                                                    $logger->log($logger->logdate, " [REDEEMITEMS ERROR] ", $logMessage);
                                                    $apiDetails = 'REDEEMITEMS-Failed: Processing of coupon redemption failed. MID = '.$MID.'. RewardItemID = '.$rewardItemID.'. TotalItemPoints = '.$totalItemPoints.'. CardNumber = '.$cardNumber;
                                                    $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, '', $apiDetails, '', 2);
                                                    if($isInserted == 0) {
                                                        $logMessage = "Failed to insert to APILogs.";
                                                        $logger->log($logger->logdate, " [REDEEMITEMS ERROR] ", $logMessage);
                                                    }
                                                    exit;
                                                }
                                                else {
                                                    //send SMS alert to player
                                                    $this->_sendSMS(SMSRequestLogsModel::COUPON_REDEMPTION, $mobileNumber, $redeemedDate, yii::app()->session['RewardOfferCopy']['SerialNumber'], $qty, "SMSC", $resultArray['LastInsertedID'], '', yii::app()->session['RewardOfferCopy']['CouponSeries']);

                                                    //$showcouponredemptionwindow = true;
                                                    //$showitemredemptionwindow = false;
                                                }
                                           }
                                           
                                        }
                                    } else {
                                        $message = "Player Redemption: Transaction Failed. Reward Offer has already ended.";
                                        $isLogged = $auditTrailModel->logEvent(AuditTrailModel::PLAYER_REDEMPTION, $message, array('MID'=>$MID, 'SessionID'=>$sessionID));
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
                                        $this->_sendResponse(200, CJSON::encode(CommonController::retMsg($module, $transMsg, $errorCode)));
                                        $logMessage = 'Transaction failed. Reward offer has already ended';
                                        $logger->log($logger->logdate, " [REDEEMITEMS ERROR] ", $logMessage);
                                        $apiDetails = 'REDEEMITEMS-Failed: Reward offer has already ended. RewardItemID = '.$rewardItemID.'.'.', ItemCurrentDate = '.$redeemedDate;
                                        $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, '', $apiDetails, '', 2);
                                        if($isInserted == 0) {
                                            $logMessage = "Failed to insert to APILogs.";
                                            $logger->log($logger->logdate, " [REDEEMITEMS ERROR] ", $logMessage);
                                        }
                                        exit;
                                    }
                                } else {
                                    $message = "Transaction Failed. Raffle Coupon is either insufficient or unavailable.";
                                    $isLogged = $auditTrailModel->logEvent(AuditTrailModel::PLAYER_REDEMPTION, $message, array('MID'=>$MID, 'SessionID'=>$sessionID));
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
                                    $transMsg = "Transaction Failed. Raffle Coupon is either insufficient or unavailable.";
                                    $errorCode = 47;
                                    Utilities::log("ReturnMessage: " . $transMsg . " ErrorCode: " . $errorCode);
                                    $this->_sendResponse(200, CJSON::encode(CommonController::retMsg($module, $transMsg, $errorCode)));
                                    $logMessage = 'Transaction failed. Raffle coupon is either insufficient or unavailable.';
                                    $logger->log($logger->logdate, " [REDEEMITEMS ERROR] ", $logMessage);
                                    $apiDetails = 'REDEEMITEMS-Failed: Processing of coupon redemption failed. MID = '.$MID.'. RewardItemID = '.$rewardItemID.'. TotalItemPoints = '.$totalItemPoints.'. CardNumber = '.$cardNumber;
                                    $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, '', $apiDetails, '', 2);
                                    if($isInserted == 0) {
                                        $logMessage = "Failed to insert to APILogs.";
                                        $logger->log($logger->logdate, " [REDEEMITEMS ERROR] ", $logMessage);
                                    }
                                    exit;
                                }
                                
                            } else {
                                //check if the available item is greater than or equal to the quantity availed by the player.
                                $availableItemCount = $rewardItemsModel->getAvailableItemCount($rewardItemID);
                                
                                if($availableItemCount['AvailableItemCount'] >= $qty) {
                                    $availableSerialCode = $itemSerialCodesModel->getAvailableSerialCodeCount($rewardItemID, $qty);
                                    
                                    if(count($availableSerialCode) >= $qty) {
                                        //redemption process for item
                                        $offerEndDate = $rewardItemsModel->getOfferEndDate($rewardItemID);
                                        $redeemedDate = $offerEndDate['ItemCurrentDate'];
                                        $currentDate = $offerEndDate['CurrentDate'];
                                        
                                        //check if the avail date is greater than the end date of the reward offer
                                        if($redeemedDate <= $offerEndDate['OfferEndDate']) {
                                            $toBeCurrentPoints = (int)$currentPoints - (int)$totalItemPoints;
                                            if($toBeCurrentPoints < 0) {
                                                $transMsg = "Transaction Failed. Card has insufficient points.";
                                                $isLogged = $auditTrailModel->logEvent(AuditTrailModel::PLAYER_REDEMPTION, $message, array('MID'=>$MID, 'SessionID'=>$sessionID));
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
                                                $this->_sendResponse(200, CJSON::encode(CommonController::retMsg($module, $transMsg, $errorCode)));
                                                $logMessage = 'Transaction failed. Card has insufficient points.';
                                                $logger->log($logger->logdate, " [REDEEMITEMS ERROR] ", $logMessage);
                                                $apiDetails = 'REDEEMITEMS-Failed: Card has insufficient points. CurrentPoints = '.$currentPoints;
                                                $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, '', $apiDetails, '', 2);
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
                                                    $isLogged = $auditTrailModel->logEvent(AuditTrailModel::PLAYER_REDEMPTION, $message, array('MID'=>$MID, 'SessionID'=>$sessionID));
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
                                                    $this->_sendResponse(200, CJSON::encode(CommonController::retMsg($module, $transMsg, $errorCode)));
                                                    $logMessage = 'Transaction failed. Card has a pending redemption.';
                                                    $logger->log($logger->logdate, " [REDEEMITEMS ERROR] ", $logMessage);
                                                    $apiDetails = 'REDEEMITEMS-Failed: Card has a pending redemption. MID = '.$MID;
                                                    $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, '', $apiDetails, '', 2);
                                                    if($isInserted == 0) {
                                                        $logMessage = "Failed to insert to APILogs.";
                                                        $logger->log($logger->logdate, " [REDEEMITEMS ERROR] ", $logMessage);
                                                    }
                                                    exit;
                                                }
                                                else {
                                                    //process item redemption
                                                    $resultArray = Processing::processItemRedemption();
                                                }
                                            }
                                        }
                                    }
                                }
                                    
                                    
//                                                    $ctr = count(Yii::app()->Session['RewardOfferCopy']['SerialNumber']);
//                                                    $totalPoints = $totalItemPoints/$qty;
//                                                    for($itr = 0; $itr < $ctr; $itr++) {
//                                                        sendSMS(SMSRequestLogsModel::ITEM_REDEMPTION, $mobileNumber);
//                                                    }
//                                                    //get details for serial code and coupon series for this reward e-coupon
//                                                    $redemptionInfo = $raffleCouponsModel->getCouponRedemptionInfo($lastInsertedID);
//                                                    $minCouponNumber = str_pad($redemptionInfo['MinCouponNumber'], 7, "0", STR_PAD_LEFT);
//                                                    $maxCouponNumber = str_pad($redemptionInfo['MaxCouponNumber'], 7, "0", STR_PAD_LEFT);
                                                    
//                                                    //prepare coupon series for this reward e-coupon
//                                                    if($redemptionInfo['MinCouponNumber'] == $redemptionInfo['MaxCouponNumber'])
//                                                        $couponSeries = $minCouponNumber;
//                                                    else
//                                                        $couponSeries = $minCouponNumber . " - " . $maxCouponNumber;
                                                    
//                                                    //prepare serial code and security code for this reward e-coupon
//                                                    $serialCode = str_pad($lastInsertedID, 7, "0", STR_PAD_LEFT) . "A" . Utilities::getMod10($minCouponNumber) . "B" . Utilities::getMod10($maxCouponNumber);
//                                                    $securityCode = Utilities::mt_rand_str(8);
//                                                    
//                                                    $isStatusUpdated = $couponRedemptionLogsModel->updateLogsStatus($lastInsertedID, 1, $MID, $redeemPoints, $serialCode, $securityCode);
//                                                    if($isStatusUpdated > 0) {
//                                                        //if redemption is successful, build sessions for needed data and return an array of response.
//                                                        Yii::app()->session['PreviousRedemption'] = $lastInsertedID;
//                                                        Yii::app()->session['RewardOfferCopy']['CouponSeries'] = $couponSeries;
//                                                        Yii::app()->session['RewardOfferCopy']['Quantity'] = $quantity;
//                                                        Yii::app()->session['RewardOfferCopy']['RedemptionDate'] = $redeemedDate;
//                                                        Yii::app()->session['RewardOfferCopy']['CheckSum'] = $securityCode;
//                                                        Yii::app()->session['RewardOfferCopy']['SerialNumber'] = $serialCode;
//                                                        
//                                                        $transMsg = 'No Error, Transaction successful.';
//                                                        $errorCode = 0;
//                                                        $this->_sendResponse(200, CJSON::encode(CommonController::retMsg($module, $transMsg, $errorCode)));
//                                                    }
//                                                    else {
//                                                        $transMsg = "Pending Redemption. Error in updating redemption log.";
//                                                        $errorCode = 31;
//                                                        Utilities::log("ReturnMessage: " . $transMsg . " ErrorCode: " . $errorCode);
//                                                        $this->_sendResponse(200, CJSON::encode(CommonController::retMsg($module, $transMsg, $errorCode))); 
//                                                    }
                                                if(!null) {
                                                    
                                         
                                                }
                                                else {
                                                    $transMsg = "Transaction failed.";
                                                    $errorCode = 4;
                                                    Utilities::log("ReturnMessage: " . $transMsg . " ErrorCode: " . $errorCode);
                                                    $this->_sendResponse(200, CJSON::encode(CommonController::retMsg($module, $transMsg, $errorCode)));
                                                    $logMessage = 'Item Redemption failed.';
                                                    $logger->log($logger->logdate, " [REDEEMITEMS ERROR] ", $logMessage);
                                                    $apiDetails = 'REDEEMITEMS-Failed: Processing of Item Redemption failed. MID = '.$MID.'. RewardItemID = '.$rewardItemID.'. TotalItemPoints = '.$totalItemPoints.'. CardNumber = '.$cardNumber;
                                                    $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, '', $apiDetails, '', 2);
                                                    if($isInserted == 0) {
                                                        $logMessage = "Failed to insert to APILogs.";
                                                        $logger->log($logger->logdate, " [REDEEMITEMS ERROR] ", $logMessage);
                                                    }
                                                    exit;
                                                }
                                                $sessionID2 = Yii::app()->Session['SessionID'];
                                                $result2 = $auditTrailModel->logEvent(AuditTrailModel::PLAYER_REDEMPTION, $message, array('ID' => $MID, 'SessionID' => $sessionID2));
                                                if($result2 > 0) {
                                                    
                                                }
                                                else {
//                                                    $transMsg = "Failed to log event on Audit Trail.";
//                                                    $errorCode = 25;
//                                                    Utilities::log("ReturnMessage: " . $transMsg . " ErrorCode: " . $errorCode);
//                                                    $this->_sendResponse(200, CJSON::encode(CommonController::retMsg($module, $transMsg, $errorCode)));
                                                        $logMessage = "Failed to log event on Audit Trail.";
//                                                        $quantity = '';
//                                                        $totalItemPoints = '';
                                                        $logger->log($logger->logdate,"[ITEM REDEMPTION ERROR] ", $logMessage);
                                                }
                                                
                                                $qty = '';
                                                $totalItemPoints = '';
                                            }
                                        }
                                    }
                       
                    
                    $transMsg = 'No Error, Transaction successful.';
                    $errorCode = 0;
                    $this->_sendResponse(200, CJSON::encode(CommonController::retMsg($module, $transMsg, $errorCode)));
                    $logMessage = 'Redemption successful.';
                    $logger->log($logger->logdate, " [REDEEMITEMS SUCCESS] ", $logMessage);
                    $apiDetails = 'REDEEMITEMS-SUCCESS: Processing of Coupon/Item Redemption successful. MID = '.$MID.'. RewardItemID = '.$rewardItemID.'. TotalItemPoints = '.$totalItemPoints.'. CardNumber = '.$cardNumber;
                    $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, '', $apiDetails, '', 1);
                    if($isInserted == 0) {
                        $logMessage = "Failed to insert to APILogs.";
                        $logger->log($logger->logdate, " [REDEEMITEMS ERROR] ", $logMessage);
                    }
                    exit;
                }
                else {
                    $transMsg = "Not connected.";
                    $errorCode = 13;
                    Utilities::log("ReturnMessage: " . $transMsg . " ErrorCode: " . $errorCode);
                    $this->_sendResponse(200, CJSON::encode(CommonController::retMsg($module, $transMsg, $errorCode)));
                    $logMessage = 'Not connected.';
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
            $this->_sendResponse(200, CJSON::encode(CommonController::retMsg($module, $errorCode, $transMsg)));
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
        
        if(isset($request['CardNumber']) && isset($request['MPSessionID'])) {
            if($request['CardNumber'] == '' || $request['MPSessionID'] == '') {
                $transMsg = "One or more fields is not set or is blank.";
                $errorCode = 1;
                Utilities::log("ReturnMessage: " . $transMsg . " ErrorCode: " . $errorCode);
                $this->_sendResponse(200, CJSON::encode(CommonController::retMsgGetProfile($module, '', $errorCode, $transMsg)));
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
                
//                if(isset(Yii::app()->session['SessionID'])) {
//                    $sessionID = Yii::app()->session['SessionID'];
//                }
//                else {
//                    $sessionID = 0;
//                }
//                
                $memberSessions = $memberSessionsModel->getMID($mpSessionID);
                if($memberSessions)
                    $MID = $memberSessions['MID'];
                else {
                    $transMsg = "Not connected.";
                    $errorCode = 13;
                    Utilities::log("ReturnMessage: " . $transMsg . " ErrorCode: " . $errorCode);
                    $this->_sendResponse(200, CJSON::encode(CommonController::retMsgGetProfile($module, '', $errorCode, $transMsg)));
                    $logMessage = 'Not connected.';
                    $logger->log($logger->logdate, " [LISTITEMS ERROR] ", $logMessage);
                    $apiDetails = 'LISTITEMS-Failed: There is no active session. MID = '.$MID;
                    $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, '', $apiDetails, '', 2);
                    if($isInserted == 0) {
                        $logMessage = "Failed to insert to APILogs.";
                        $logger->log($logger->logdate, " [LISTITEMS ERROR] ", $logMessage);
                    }
                    exit; 
                }
//                
//                if(isset(Yii::app()->session['MID'])) {
//                    $MID = Yii::app()->session['MID'];
//                }
//                else {
//                    $MID = 0;
//                }
                             
                $isExist = $memberSessionsModel->checkIfSessionExist($MID, $mpSessionID);
                
                if($isExist > 0) {
                    if($MID == '' || $MID == null) {
                        $transMsg = "Account is Banned.";
                        $errorCode = 40;
                        Utilities::log("ReturnMessage: " . $transMsg . " ErrorCode: " . $errorCode);
                        $this->_sendResponse(200, CJSON::encode(CommonController::retMsgGetProfile($module, '', $errorCode, $transMsg)));
                        $logMessage = 'Account is banned.';
                        $logger->log($logger->logdate, " [GETPROFILE ERROR] ", $logMessage);
                        $apiDetails = 'GETPROFILE-Failed: Account is banned.';
                        $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, '', $apiDetails, '', 2);
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
                            $mobileNumber = $memberDetails['MobileNo'];
                            $alternateMobileNumber = $memberDetails['AlternateMobileNo'];
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
                            //Yii::app()->session['Email'] = $emailAddress;
                            
                            $result = $auditTrailModel->logEvent(AuditTrailModel::GET_PROFILE, 'GetProfile', array('MID' => $MID, 'SessionID' => $mpSessionID));
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
                                             'BonusPoints' => $bonusPoints, 'RedeemedPoints' => $redeemedPoints, 'LifetimePoints' => $lifetimePoints);
                            $transMsg = 'No Error, Transaction successful.';
                            $errorCode = 0;
                            $this->_sendResponse(200, CJSON::encode(CommonController::retMsgGetProfile($module, $profile, $errorCode, $transMsg)));
                            $logMessage = 'Get member profile is successful.';
                            $logger->log($logger->logdate, " [GETPROFILE SUCCESS] ", $logMessage);
                            $apiDetails = 'GETPROFILE-Success: Get member profile is successful.';
                            $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, '', $apiDetails, '', 1);
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
                            $this->_sendResponse(200, CJSON::encode(CommonController::retMsgGetProfile($module, '', $errorCode, $transMsg)));
                            $logMessage = 'Account is banned.';
                            $logger->log($logger->logdate, " [GETPROFILE ERROR] ", $logMessage);
                            $apiDetails = 'GETPROFILE-Failed: Account is banned.';
                            $isInserted = $apiLogsModel->insertAPIlogs($apiMethod, '', $apiDetails, '', 2);
                            if($isInserted == 0) {
                                $logMessage = "Failed to insert to APILogs.";
                                $logger->log($logger->logdate, " [GETPROFILE ERROR] ", $logMessage);
                            }
                            
                            session_destroy();
                            exit;
                        }
                    }
                }
                else {
                    $transMsg = "Not connected.";
                    $errorCode = 13;
                    Utilities::log("ReturnMessage: " . $transMsg . " ErrorCode: " . $errorCode);
                    $this->_sendResponse(200, CJSON::encode(CommonController::retMsgGetProfile($module, '', $errorCode, $transMsg)));
                    $logMessage = 'Not connected.';
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
            $this->_sendResponse(200, CJSON::encode(CommonController::retMsgGetProfile($module, '', $errorCode, $transMsg)));
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
    
    public function actionChangePassword() {
        $request = $this->_readJsonRequest();
        $transMsg = '';
        $errorCode = '';
        $module = 'ChangePassword';
        
        if(isset($request['Username']) && isset($request['Password']) && isset($request['ConfirmPassword'])) {
            if($request['Username'] == '' || $request['Password'] == '' || $request['ConfirmPassword'] == '' ) {
                $transMsg = "One or more fields is not set or is blank.";
                $errorCode = 1;
                Utilities::log("ReturnMessage: " . $transMsg . " ErrorCode: " . $errorCode);
                $this->_sendResponse(200, CJSON::encode(CommonController::retMsg($module, $transMsg, $errorCode)));
                exit;
            }
            else if($request['Password'] != $request['ConfirmPassword']) {
                $transMsg = "Password should be the same as confirm password.";
                $errorCode = 20;
                Utilities::log("ReturnMessage: " . $transMsg . " ErrorCode: " . $errorCode);
                $this->_sendResponse(200, CJSON::encode(CommonController::retMsg($module, $transMsg, $errorCode)));
                exit;
            }
            else {
                $hashedCardNumber = $_GET['CardNumber'];
                $cardNumber = base64_decode($hashedCardNumber);
                
                $membersModel = new MembersModel();
                $memberCardsModel = new MemberCardsModel();
                
                $isAllowed = $membersModel->getForChangePasswordUsingCardNumber($cardNumber);
                if($isAllowed)
                    $IsForChange = $isAllowed['ForChangePassword'];
                
                $username = trim($request['Username']);
                $password = trim($request['Password']);
                $confirmPassword = trim($request['ConfirmPassword']);
                
                if($IsForChange == 1) {
                    $result = $memberCardsModel->getMIDUsingCard($username);
                    if($result)
                        $MID = $result['MID'];
                    
                    $isUpdated = $membersModel->updatePasswordUsingMID($MID, $confirmPassword);
                    if($isUpdated == 1) {
                        $isSuccessful = $membersModel->updateForChangePasswordUsingMID($MID, 0);
                        if($isSuccessful == 1) {
                            $transMsg = "No error, Transaction successful.";
                            $errorCode = 0;
                            Utilities::log("ReturnMessage: " . $transMsg . " ErrorCode: " . $errorCode);
                            $this->_sendResponse(200, CJSON::encode(CommonController::retMsg($module, $transMsg, $errorCode)));
                            exit;
                        }
                        else
                            exit;
                    }
                    else
                        exit;
                    
                }
                else {
                    $transMsg = "Member account is not for change password.";
                    $errorCode = 54;
                    Utilities::log("ReturnMessage: " . $transMsg . " ErrorCode: " . $errorCode);
                    $this->_sendResponse(200, CJSON::encode(CommonController::retMsg($module, $transMsg, $errorCode)));
                    exit;
                }
                
            }
        }
        else {
            $transMsg = "One or more fields is not set or is blank.";
                $errorCode = 1;
                Utilities::log("ReturnMessage: " . $transMsg . " ErrorCode: " . $errorCode);
                $this->_sendResponse(200, CJSON::encode(CommonController::retMsg($module, $transMsg, $errorCode)));
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
////                            Yii::app()->session['SessionID'] = $mpSessionid;
////                            Yii::app()->session['MID'] = $MID;
////                            Yii::app()->session['UserName'] = $username;
////                            Yii::app()->session['CardTypeID'] = $cardTypeID;
////                            Yii::app()->session['DateEnded'] = $endDate;
////                            Yii::app()->session['Password'] = $password;
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
//                    if(isset(Yii::app()->session['MID'])) {
//                        $MID = Yii::app()->session['MID'];
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
////        if(isset(Yii::app()->session['UserName'])) {
////            $username = Yii::app()->session['UserName'];
////        }
////        else {
////            $username = '';
////        }
////        
////        if(isset(Yii::app()->session['Password'])) {
////            $password = Yii::app()->session['Password'];
////        }
////        else {
////            $password = '';
////        }
//               
//    }
}

?>
