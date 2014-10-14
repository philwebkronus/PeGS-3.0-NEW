<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of AmpapiController
 *
 * @author jdlachica
 * @date 07/21/2014
 */
class AmpapiController extends Controller {

    //URL for AMPAPI, and MPAPI
    public $urlMPAPI;
    public $urlAMPAPI;

    public function __construct() {
        $this->urlAMPAPI = Yii::app()->params['urlAMPAPI'];
        $this->urlMPAPI = Yii::app()->params['urlMPAPI'];
    }

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

    private $activeSession = false;//Boolean: for Session
    private $currentAID = null;
    public $redemptionType = 0;//1. Item or 2. Coupon



    //@Variable : This variable contains an ARRAY OF ERROR MESSAGES (Associative Array Type)
    private $errorMessage = array(
        '0'=>'No Error, Transaction successful.',
        '0.1'=>'Valid',
        '0.2'=>'One or more fields is not set or is blank.',
        '1'=>'One or more fields is not set or is blank.',
        '1.1'=>'Invalid.',
        '2'=>'Invalid input.',
        '2.1'=>'Session has expired. Please login again',
        '2.2'=>'Failed to delete session.',
        '3'=>'Member not found.',
        '3.1'=>'Third Party Account not found.',
        '4'=>'Transaction failed.',
        '5'=>'Invalid Email Address.',
        '6'=>'Invalid Card Number.',
        '7'=>'Membership Card is Inactive.',
        '8'=>'Membership Card is Deactivated.',
        '9'=>'Membership Card is Newly Migrated.',
        '10'=>'Membership Card is Temporarily Migrated.',
        '11'=>'Membership Card is Banned.',
        '12'=>'No Email Address found for this user. Please contact Philweb Customer Service Hotline 338-3388.',
        '13'=>'Not connected.',
        '14'=>'',
        '15'=>'Mobile number should not be less than 9 digits long.',
        '16'=>'Mobile number should consist of numbers only.',
        '17'=>'Name should consist of letters only.',
        '18'=>'Password and ID Number should consist of letters and numbers only.',
        '19'=>'Password should not be less than 5 characters long.',
        '20'=>'Password should be the same as confirm password.',
        '21'=>'Sorry, email already belongs to an existing account. Please enter another email address.',
        '70'=>'Session has expired. Please login again.',//60
        '71'=>'Invalid Session ID.',//71
        '72'=>'Error in updating DateCreated.',//72
        '73'=>'No response from Membership Portal API.',//73
        '74'=>'Update Error: Failed to generate TPSessionID.',//74
        '75'=>'One or more fields is not set or is blank.',
        '76'=>'Session has expired. Please login again.',
        '1648'=>'Invalid Session ID.',
        '1649'=>'No response from Membership Portal API.',
        '1650'=>'Session has expired. Please login again.'
    );

//    private $returnMessage = array(
//        '01'=>'Generated TPSessionID; Third Party session ID.',
//        '02'=>'Valid.',
//        '03'=>'No Error, Transaction successful.',
//        '04'=>'Card is inactive.'
//    );


    private $ApiMethodID = array(
        'Login'=>APILogsModel::API_LOGIN,
        'ForgotPassword'=>APILogsModel::API_FORGOT_PASSWORD,
        'RegisterMember'=>APILogsModel::API_REGISTER_MEMBER,
        'UpdateProfile'=>APILogsModel::API_UPDATE_PROFILE,
        'GetProfile'=>APILogsModel::API_GET_PROFILE,
        'CheckPoints'=>APILogsModel::API_CHECK_POINTS,
        'ListItems'=>APILogsModel::API_LIST_ITEMS,
        'RedeemItems'=>APILogsModel::API_REDEEM_ITEMS,
        'AuthenticateSession'=>APILogsModel::API_AUTHENTICATE_SESSION,
        'GetActiveSession'=>APILogsModel::API_GET_ACTIVE_SESSION,
        'GetGender'=>APILogsModel::API_GET_GENDER,
        'GetIDPresented'=>APILogsModel::API_GET_ID_PRESENTED,
        'GetNationality'=>APILogsModel::API_GET_NATIONALITY,
        'GetOccupation'=>APILogsModel::API_GET_OCCUPATION,
        'GetIsSmoker'=>APILogsModel::API_GET_IS_SMOKER,
        'GetReferrer'=>APILogsModel::API_GET_REFERRER,
        'GetRegion'=>APILogsModel::API_GET_REGION,
        'GetCity'=>APILogsModel::API_GET_CITY,
        'Logout'=>APILogsModel::API_LOGOUT
    );
    //@purpose AuthenticateSession
    public function actionIndex(){
        //echo "This is index page!";
    }

    public function actionAuthenticateSession(){
        $request = $this->_readJsonRequest();
        $module = 'AuthenticateSession';
        $authenticateSession = new AuthenticateSessionModel();
        $ValidateRequiredField = $this->validateRequiredFields($request, $module, array('Username'=>false, 'Password'=>false));
        if($ValidateRequiredField===true){
            $Username = trim($request['Username']);
            $Password = sha1(trim($request['Password']));
            $result = $authenticateSession->authenticateCredentials($Username, $Password);
            $count = $result['Count'];
            if($count==1 && $Username==$result['Username'] && $Password==$result['Password']){
                $this->_validateSessionID($result['AID'], $Username);//Executes '_validateSessionID' function
            }
            else{
                $result = $authenticateSession->authenticateUserNameCredentials($Username);
                if(isset($result) && $result['Count']>0){
                    $this->_displayReturnMessage(2, $module, 'Invalid Input');//Invalid Input
                    $this->_apiLogs(APILogsModel::API_AUTHENTICATE_SESSION,'' , 2, '', 2, $module, $Username);

                }else{
                    $this->_displayReturnMessage("3.1", $module, 'Third Party Account not found.');//Third Party Account not found
                    $this->_apiLogs(APILogsModel::API_AUTHENTICATE_SESSION,'' , '3.1', '', 2, $module, $Username);
                }

            }
        }
    }
        private function _validateSessionID($AID, $Details){
            $module = 'AuthenticateSession';
            $authenticateSession = new AuthenticateSessionModel();
            $result = $authenticateSession->authenticateSession($AID);
            $count = $result['Count'];
            $SessionID = $result['SessionID'];
            session_start();

            if($count==0 && $SessionID==null){
                ///$this->_displayCustomMessages(1, $module, $transMsg);
                $TPSessionID = session_id();//generated Session ID
                if($TPSessionID!=null){
                    $result = $authenticateSession->insertTPSessionID($AID, $TPSessionID);
                    if($result==1){
                        //$TPSID = $authenticateSession->getTPSessionID($AID);//TP Session ID
                        $apiLogsReferenceID = '';
                        $errorCode = 0;
                        $trackingID = '';
                        $status = 1;//1 is successful || 2 is failed.

                        $this->_displayMessage($errorCode, $module, $TPSessionID);
                        $this->_auditTrail(AuditTrailModel::AUTHENTICATE_SESSION,0,$AID, $TPSessionID, $module, $Details);
                        $this->_apiLogs(APILogsModel::API_AUTHENTICATE_SESSION, $apiLogsReferenceID, $errorCode, $trackingID, $status, $module, $Details);
                        $this->_logSuccess($module, 'TPSessionID generated Successfully.');

                    }
                    else if($result==0){
                        $this->_logError($module, 'Failed to generate TPSessionID.');
                        $this->_apiLogs(APILogsModel::API_AUTHENTICATE_SESSION,'' , 74, '', 2, $module, $Details);
                    }
                }
                else{
                    $this->_logError($module, 'Failed to generate TPSessionID.');
                    $this->_apiLogs(APILogsModel::API_AUTHENTICATE_SESSION,'' , 74, '', 2, $module, $Details);
                }
            }
            else if($count==1){
                $TPSessionID = session_id();//generated Session ID
                if($TPSessionID!=null){
                    $result = $authenticateSession->updateTPSessionID($AID, $TPSessionID);
                    if($result==1){
                        $apiLogsReferenceID = '';
                        $errorCode = 0;
                        $trackingID = '';
                        $status = 1;//1 is successful || 2 is failed.

                        $this->_displayMessage($errorCode, $module, $TPSessionID);
                        $this->_auditTrail(AuditTrailModel::AUTHENTICATE_SESSION,0, $AID, $TPSessionID, $module, $Details);
                        $this->_apiLogs(APILogsModel::API_AUTHENTICATE_SESSION, $apiLogsReferenceID, $errorCode, $trackingID, $status, $module, $Details);
                        $this->_logSuccess($module, 'TPSessionID Generate Successfully.');
                    }
                    else if($result==0){
                        $this->_displayCustomMessages(1, $module, "Update Error");
                        $this->_logError($module, 'Failed to generate TPSessionID.');
                        $this->_apiLogs(APILogsModel::API_AUTHENTICATE_SESSION,'' , 74, '', 2, $module, $Details);
                    }
                }
                else{
                    $this->_logError($module, 'Failed to generate TPSessionID.');
                    $this->_apiLogs(APILogsModel::API_AUTHENTICATE_SESSION,'' , 74, '', 2, $module, $Details);
                }
            }
            else{
                $this->_displayCustomMessages(4, $module, "Transaction Failed.");
                $this->_logError($module, 'Failed to generate TPSessionID.');
                $this->_apiLogs(APILogsModel::API_AUTHENTICATE_SESSION,'' , 4, '', 2, $module, $Details);
            }
        }

    public function actionGetActiveSession(){
        date_default_timezone_set('Asia/Manila');//setting to default timezone

        $request = $this->_readJsonRequest();
        $GetActiveSessionModle = new GetActiveSessionModel();
        $module = 'GetActiveSession';
        $activeSession = false;
        $validateRequiredFields = $this->validateRequiredFields($request, $module, array('TPSessionID'=>false,'Username'=>false));
        if($validateRequiredFields===true){
            $TPSessionID = trim($request['TPSessionID']);
            $Username = trim($request['Username']);

            $result = $GetActiveSessionModle->getActiveSession($TPSessionID, $Username);

            if(isset($result['Count']) && $result['Count']!=0){
                $resultTPSessionID = $result['SessionID'];
                $resultUsername = $result['Username'];
                $AID = $result['AID'];


                if($TPSessionID == $resultTPSessionID && $Username==$resultUsername){

                    $SessionDateTime = strtotime($result['DateCreated']);
                    $CurrentDateTime = strtotime(date('Y-m-d H:i:s'));
                    $TimeInterval = round(abs($CurrentDateTime-$SessionDateTime)/60,2);//echo $TimeInterval.'='.$CurrentDateTime.'-'.$SessionDateTime;exit;
                    $AID=$result['AID'];
                    $MaxTime = 30.00;

                    if($TimeInterval<$MaxTime){
                        $this->_displaySuccessMessage('0.1', $module, 'GetActiveSession Success.');

                        $this->_auditTrail(AuditTrailModel::GET_ACTIVE_SESSION,0,$AID, $TPSessionID, $module, $Username);
                        $this->_apiLogs(APILogsModel::API_GET_ACTIVE_SESSION,'' , 0, '', 1, $module, $Username);

                        $ValidateTPSession = new ValidateTPSessionIDModel();
                        $activeSession = true;

                        //Update Session's DateCreated
                        if(!(isset($request['SentFromAMPAPI']))){

                            $UpdateSessionDate = $ValidateTPSession->updateSessionDateCreated($TPSessionID, $AID);
                            if($UpdateSessionDate==0){
                                $this->_displayReturnMessage(72, $module, 'Error in Updating DateCreated.');
                                $this->_apiLogs(APILogsModel::API_GET_ACTIVE_SESSION,'' , 72, '', 2, $module, $Username);
                            }
                        }
                    }
                    else{
                        $ApiMethodID = $this->ApiMethodID;
                        $this->_displayReturnMessage(76,$module, $module.' contains expired SessionID.');
                        $this->_apiLogs($ApiMethodID[$module],'' , 76, '', 2, $module, $TPSessionID);
                    }

                }

                else{
                    $this->_displayReturnMessage('1.1', $module, 'Invalid Input.');
                    $this->_apiLogs(APILogsModel::API_GET_ACTIVE_SESSION,'' , '1.1', '', 2, $module, $Username);
                }
            }else{
                $validateTPSessionID = $this->_validateTPSession($TPSessionID, 'GetActiveSession', $module);
                if($validateTPSessionID===true){
                    $this->_displayReturnMessage('1.1', $module, 'Invalid Input.');
                    $this->_apiLogs(APILogsModel::API_GET_ACTIVE_SESSION,'' , '1.1', '', 2, $module, $Username);
                }
            }

        }

        if(isset($request['SentFromAMPAPI'])){
            if($request['SentFromAMPAPI']==1){
                $AID = $request['AID'];
                $ModuleNameAMPAPI = $request['ModuleNameAMPAPI'];
                $this->_updateSessionDate($TPSessionID, $AID, $ModuleNameAMPAPI);
                return $activeSession;
            }
        }
//
        //$this->activeSession=$activeSession;

    }

    public function actionLogin(){
        $request = $this->_readJsonRequest();
        $module = 'Login';
        //$authenticateSession = new AuthenticateSessionModel();

        $validateRequiredField = $this->validateRequiredFields($request, $module, array('TPSessionID'=>false, 'Username'=>false, 'Password'=>false));
        //$validateRequiredField=true;
        if($validateRequiredField===true){

            $validateTPSessionID = $this->_validateTPSession($request['TPSessionID'], 'GetActiveSession', $module);
            if($validateTPSessionID===true){
                $TPSessionID=$request['TPSessionID'];
                $AID = $this->currentAID;
                $moduleName ='login';
                $Username = trim($request['Username']);
                $Password = trim($request['Password']);
                $url = $this->genMPAPIURL($moduleName);
                $postData = CJSON::encode(array('Username'=>$Username, 'Password'=>$Password));
                $result = $this->SubmitData($url, $postData);

                if(isset($result[0]) && $result[0]==200){
                    $this->_sendResponse(200, $result[1]);
                    $this->_auditTrail(AuditTrailModel::LOGIN,0,$AID, $TPSessionID, $module, $Username);
                    $this->_apiLogs(APILogsModel::API_LOGIN,'' , 0, '', 1, $module, $Username);
                }
                else{
                    $this->_displayCustomMessages(73, $module, 'No response from Membership portal API.');//Error 73
                    $this->_apiLogs(APILogsModel::API_LOGIN,'' , 73, '', 2, $module, $Username);
                }
            }

        }

    }

    public function actionForgotPassword(){
        $request = $this->_readJsonRequest();
        $module = 'ForgotPassword';
        $ForgotPassword = new ForgotPasswordModel();

        $validateRequiredFields = $this->validateRequiredFields($request, $module, array('TPSessionID'=>false,'EmailCardNumber'=>false));
        if($validateRequiredFields===true){

            $TPSessionID = trim($request['TPSessionID']);

            $EmailCardNumber = trim($request['EmailCardNumber']);
            $validateTPSessionID = $this->_validateTPSession($TPSessionID, 'GetActiveSession', $module);
            if($validateTPSessionID===true){
                $moduleName ='forgotpassword';
                $url = $this->genMPAPIURL($moduleName);
                $postData = CJSON::encode(array('EmailCardNumber'=>$EmailCardNumber));
                $result = $this->SubmitData($url, $postData);
                $AID = $this->currentAID;

                if(isset($result[0]) && $result[0]==200){
                    $this->_sendResponse(200, $result[1]);
                    $this->_auditTrail(AuditTrailModel::FORGOT_PASSWORD,0,$AID, $TPSessionID, $module, $EmailCardNumber);
                    $this->_apiLogs(APILogsModel::API_FORGOT_PASSWORD,'' , 0, '', 1, $module, $EmailCardNumber);
                }
                else{
                    $this->_displayCustomMessages(73, $module, 'No response from Membership portal API.');//Error 73
                    $this->_apiLogs(APILogsModel::API_FORGOT_PASSWORD,'' , 73, '', 2, $module, $EmailCardNumber);
                }
            }
        }

    }

    public function actionRegisterMember(){
       $request = $this->_readJsonRequest();
       $module='RegisterMember';


       $fields = array('TPSessionID'=>false,'FirstName'=>false,'LastName'=>false,'Password'=>false,'PermanentAdd'=>false,'MobileNo'=>false,'EmailAddress'=>false,'IDPresented'=>false,'IDNumber'=>false,'Birthdate'=>false);

       $validateRequiredFields = $this->validateRequiredFields($request, $module, $fields);
       if($validateRequiredFields ===true){
           $this->_registerMember($request, $module);
       }

    }
        private function _registerMember($request, $module){
            $TPSessionID = trim($request['TPSessionID']);
            $validateTPSessionID = $this->_validateTPSession($TPSessionID, 'GetActiveSession', $module);
            if($validateTPSessionID===true){//print_r($request);
                $TPSessionID=trim($request['TPSessionID']);
                $FirstName=trim($request['FirstName']);
                $MiddleName=trim($request['MiddleName']);
                $LastName=trim($request['LastName']);
                $NickName=trim($request['NickName']);
                $Password=trim($request['Password']);
                $PermanentAdd=trim($request['PermanentAdd']);
                $MobileNo=trim($request['MobileNo']);
                $AlternateMobileNo=trim($request['AlternateMobileNo']);
                $EmailAddress=trim($request['EmailAddress']);
                $AlternateEmail=trim($request['AlternateEmail']);
                $Gender=trim($request['Gender']);
                $IDPresented=trim($request['IDPresented']);
                $IDNumber=trim($request['IDNumber']);
                $Nationality=trim($request['Nationality']);
                $Birthdate=trim($request['Birthdate']);
                $Occupation=trim($request['Occupation']);
                $IsSmoker=trim($request['IsSmoker']);
                $ReferralCode = trim($request['ReferralCode']);
                $ReferrerID = trim($request['ReferrerID']);
                $EmailSubscription = trim($request['EmailSubscription']);
                $SMSubscription = trim($request['SMSSubscription']);


                $moduleName =  strtolower($module);
                $url = $this->genMPAPIURL($moduleName);

                $postData = CJSON::encode(array(
                    'FirstName'=>$FirstName,
                    'MiddleName'=>$MiddleName,
                    'LastName'=>$LastName,
                    'NickName'=>$NickName,
                    'Password'=>$Password,
                    'PermanentAdd'=>$PermanentAdd,
                    'MobileNo'=>$MobileNo,
                    'AlternateMobileNo'=>$AlternateMobileNo,
                    'EmailAddress'=>$EmailAddress,
                    'AlternateEmail'=>$AlternateEmail,
                    'Gender'=>$Gender,
                    'IDPresented'=>$IDPresented,
                    'IDNumber'=>$IDNumber,
                    'Nationality'=>$Nationality,
                    'Birthdate'=>$Birthdate,
                    'Occupation'=>$Occupation,
                    'IsSmoker'=>$IsSmoker,
                    'ReferralCode'=>$ReferralCode,
                    'ReferrerID'=>$ReferrerID,
                    'EmailSubscription'=>$EmailSubscription,
                    'SMSSubscription'=>$SMSubscription
                ));
                $result = $this->SubmitData($url, $postData);
                $AID = $this->currentAID;
                if(isset($result[0]) && $result[0]==200){
                    $this->_sendResponse(200, $result[1]);$AID = $this->currentAID;

                    $ValidateResponse = $this->validateResponse($result[1], $module);
                    if($ValidateResponse==true){
                        $this->_auditTrail(AuditTrailModel::REGISTER_MEMBER,0,$AID, $TPSessionID, $module, $LastName.', '.$FirstName);
                        $this->_apiLogs(APILogsModel::API_REGISTER_MEMBER,'' , 0, '', 1, $module, $LastName.', '.$FirstName);
                    }

                }
                else{
                    $this->_displayCustomMessages(73, $module, 'No response from Membership portal API.');//Error 73
                    $this->_apiLogs(APILogsModel::API_REGISTER_MEMBER,'' , 73, '', 2, $module, $LastName.', '.$FirstName);
                }
            }
        }

    public function actionUpdateProfile(){
       $request = $this->_readJsonRequest();
       $module='UpdateProfile';
       //$UpdateProfile = new UpdateProfileModel();
       //isset($request['']) && $request['']!=null
       $fields = array('TPSessionID'=>false,'MPSessionID'=>false, 'FirstName'=>false,'LastName'=>false,'MobileNo'=>false,'EmailAddress'=>false,'Birthdate'=>false);

       $validateRequiredFields = $this->validateRequiredFields($request, $module, $fields);
       if($validateRequiredFields ===true){
           $validateMobileNoDataType = $this->validateContactNumberDataType($request, $module, array('MobileNo'=>false));
           if($validateMobileNoDataType===true){
               $validateMobileNoLength = $this->validateContactNumberLength($request, $module, array('MobileNo'=>false));
               if($validateMobileNoLength===true){
                   $validateAlternateMobile = $this->validateAlternateMobileDataType($request, $module, array('AlternateMobileNo'=>false));
                   if($validateAlternateMobile===true){
                       $validateAlternateMobile = $this->validateAlternateMobileLength($request, $module, array('AlternateMobileNo'=>false));
                       if($validateAlternateMobile===true){
                            $validateNames = $this->validateNames($request, $module, array('FirstName'=>false,'LastName'=>false));
                            if($validateNames===true){
                                $validateMiddleName = $this->validateNotRequiredNames($request, $module, array('MiddleName'=>false), 'MiddleName');
                                if($validateMiddleName===true){
                                    $validateNickName = $this->validateNotRequiredNames($request, $module, array('NickName'=>false), 'NickName');
                                    if($validateNickName===true){
                                        $validateEmail = $this->validateEmail($request, $module, array('EmailAddress'=>false));
                                        if($validateEmail===true){
                                            $validateEmail = $this->validateAlternateEmail($request, $module, array('AlternateEmail'=>false), 'AlternateEmail');
                                            if($validateEmail===true){
                                                $validatePassword = $this->validateAlphaNumeric($request, $module, array('Password'=>false), 'Password');
                                                if($validatePassword===true){
                                                    $validatePassword = $this->validatePasswordLength($request, $module, array('Password'=>false));
                                                    if($validatePassword===true){
                                                        $validateIDNumber = $this->validateAlphaNumeric($request, $module, array('IDNumber'=>false),'IDNumber');
                                                        if($validateIDNumber===true){
                                                            $this->_updateProfile($request, $module);
                                                        }
                                                    }
                                                }
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }

    }
        private function _updateProfile($request, $module){
            $TPSessionID = trim($request['TPSessionID']);

            $validateTPSessionID = $this->_validateTPSession($TPSessionID, '',$module);
            if($validateTPSessionID===true){
                $MPSessionID=$request['MPSessionID'];
                $FirstName=trim($request['FirstName']);
                $MiddleName=trim($request['MiddleName']);
                $LastName=trim($request['LastName']);
                $NickName=trim($request['NickName']);
                $Password=trim($request['Password']);
                $PermanentAdd=trim($request['PermanentAdd']);
                $MobileNo=trim($request['MobileNo']);
                $AlternateMobileNo=trim($request['AlternateMobileNo']);
                $EmailAddress=trim($request['EmailAddress']);
                $AlternateEmail=trim($request['AlternateEmail']);
                $Gender=trim($request['Gender']);
                $IDPresented=trim($request['IDPresented']);
                $IDNumber=trim($request['IDNumber']);
                $Nationality=trim($request['Nationality']);
                $Birthdate=trim($request['Birthdate']);
                $Occupation=trim($request['Occupation']);
                $IsSmoker=trim($request['IsSmoker']);
                $Region=trim($request['Region']);
                $City=trim($request['City']);

                $moduleName =  strtolower($module);
                $url = $this->genMPAPIURL($moduleName);
                $postData = CJSON::encode(array(
                    'MPSessionID'=>$MPSessionID,
                    'FirstName'=>$FirstName,
                    'MiddleName'=>$MiddleName,
                    'LastName'=>$LastName,
                    'NickName'=>$NickName,
                    'Password'=>$Password,
                    'PermanentAdd'=>$PermanentAdd,
                    'MobileNo'=>$MobileNo,
                    'AlternateMobileNo'=>$AlternateMobileNo,
                    'EmailAddress'=>$EmailAddress,
                    'AlternateEmail'=>$AlternateEmail,
                    'Gender'=>$Gender,
                    'IDPresented'=>$IDPresented,
                    'IDNumber'=>$IDNumber,
                    'Nationality'=>$Nationality,
                    'Occupation'=>$Occupation,
                    'IsSmoker'=>$IsSmoker,
                    'Birthdate'=>$Birthdate,
                    'Region'=>$Region,
                    'City'=>$City
                ));
                $result = $this->SubmitData($url, $postData);
                $AID = $this->currentAID;
                if(isset($result[0]) && $result[0]==200){
                    $this->_sendResponse(200, $result[1]);

                    $ValidateResponse = $this->validateResponse($result[1], $module);
                    if($ValidateResponse==true){
                        $this->_auditTrail(AuditTrailModel::UPDATE_PROFILE,0,$AID, $TPSessionID, $module, $LastName.', '.$FirstName);
                        $this->_apiLogs(APILogsModel::API_UPDATE_PROFILE,'' , 0, '', 1, $module, $LastName.', '.$FirstName);
                    }

                }
                else{
                    $this->_displayCustomMessages(73, $module, 'No response from Membership portal API.');//Error 73
                    $this->_apiLogs(APILogsModel::API_UPDATE_PROFILE,'' , 73, '', 2, $module, $LastName.', '.$FirstName);
                }
            }
        }
        private function validateAlternateMobileDataType($request, $module, $fields){
            $return = true;
            if($request['AlternateMobileNo']!=null){
                $validate = $this->validateContactNumberDataType($request, $module, $fields);
                if($validate==true){$return = true;}
                else{$return =false;}
            }
            return $return;
        }

        private function validateAlternateMobileLength($request, $module, $fields){
            $return = true;
            if($request['AlternateMobileNo']!=null){
                $validate = $this->validateContactNumberLength($request, $module, $fields);
                if($validate==true){$return = true;}
                else{$return =false;}
            }
            return $return;
        }

        private function validateNotRequiredNames($request, $module, $fields,$index){
            $return = true;
            if($request[$index]!=null){
                $validate = $this->validateNames($request, $module, $fields);
                if($validate==true){$return = true;}
                else{$return =false;}
            }
            return $return;
        }

    public function actionCheckPoints(){
        $request = $this->_readJsonRequest();//print_r($request);
        $module = 'CheckPoints';

        $validateRequiredField = $this->validateRequiredFields($request, $module, array('TPSessionID'=>false, 'CardNumber'=>false));
        if($validateRequiredField===true){
            $TPSessionID = $request['TPSessionID'];
            $validateTPSessionID = $this->_validateTPSession($TPSessionID, 'GetActiveSession', $module);
            if($validateTPSessionID===true){
                $CardNumber= trim($request['CardNumber']);
                $moduleName ='checkpoints';
                $url = $this->genMPAPIURL($moduleName);
                $postData = CJSON::encode(array('CardNumber'=>$CardNumber));
                $result = $this->SubmitData($url, $postData);
                $AID = $this->currentAID;
                if(isset($result[0]) && $result[0]==200){
                    $this->_sendResponse(200, $result[1]);

                    $ValidateResponse = $this->validateResponse($result[1], $module);
                    if($ValidateResponse==true){
                        $this->_auditTrail(AuditTrailModel::CHECK_POINTS,0,$AID, $TPSessionID, $module, $CardNumber);
                        $this->_apiLogs(APILogsModel::API_CHECK_POINTS,'' , 0, '', 1, $module);
                    }

                }
                else{
                    $this->_displayCustomMessages(73, $module, 'No response from Membership portal API.');//Error 73

                    $this->_apiLogs(APILogsModel::API_CHECK_POINTS,'' , 73, '', 2, $module);
                }
            }
        }
    }

    public function actionListItems(){
        $request = $this->_readJsonRequest();//print_r($request);
        $module = 'ListItems';

        $validateRequiredField = $this->validateRequiredFields($request, $module, array('TPSessionID'=>false, 'MPSessionID'=>false,'PlayerClassID'=>false));
        if($validateRequiredField===true){
            $TPSessionID = trim($request['TPSessionID']);
            $validateTPSessionID = $this->_validateTPSession($TPSessionID, 'GetActiveSession', $module);
            if($validateTPSessionID===true){
                $MPSessionID= trim($request['MPSessionID']);
                $PlayerClassID= trim($request['PlayerClassID']);
                $moduleName =  strtolower($module);
                $url = $this->genMPAPIURL($moduleName);
                $postData = CJSON::encode(array('MPSessionID'=>$MPSessionID,'PlayerClassID'=>$PlayerClassID));
                $result = $this->SubmitData($url, $postData);
                $AID = $this->currentAID;
                if(isset($result[0]) && $result[0]==200){
                    $this->_sendResponse(200, $result[1]);

                    $ValidateResponse = $this->validateResponse($result[1], $module);
                    if($ValidateResponse==true){
                        $this->_auditTrail(AuditTrailModel::LIST_ITEMS,0,$AID, $TPSessionID, $module, $PlayerClassID);
                        $this->_apiLogs(APILogsModel::API_LIST_ITEMS,'' , 0, '', 1, $module);
                    }

                }
                else{
                    $this->_displayCustomMessages(73, $module, 'No response from Membership portal API.');//Error 73
                    $this->_apiLogs(APILogsModel::API_LIST_ITEMS,'' , 73, '', 2, $module);
                }
            }
        }
    }

    public function actionRedeemItems(){
        $request = $this->_readJsonRequest();//print_r($request);
        $module = 'RedeemItems';

        $validateRequiredField = $this->validateRequiredFields($request, $module, array('TPSessionID'=>false, 'MPSessionID'=>false,'CardNumber'=>false,'RewardID'=>false, 'RewardItemID'=>false, 'Quantity'=>false, 'Source'=>false));
        if($validateRequiredField===true){
            $TPSessionID = trim($request['TPSessionID']);
            $validateTPSessionID = $this->_validateTPSession($TPSessionID, 'GetActiveSession', $module);
            if($validateTPSessionID===true){

                $MPSessionID= trim($request['MPSessionID']);
                $CardNumber= trim($request['CardNumber']);
                $RewardID = trim($request['RewardID']);
                $RewardItemID = trim($request['RewardItemID']);
                $Quantity =trim($request['Quantity']);
                $Source=trim($request['Source']);

                $moduleName =  strtolower($module);
                $url = $this->genMPAPIURL($moduleName);
                $postData = CJSON::encode(array('MPSessionID'=>$MPSessionID,'CardNumber'=>$CardNumber, 'RewardID'=>$RewardID, 'RewardItemID'=>$RewardItemID, 'Quantity'=>$Quantity,'Source'=>$Source));
                $result = $this->SubmitData($url, $postData);
                $AID = $this->currentAID;
                if(isset($result[0]) && $result[0]==200){
                    $this->_sendResponse(200, $result[1]);

                    $ValidateResponse = $this->validateResponse($result[1], $module);
                    if($ValidateResponse==true){
                        $this->_auditTrail(AuditTrailModel::REDEEM_ITEMS,0,$AID, $TPSessionID, $module, $CardNumber);
                        $this->_apiLogs(APILogsModel::API_REDEEM_ITEMS,'' , 0, '', 1, $module, $CardNumber);
                    }

                }
                else{
                    $this->_displayCustomMessages(73, $module, 'No response from Membership portal API.');//Error 73
                    $this->_apiLogs(APILogsModel::API_REDEEM_ITEMS,'' , 73, '', 2, $module, $CardNumber);
                }
            }
        }
        else{

        }
    }

    public function actionGetProfile(){
        $request = $this->_readJsonRequest();
        $module = 'GetProfile';

        $validateRequiredField = $this->validateRequiredFields($request, $module, array('TPSessionID'=>false, 'MPSessionID'=>false, 'CardNumber'=>false));
        if($validateRequiredField===true){
            $TPSessionID = trim($request['TPSessionID']);
            $validateTPSessionID = $this->_validateTPSession($TPSessionID, 'GetActiveSession', $module);
            if($validateTPSessionID===true){
                $moduleName ='getprofile';
                $MPSessionID = trim($request['MPSessionID']);
                $CardNumber = trim($request['CardNumber']);
                $url = $this->genMPAPIURL($moduleName);
                $postData = CJSON::encode(array('MPSessionID'=>$MPSessionID, 'CardNumber'=>$CardNumber));
                $result = $this->SubmitData($url, $postData);
                $AID = $this->currentAID;
                if(isset($result[0]) && $result[0]==200){
                    $this->_sendResponse(200, $result[1]);

                    $ValidateResponse = $this->validateResponse($result[1], $module);
                    if($ValidateResponse==true){
                        $this->_auditTrail(AuditTrailModel::GET_PROFILE,0,$AID, $TPSessionID, $module, $CardNumber);
                        $this->_apiLogs(APILogsModel::API_GET_PROFILE,'' , 0, '', 1, $module, $CardNumber);
                    }
                }
                else{
                    $this->_displayCustomMessages(73, $module, 'No response from Membership portal API.');//Error 73
                    $this->_apiLogs(APILogsModel::API_GET_PROFILE,'' , 73, '', 2, $module, $CardNumber);
                }
            }
        }
    }

    public function actionGetGender(){
        $request = $this->_readJsonRequest();
        $module = 'GetGender';

        $validateRequiredField = $this->validateRequiredFields2($request, $module, array('TPSessionID'=>false));
        if($validateRequiredField===true){
            $TPSessionID = trim($request['TPSessionID']);
            $validateTPSessionID = $this->_validateTPSession($TPSessionID, 'GetActiveSession', $module);
            if($validateTPSessionID===true){
                $moduleName ='getgender';
                $url = $this->genMPAPIURL($moduleName);
                $postData = CJSON::encode(array());
                $result = $this->SubmitData($url, $postData);

                if(isset($result[0]) && $result[0]==200){
                    $this->_sendResponse(200, $result[1]);
                }
                else{
                    $this->_displayCustomMessages(73, $module, 'No response from Membership portal API.');//Error 73
                }
            }
        }
    }

    public function actionGetIDPresented(){
        $request = $this->_readJsonRequest();
        $module = 'GetIDPresented';

        $validateRequiredField = $this->validateRequiredFields2($request, $module, array('TPSessionID'=>false));

        if($validateRequiredField===true){
            $TPSessionID = trim($request['TPSessionID']);
            $validateTPSessionID = $this->_validateTPSession($TPSessionID, 'GetActiveSession', $module);
            if($validateTPSessionID===true){
                $moduleName ='getidpresented';
                $url = $this->genMPAPIURL($moduleName);
                $postData = CJSON::encode(array());
                $result = $this->SubmitData($url, $postData);

                if(isset($result[0]) && $result[0]==200){
                    $this->_sendResponse(200, $result[1]);
                }
                else{
                    $this->_displayCustomMessages(73, $module, 'No response from Membership portal API.');//Error 73
                }
            }
        }
    }

    public function actionGetNationality(){
        $request = $this->_readJsonRequest();
        $module = 'GetNationality';

        $validateRequiredField = $this->validateRequiredFields2($request, $module, array('TPSessionID'=>false));

        if($validateRequiredField===true){
            $TPSessionID = trim($request['TPSessionID']);
            $validateTPSessionID = $this->_validateTPSession($TPSessionID, 'GetActiveSession', $module);
            if($validateTPSessionID===true){
                $moduleName ='getnationality';
                $url = $this->genMPAPIURL($moduleName);
                $postData = CJSON::encode(array());
                $result = $this->SubmitData($url, $postData);

                if(isset($result[0]) && $result[0]==200){
                    $this->_sendResponse(200, $result[1]);
                }
                else{
                    $this->_displayCustomMessages(73, $module, 'No response from Membership portal API.');//Error 73
                }
            }
        }
    }

    public function actionGetOccupation(){
       $request = $this->_readJsonRequest();
        $module = 'GetOccupation';

        $validateRequiredField = $this->validateRequiredFields2($request, $module, array('TPSessionID'=>false));

        if($validateRequiredField===true){
            $TPSessionID = trim($request['TPSessionID']);
            $validateTPSessionID = $this->_validateTPSession($TPSessionID, 'GetActiveSession', $module);
            if($validateTPSessionID===true){
                $moduleName ='getoccupation';
                $url = $this->genMPAPIURL($moduleName);
                $postData = CJSON::encode(array());
                $result = $this->SubmitData($url, $postData);

                if(isset($result[0]) && $result[0]==200){
                    $this->_sendResponse(200, $result[1]);
                }
                else{
                    $this->_displayCustomMessages(73, $module, 'No response from Membership portal API.');//Error 73
                }
            }
        }
    }

    public function actionGetIsSmoker(){
        $request = $this->_readJsonRequest();
        $module = 'GetIsSmoker';

        $validateRequiredField = $this->validateRequiredFields2($request, $module, array('TPSessionID'=>false));

        if($validateRequiredField===true){
            $TPSessionID = trim($request['TPSessionID']);
            $validateTPSessionID = $this->_validateTPSession($TPSessionID, 'GetActiveSession', $module);
            if($validateTPSessionID===true){
                $moduleName ='getissmoker';
                $url = $this->genMPAPIURL($moduleName);
                $postData = CJSON::encode(array());
                $result = $this->SubmitData($url, $postData);

                if(isset($result[0]) && $result[0]==200){
                    $this->_sendResponse(200, $result[1]);
                }
                else{
                    $this->_displayCustomMessages(73, $module, 'No response from Membership portal API.');//Error 73
                }
            }
        }
    }

    public function actionGetReferrer(){
        $request = $this->_readJsonRequest();
        $module = 'GetReferrer';

        $validateRequiredField = $this->validateRequiredFields2($request, $module, array('TPSessionID'=>false));

        if($validateRequiredField===true){
            $TPSessionID = trim($request['TPSessionID']);
            $validateTPSessionID = $this->_validateTPSession($TPSessionID, 'GetActiveSession', $module);
            if($validateTPSessionID===true){
                $moduleName =  strtolower($module);
                $url = $this->genMPAPIURL($moduleName);
                $postData = CJSON::encode(array());
                $result = $this->SubmitData($url, $postData);

                if(isset($result[0]) && $result[0]==200){
                    $this->_sendResponse(200, $result[1]);
                }
                else{
                    $this->_displayCustomMessages(73, $module, 'No response from Membership portal API.');//Error 73
                }
            }
        }
    }

    public function actionGetRegion(){
        $request = $this->_readJsonRequest();
        $module = 'GetRegion';

        $validateRequiredField = $this->validateRequiredFields2($request, $module, array('TPSessionID'=>false));

        if($validateRequiredField===true){
            $TPSessionID = trim($request['TPSessionID']);
            $validateTPSessionID = $this->_validateTPSession($TPSessionID, 'GetActiveSession', $module);
            if($validateTPSessionID===true){
                $moduleName =  strtolower($module);
                $url = $this->genMPAPIURL($moduleName);
                $postData = CJSON::encode(array());
                $result = $this->SubmitData($url, $postData);

                if(isset($result[0]) && $result[0]==200){
                    $this->_sendResponse(200, $result[1]);
                }
                else{
                    $this->_displayCustomMessages(73, $module, 'No response from Membership portal API.');//Error 73
                }
            }
        }
    }

    public function actionGetCity(){
        $request = $this->_readJsonRequest();
        $module = 'GetCity';

        $validateRequiredField = $this->validateRequiredFields2($request, $module, array('TPSessionID'=>false));

        if($validateRequiredField===true){
            $TPSessionID = trim($request['TPSessionID']);
            $validateTPSessionID = $this->_validateTPSession($TPSessionID, 'GetActiveSession', $module);
            if($validateTPSessionID===true){
                $moduleName =  strtolower($module);
                $url = $this->genMPAPIURL($moduleName);
                $postData = CJSON::encode(array());
                $result = $this->SubmitData($url, $postData);

                if(isset($result[0]) && $result[0]==200){
                    $this->_sendResponse(200, $result[1]);
                }
                else{
                    $this->_displayCustomMessages(1649, $module, 'No response from Membership portal API.');//Error 1649
                }
            }
        }
    }

    public function actionLogout(){
        $request = $this->_readJsonRequest();
        $module = 'Logout';

        $validateRequiredField = $this->validateRequiredFields2($request, $module, array('TPSessionID'=>false, 'MPSessionID'=>false));

        if($validateRequiredField===true){
            $TPSessionID = trim($request['TPSessionID']);
            $validateTPSessionID = $this->_validateTPSession($TPSessionID, 'GetActiveSession', $module);
            if($validateTPSessionID===true){
                $moduleName ='logout';
                $url = $this->genMPAPIURL($moduleName);
                $MPSessionID = trim($request['MPSessionID']);
                $postData = CJSON::encode(array('MPSessionID'=>$MPSessionID));
                $result = $this->SubmitData($url, $postData);


                if(isset($result[0]) && $result[0]==200){
                    $ValidateResponse = $this->validateResponse($result[1], $module);
                    if($ValidateResponse===true){
                        $AID = $this->currentAID;
                        $Logout = new LogoutModel();


                        $logoutResponse = $Logout->logout($AID, $TPSessionID);
                        if($logoutResponse==1){
                            $this->_sendResponse(200, $result[1]);
                            $this->_auditTrail(AuditTrailModel::LOGOUT,0,$AID, $TPSessionID, $module, $TPSessionID);
                            $this->_apiLogs(APILogsModel::API_LOGOUT,'' , 0, '', 1, $module, $TPSessionID);

                        }
                        else{
                            $this->_displayReturnMessage('2.2', $module, 'Failed to delete session.');//Error 73
                            $this->_apiLogs(APILogsModel::API_LOGOUT,'' , '2.2', '', 2, $module, $TPSessionID);
                        }
                    }else{
                        $this->_sendResponse(200, $result[1]);
                    }
                }
                else{
                    $this->_displayCustomMessages(73, $module, 'No response from Membership portal API.');//Error 73
                }
            }
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
    private function _displayMessage($errorCode, $module, $TPSessionID){
        if($module=='AuthenticateSession'){$transMsg=$TPSessionID;}
        else{$transMsg = $this->errorMessage[$errorCode];}

        $this->_sendResponse(200, CJSON::encode(CommonController::retMsg($module, $transMsg, $errorCode, '','',$TPSessionID)));
        Utilities::log("ReturnMessage: " . $transMsg. " ErrorCode: " . $errorCode);
    }
    private function _displaySuccesfulMessage($returnCode, $module){
        $transMsg = $this->returnMessage[$returnCode];
        $this->_sendResponse(200, CJSON::encode(CommonController::retMsg($module, $transMsg, $returnCode)));
    }

    //This function invokes necessary method in displaying error messages based on '$errorMessage' php variable declared in this class.
    private function _displayReturnMessage($errorCode, $module, $logErrorMessage, $ApiLogsModel='', $RewardID=''){
        $transMsg = $this->errorMessage[$errorCode];

        $eCode = floor($errorCode);
        $this->_sendResponse(200, CJSON::encode(CommonController::retMsg($module, $transMsg, $eCode,'','','','','','','','','', $RewardID)));
        Utilities::log("ReturnMessage: " . $transMsg. " ErrorCode: " . $errorCode);
        $this->_logError($module, $logErrorMessage);
        //$this->_apiLogs($ApiLogsModel, $apiLogsReferenceID, $errorCode, $trackingID, $status, $module);
    }

    private function _displaySuccessMessage($errorCode, $module, $logErrorMessage){
        $transMsg = $this->errorMessage[$errorCode];

        $eCode = floor($errorCode);
        $this->_sendResponse(200, CJSON::encode(CommonController::retMsg($module, $transMsg, $eCode)));
        Utilities::log("ReturnMessage: " . $transMsg. " ErrorCode: " . $errorCode);
        $this->_logSuccess($module, $logErrorMessage);
    }

    //This function invokes necessary method in displaying custom error message.
    private function _displayCustomMessages($errorCode, $module, $errorMessages){
        $transMsg = $errorMessages;
//        strlen($errorCode)>2?$eCode = substr($errorCode, 0, 1):$eCode = substr($errorCode, 0, strlen($errorCode));
        $eCode = floor($errorCode);
        $this->_sendResponse(200, CJSON::encode(CommonController::retMsg($module, $transMsg, $eCode)));
        Utilities::log("ReturnMessage: " . $transMsg. " ErrorCode: " . $eCode);
        $this->_logError($module, $errorMessages);
    }

    private function _utilityLogs($transMsg, $errorCode){
        return "ReturnMessage: " . $transMsg . " ErrorCode: " . $errorCode;
    }
    //This function creat audittrail logs
    private function _auditTrail($auditFunction,$errorCode, $AID, $sessionID, $module, $details=''){
        $auditTrailModel = new AuditTrailModel();
        $logger = new ErrorLogger();

        $transMsg = $module.': '.$details;
        $result = $auditTrailModel->logEvent($auditFunction, $transMsg, array('AID' => $AID, 'SessionID' => $sessionID));
        //@Ternary function or conditional statement
        $result==1?$this->_logSuccess($module, "Audittrail log success."):$this->_logError($module, 'Failed to log event on Audittrail.');

    }
    //This function inserts logs in apilogs table
    private function _apiLogs($apiMethodID, $refID, $errorCode, $trackingID, $status, $module, $details=''){
        $apiLogsModel = new APILogsModel();
        $transMsg = $module.': '.$details.'|| '.$this->errorMessage[$errorCode];
        $isInserted = $apiLogsModel->insertAPIlogs($apiMethodID, $refID, $transMsg, $trackingID, $status);
        //@Ternary function or conditional statement
        $isInserted ==1?$this->_logSuccess($module, 'APIlogs success.'):$this->_logError($module, 'Failed to insert to APILogs.');
    }

    //This function creates logs for failed transaction
    private function _logError($module, $logMessage){
        $logger = new ErrorLogger();
        $logger->log($logger->logdate, " [".strtoupper($module)." ERROR] ", $logMessage);
    }
    //This function creates logs for success transactions
    private function _logSuccess($module, $logMessage){
        $logger = new ErrorLogger();
        $logger->log($logger->logdate, " [".strtoupper($module)." SUCCESS] ", $logMessage);
        //$this->_apiLogs('', $refID, $errorCode, $trackingID, $status, $module)
    }


    //validation functions

    private function validateRequiredFields($request, $module, $fields){
        $validateSuccess = false;
        foreach($fields as $key=>$value){
            if(isset($request[$key]) && $request[$key]!=null){
                $fields[$key]=true;
            }
            else{
                $module=='GetActiveSession'?$ErrorCode=75:$ErrorCode=1;
                $this->_displayReturnMessage($ErrorCode, $module, $key.' is not set or is blank.');
                $ApiMethodID = $this->ApiMethodID;
                $this->_apiLogs($ApiMethodID[$module],'' , $ErrorCode, '', 2, $module, $key);
                return false;
            }
        }
        $validateSuccess = $this->validateAllFields($fields);
        return $validateSuccess;
    }


    private function validateRequiredFields2($request, $module, $fields){
        $validateSuccess = false;
        foreach($fields as $key=>$value){
            if(isset($request[$key]) && $request[$key]!=null){
                $fields[$key]=true;
            }
            else{
                $eCode='0.2';
                $this->_displayReturnMessage($eCode, $module, $key.' is not set or is blank.');//
                $ApiMethodID = $this->ApiMethodID;
                $this->_apiLogs($ApiMethodID[$module],'' , $eCode, '', 2, $module, $key);
                return false;
            }
        }
        $validateSuccess = $this->validateAllFields($fields);
        return $validateSuccess;
    }


    //It validates contact number
    private function validateContactNumberLength($request, $module, $fields){

        foreach($fields as $key=>$value){

            if(!(strlen($request[$key])<9)){
                $fields[$key]=true;
            }
            else{
                $this->_displayReturnMessage(15, $module, $key.' should not be less than 9 digits long.');
                return false;
            }
        }
        $validateSuccess = $this->validateAllFields($fields);
        return $validateSuccess;

    }

    //It validates field if it is number
    private function validateContactNumberDataType($request, $module, $fields){
        foreach($fields as $key=>$value){
            if((is_numeric($request[$key]))){
                $fields[$key]=true;
            }
            else{
                $this->_displayReturnMessage(16, $module, $key.' should consist of numbers only.');
                return false;
            }
        }
        $validateSuccess = $this->validateAllFields($fields);
        return $validateSuccess;
    }

    private function validateNames($request, $module, $fields){
        foreach($fields as $key=>$value){
            if((ctype_alpha($request[$key]))){
                $fields[$key]=true;
            }
            else{
                $this->_displayReturnMessage(17, $module, $key.' should consist of letters only.');
                return false;
            }
        }

        $validateSuccess = $this->validateAllFields($fields);
        return $validateSuccess;
    }

    private function validateEmail($request, $module, $fields){
        foreach($fields as $key=>$value){
            if(Utilities::validateEmail($request[$key])==true){
                $fields[$key]=true;
            }
            else{
                $this->_displayReturnMessage(5, $module, $key.' contains invalid email.');
                return false;
            }
        }

        $validateSuccess = $this->validateAllFields($fields);
        return $validateSuccess;
    }
        private function validateAlternateEmail($request, $module, $fields, $key){
            $return = true;
            if($request[$key]!=null){
                $validate = $this->validateEmail($request, $module, $fields);
                if($validate==true){$return = true;}
                else{$return =false;}
            }
            return $return;
        }
    private function validateAlphaNumeric($request, $module, $fields, $index){
        $validateSuccess=true;

        if($request[$index]!=null){
        foreach($fields as $key=>$value){
            if(Utilities::validateAlphaNumeric($request[$key])==true){
                $fields[$key]=true;
            }
            else{
                $this->_displayReturnMessage(18, $module, $key.' should consist of letters and numbers only.');
                return false;
            }
        }

        $validateSuccess = $this->validateAllFields($fields);
        }
        return $validateSuccess;
    }
    private function validatePasswordLength($request, $module, $fields){
        $validateSuccess=true;
        if($request['Password']!=null){
        foreach($fields as $key=>$value){
            if(!(strlen($request['Password'])<5)){
                $fields[$key]=true;
            }
            else{
                $this->_displayReturnMessage(19, $module, $key.' should not be less than 5 characters long.');
                return false;
            }
        }
        $validateSuccess = $this->validateAllFields($fields);
        }
        return $validateSuccess;
    }



   private function validateAllFields($fields){
       $validateSuccess = false;
       foreach($fields as $value){
            if($value==true){
                $validateSuccess=true;
                return true;
            }
            else{
                $validateSuccess=false;
                return false;
            }
        }
        return $validateSuccess;
   }

   private function validateResponse($result, $moduleName){
        $valid = false;
        $parse = CJSON::decode($result);
        $ErrorCode = $parse[$moduleName]['ErrorCode'];
        if($ErrorCode==0){
            $valid = true;
        }else{
            $valid=false;
        }
        return $valid;
    }

   private function _validateTPSession($TPSessionID, $moduleNameMPAPI='GetActiveSession', $moduleNameAMPAPI = ''){
        date_default_timezone_set('Asia/Manila');//setting to default timezone

        $ValidateTPSession = new ValidateTPSessionIDModel();
        $queryResult = $ValidateTPSession->validateTPSessionID(trim($TPSessionID));

        $count = $queryResult['Count'];
        $valid = false;
        $ApiMethodID = $this->ApiMethodID;
        if(isset($count) && $count==1){
            $SessionDateTime = strtotime($queryResult['DateCreated']);
            $CurrentDateTime = strtotime(date('Y-m-d H:i:s'));
            $TimeInterval = round(abs($CurrentDateTime-$SessionDateTime)/60,2);//echo $TimeInterval.'='.$CurrentDateTime.'-'.$SessionDateTime;exit;
            $AID=$queryResult['AID'];
            $MaxTime = 30.00;

            if($TimeInterval<$MaxTime){
                $TPUsername = $queryResult['UserName'];
                $url = $this->genAMPAPIURL($moduleNameMPAPI);
                $postData = CJSON::encode(array('TPSessionID'=>$TPSessionID, 'Username'=>$TPUsername, 'SentFromAMPAPI'=>1,'ModuleNameAMPAPI'=>$moduleNameAMPAPI, 'AID'=>$AID));
                $result = $this->SubmitData($url, $postData);
                $this->currentAID = $AID;
                if(isset($result)){
                //$ActiveSession = $this->activeSession;
                //if($ActiveSession===true){
                    $parse = CJSON::decode($result[1]);
                    $ErrorCode = $parse[$moduleNameMPAPI]['ErrorCode'];
                    //$DateCreated = $parse[$moduleName]['DateCreated'];
                    if($ErrorCode==0){
                        $valid = true;
                    }else{
                        $valid=false;
                    }
                }

            }else{
                $valid=false;
                $moduleNameAMPAPI=='GetCity'?$eCode=1650:$eCode=76;
                $this->_displayReturnMessage($eCode, $moduleNameAMPAPI, $moduleNameAMPAPI.' contains expired SessionID.');
                $this->_apiLogs($ApiMethodID[$moduleNameAMPAPI],'' , $eCode, '', 2, $moduleNameAMPAPI, $TPSessionID);

            }

        }else{
            $valid=false;
            $moduleNameAMPAPI=='GetCity'?$eCode=1648:$eCode=71;
            $this->_displayReturnMessage($eCode, $moduleNameAMPAPI, $moduleNameAMPAPI.' has invalid Session ID.');//ErrorMesssage:One or more fields is not set or is blank, ErrorCode:1
            $this->_apiLogs($ApiMethodID[$moduleNameAMPAPI],'' , $eCode, '', 2, $moduleNameAMPAPI, $TPSessionID);
        }
        return $valid;
    }
        private function _updateSessionDate($TPSessionID, $AID, $moduleNameAMPAPI){
            $ValidateTPSession = new ValidateTPSessionIDModel();
            $UpdateSession = $ValidateTPSession->updateSessionDateCreated($TPSessionID, $AID);
            if($UpdateSession==0){
                $this->_displayReturnMessage(72, $moduleNameAMPAPI, 'Error in Updating DateCreated.');
            }
        }


   private function SubmitData( $uri, $postdata)
    {
            $curl = curl_init( $uri );

            curl_setopt( $curl, CURLOPT_FRESH_CONNECT, $this->_caching );
            curl_setopt( $curl, CURLOPT_CONNECTTIMEOUT, $this->_connectionTimeout );
            curl_setopt( $curl, CURLOPT_TIMEOUT, $this->_timeout );
            curl_setopt( $curl, CURLOPT_USERAGENT, $this->_userAgent );
            curl_setopt( $curl, CURLOPT_SSL_VERIFYHOST, FALSE );
            curl_setopt( $curl, CURLOPT_SSL_VERIFYPEER, FALSE );
            curl_setopt( $curl, CURLOPT_POST, TRUE );
            curl_setopt( $curl, CURLOPT_HTTPHEADER, array( 'Content-Type: application/json' ) );
            curl_setopt( $curl, CURLOPT_RETURNTRANSFER, TRUE );
            // Data+Files to be posted
            curl_setopt($curl, CURLOPT_POSTFIELDS, $postdata);
            $response = curl_exec( $curl );

            $http_status = curl_getinfo( $curl, CURLINFO_HTTP_CODE );

            curl_close( $curl );


            return array( $http_status, $response );
    }

    //This functions generates URL String for the use of certain method.
    private function genMPAPIURL($moduleName = null){
        return $this->urlMPAPI.$moduleName;
    }

    private function genAMPAPIURL($moduleName = null){
        return $this->urlAMPAPI.$moduleName;
    }

}

