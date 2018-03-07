<?php

/**
 * Description of MPapiInvokerController
 *
 * @author fdlsison
 * @date 06-20-2014
 */
class MPapiInvokerController extends Controller {

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

    public function actionOverview() {
        $this->pageTitle = 'Membership Portal API - Overview';
        $this->render('overview');
    }

    public function actionLogin() {
        $this->pageTitle = 'Membership Portal API - Login';
        $result = '';

        if (isset($_POST['Username']) || isset($_POST['Password'])) {
            $username = $_POST['Username'];
            $password = $_POST['Password'];

            $result = $this->_login($username, $password);
        }


        $this->render('login', array('result' => $result));
    }

    public function actionChangePassword() {
        $this->pageTitle = 'Membership Portal API - Change Password';
        $result = '';

        if (isset($_POST['CardNumber']) || isset($_POST['NewPassword'])) {
            $cardNumber = $_POST['CardNumber'];
            $newPassword = $_POST['NewPassword'];

            $result = $this->_changePassword($cardNumber, $newPassword);
        }


        $this->render('changepassword', array('result' => $result));
    }

    public function actionForgotPassword() {
        $this->pageTitle = 'Membership Portal API - Forgot Password';
        $result = '';

        if (isset($_POST['EmailCardNumber'])) {
            $emailCardNumber = $_POST['EmailCardNumber'];

            $result = $this->_forgotPassword($emailCardNumber);
        }

        $this->render('forgotpassword', array('result' => $result));
    }

    public function actionRegisterMember() {
        $this->pageTitle = 'Membership Portal API - Register Member';
        $result = '';

        if (isset($_POST['FirstName']) || isset($_POST['LastName']) || isset($_POST['MobileNo']) || isset($_POST['EmailAddress']) || isset($_POST['IDNumber']) || isset($_POST['Birthdate']) || isset($_POST['Password']) || isset($_POST['IDPresented']) || isset($_POST['PermanentAdd'])) {
            $firstname = $_POST['FirstName'];
            $middlename = $_POST['MiddleName'];
            $nickname = $_POST['NickName'];
            $lastname = $_POST['LastName'];
            $mobileNumber = $_POST['MobileNo'];
            $alternateMobileNumber = $_POST['AlternateMobileNo'];
            $emailAddress = $_POST['EmailAddress'];
            $alternateEmail = $_POST['AlternateEmail'];
            $gender = $_POST['Gender'];
            $idNumber = $_POST['IDNumber'];
            $birthdate = $_POST['Birthdate'];
            $password = $_POST['Password'];
            $idPresented = $_POST['IDPresented'];
            $permanentAddress = $_POST['PermanentAdd'];
            $nationality = $_POST['Nationality'];
            $occupation = $_POST['Occupation'];
            $isSmoker = $_POST['IsSmoker'];
            $referrerID = $_POST['ReferrerID'];
            $referralCode = $_POST['ReferralCode'];
            $emailSubscription = $_POST['EmailSubscription'];
            $smsSubscription = $_POST['SMSSubscription'];



            $result = $this->_registerMember($firstname, $middlename, $lastname, $nickname, $mobileNumber, $alternateMobileNumber, $emailAddress, $alternateEmail, $gender, $idNumber, $birthdate, $password, $idPresented, $permanentAddress, $nationality, $occupation, $isSmoker, $referrerID, $referralCode, $emailSubscription, $smsSubscription);
        }

        $this->render('registermember', array('result' => $result));
    }

    public function actionAutoRegisterMember() {
        $this->pageTitle = 'Membership Portal API - Auto Register Member';
        $result = '';

        if (isset($_POST['FirstName']) || isset($_POST['LastName']) || isset($_POST['MobileNo']) || isset($_POST['EmailAddress']) || isset($_POST['IDNumber']) || isset($_POST['Birthdate']) || isset($_POST['Password']) || isset($_POST['IDPresented']) || isset($_POST['PermanentAdd'])) {
            $firstname = $_POST['FirstName'];
            $middlename = $_POST['MiddleName'];
            $nickname = $_POST['NickName'];
            $lastname = $_POST['LastName'];
            $mobileNumber = $_POST['MobileNo'];
            $alternateMobileNumber = $_POST['AlternateMobileNo'];
            $emailAddress = $_POST['EmailAddress'];
            $alternateEmail = $_POST['AlternateEmail'];
            $gender = $_POST['Gender'];
            $idNumber = $_POST['IDNumber'];
            $birthdate = $_POST['Birthdate'];
            $password = $_POST['Password'];
            $idPresented = $_POST['IDPresented'];
            $permanentAddress = $_POST['PermanentAdd'];
            $nationality = $_POST['Nationality'];
            $occupation = $_POST['Occupation'];
            $isSmoker = $_POST['IsSmoker'];
            $referrerID = $_POST['ReferrerID'];
            $referralCode = $_POST['ReferralCode'];
            $emailSubscription = $_POST['EmailSubscription'];
            $smsSubscription = $_POST['SMSSubscription'];
            $CivilStatus = $_POST['CivilStatus'];
            $RegisterFor = $_POST['RegisterFor'];
            $UBCard = $_POST['UBCard'];
            $AID = $_POST['AID'];
            $SiteID = $_POST['SiteID'];


            $result = $this->_autoregisterMember($firstname, $middlename, $lastname, $nickname, $mobileNumber, $alternateMobileNumber, $emailAddress, $alternateEmail, $gender, $idNumber, $birthdate, $password, $idPresented, $permanentAddress, $nationality, $occupation, $isSmoker, $referrerID, $referralCode, $emailSubscription, $smsSubscription, $CivilStatus, $RegisterFor, $UBCard, $AID, $SiteID);
        }

        $this->render('autoregistermember', array('result' => $result));
    }

    public function actionUpdateProfile() {
        $this->pageTitle = 'Membership Portal API - Update Profile';
        $result = '';

        if (//isset($_POST['FirstName']) || isset($_POST['LastName']) ||
                isset($_POST['MobileNo']) || isset($_POST['EmailAddress']) || isset($_POST['IDNumber']) || isset($_POST['Birthdate']) || isset($_POST['MPSessionID']) || isset($_POST['Region']) || isset($_POST['City'])) {
            $mpSessionID = $_POST['MPSessionID'];
            //$firstname = $_POST['FirstName'];
            //$middlename = $_POST['MiddleName'];
            //$nickname = $_POST['NickName'];
            //$lastname = $_POST['LastName'];
            $mobileNumber = $_POST['MobileNo'];
            $alternateMobileNumber = $_POST['AlternateMobileNo'];
            $emailAddress = $_POST['EmailAddress'];
            $alternateEmail = $_POST['AlternateEmail'];
            $gender = $_POST['Gender'];
            $idNumber = $_POST['IDNumber'];
            $birthdate = $_POST['Birthdate'];
            $password = $_POST['Password'];
            $idPresented = $_POST['IDPresented'];
            $permanentAddress = $_POST['PermanentAdd'];
            $nationality = $_POST['Nationality'];
            $occupation = $_POST['Occupation'];
            $isSmoker = $_POST['IsSmoker'];
            $region = $_POST['Region'];
            $city = $_POST['City'];

            $result = $this->_updateProfile($mpSessionID, $mobileNumber, $alternateMobileNumber, $emailAddress, $alternateEmail, $gender, $idNumber, $birthdate, $password, $idPresented, $permanentAddress, $nationality, $occupation, $isSmoker, $region, $city);
        }

        $this->render('updateprofile', array('result' => $result));
    }

    public function actionCheckPoints() {
        $this->pageTitle = 'Membership Portal API - Check Points';
        $result = '';

        if (isset($_POST['CardNumber'])) {
            $cardNumber = $_POST['CardNumber'];
            $config = Yii::app()->params['config'];

            $result = $this->_checkPoints($cardNumber, $config);
        }

        $this->render('checkpoints', array('result' => $result));
    }

    public function actionListItems() {
        $this->pageTitle = 'Membership Portal API - List Items';
        $result = '';

        if (isset($_POST['PlayerClassID']) || isset($_POST['MPSessionID'])) {
            $playerClassID = $_POST['PlayerClassID'];
            $mpSessionID = $_POST['MPSessionID'];

            $result = $this->_listItems($playerClassID, $mpSessionID);
        }

        $this->render('listitems', array('result' => $result));
    }

    public function actionRedeemItems() {
        $this->pageTitle = 'Membership Portal API - Redeem Items';
        $result = '';

        if (isset($_POST['MPSessionID']) || isset($_POST['CardNumber']) || isset($_POST['RewardItemID']) || isset($_POST['RewardID']) || isset($_POST['Quantity']) || isset($_POST['Source'])) {
            $mpSessionID = $_POST['MPSessionID'];
            $cardNumber = $_POST['CardNumber'];
            $rewardItemID = $_POST['RewardItemID'];
            $rewardID = $_POST['RewardID'];
            $quantity = $_POST['Quantity'];
            //$itemQuantity = $_POST['ItemQuantity'];
            $source = $_POST['Source'];
            $config = Yii::app()->params['config'];

            $result = $this->_redeemItems($mpSessionID, $cardNumber, $rewardID, $rewardItemID, $quantity, $source, $config);
        }

        $this->render('redeemitems', array('result' => $result));
    }

    public function actionGetProfile() {
        $this->pageTitle = 'Membership Portal API - Get Profile';
        $result = '';

        if (isset($_POST['CardNumber']) || isset($_POST['MPSessionID'])) {
            $cardNumber = $_POST['CardNumber'];
            $mpSessionID = $_POST['MPSessionID'];
            $config = Yii::app()->params['config'];

            $result = $this->_getProfile($cardNumber, $mpSessionID, $config);
        }

        $this->render('getprofile', array('result' => $result));
    }

    public function actionGetGender() {
        $this->pageTitle = 'Membership Portal API - Get Gender';
        $result = '';

        if (isset($_POST['yt0']))
            $result = $this->_getGender();

        $this->render('getgender', array('result' => $result));
    }

    public function actionGetIDPresented() {
        $this->pageTitle = 'Membership Portal API - Get ID Presented';
        $result = '';

        if (isset($_POST['yt0']))
            $result = $this->_getIDPresented();

        $this->render('getidpresented', array('result' => $result));
    }

    public function actionGetNationality() {
        $this->pageTitle = 'Membership Portal API - Get Nationality';
        $result = '';

        if (isset($_POST['yt0']))
            $result = $this->_getNationality();

        $this->render('getnationality', array('result' => $result));
    }

    public function actionGetOccupation() {
        $this->pageTitle = 'Membership Portal API - Get Occupation';
        $result = '';

        if (isset($_POST['yt0']))
            $result = $this->_getOccupation();

        $this->render('getoccupation', array('result' => $result));
    }

    public function actionGetIsSmoker() {
        $this->pageTitle = 'Membership Portal API - Get IsSmoker';
        $result = '';

        if (isset($_POST['yt0']))
            $result = $this->_getIsSmoker();

        $this->render('getissmoker', array('result' => $result));
    }

    public function actionDeleteSession() {
        $this->pageTitle = 'Membership Portal API - Delete Session';
        $result = '';

        $result = $this->_deleteSession();

        $this->render('deletesession', array('result' => $result));
    }

    public function actionLogout() {
        $this->pageTitle = 'Membership Portal API - Logout';
        $result = '';

        if (isset($_POST['MPSessionID'])) {
            $mpSessionID = $_POST['MPSessionID'];

            $result = $this->_logout($mpSessionID);
        }

        $this->render('logout', array('result' => $result));
    }

    public function actionGetReferrer() {
        $this->pageTitle = 'Membership Portal API - Get Referrer';
        $result = '';

        if (isset($_POST['yt0']))
            $result = $this->_getReferrer();

        $this->render('getreferrer', array('result' => $result));
    }

    public function actionGetRegion() {
        $this->pageTitle = 'Membership Portal API - Get Region';
        $result = '';

        if (isset($_POST['yt0']))
            $result = $this->_getRegion();

        $this->render('getregion', array('result' => $result));
    }

    public function actionGetCity() {
        $this->pageTitle = 'Membership Portal API - Get City';
        $result = '';

        if (isset($_POST['yt0']))
            $result = $this->_getCity();

        $this->render('getcity', array('result' => $result));
    }

    //@date 09-16-2014
    public function actionRegisterMemberBT() {
        $this->pageTitle = 'Membership Portal API - Register Member BT';
        $result = '';

        if (isset($_POST['FirstName']) || isset($_POST['LastName']) || isset($_POST['MobileNo']) || isset($_POST['EmailAddress']) || isset($_POST['Birthdate'])) {
            $firstname = $_POST['FirstName'];
            $lastname = $_POST['LastName'];
            $mobileNumber = $_POST['MobileNo'];
            $emailAddress = $_POST['EmailAddress'];
            $birthdate = $_POST['Birthdate'];

            $result = $this->_registerMemberBT($firstname, $lastname, $mobileNumber, $emailAddress, $birthdate);
        }

        $this->render('registermemberbt', array('result' => $result));
    }

    /*
     * Date Created  : August 10, 2015
     * @javida
     */

    public function actionRegisterMemberBTNoEmail() {
        $this->pageTitle = 'Membership Portal API - Register Member BT No Email';
        $result = '';

        if (isset($_POST['FirstName']) || isset($_POST['LastName']) || isset($_POST['MobileNo']) || isset($_POST['Birthdate'])) {
            $firstname = $_POST['FirstName'];
            $lastname = $_POST['LastName'];
            $mobileNumber = $_POST['MobileNo'];
            $birthdate = $_POST['Birthdate'];

            $result = $this->_registerMemberBTNoEmail($firstname, $lastname, $mobileNumber, $birthdate);
        }

        $this->render('registermemberbtnoemail', array('result' => $result));
    }

    //@date 05-07-2015
    public function actionGetBalance() {
        $this->pageTitle = 'Membership Portal API - Get Balance';
        $result = '';

        if (isset($_POST['CardNumber']) || isset($_POST['MPSessionID'])) {
            $cardNumber = $_POST['CardNumber'];
            $mpSessionID = $_POST['MPSessionID'];

            $result = $this->_getBalance($cardNumber, $mpSessionID);
        }

        $this->render('getbalance', array('result' => $result));
    }

    public function actionRedeemCompPoints() {
        $this->pageTitle = 'Membership Portal API - Redeem CompPoints';
        $result = '';

        if (isset($_POST['MPSessionID']) || isset($_POST['CardNumber']) || isset($_POST['Quantity']) || isset($_POST['Source'])) {
            $mpSessionID = $_POST['MPSessionID'];
            $cardNumber = $_POST['CardNumber'];
            $quantity = $_POST['Quantity'];
            $source = $_POST['Source'];

            $config = Yii::app()->params['config'];

            $result = $this->_redeemCompPoints($mpSessionID, $cardNumber, $quantity, $source, $config);
        }

        $this->render('redeemcomppoints', array('result' => $result));
    }

    public function actionGetCivilStatus() {
        $this->pageTitle = 'Membership Portal API - Get ID Presented';
        $result = '';

        if (isset($_POST['yt0']))
            $result = $this->_getCivilStatus();

        $this->render('getcivilstatus', array('result' => $result));
    }

    public function actionGetRegisterFor() {
        $this->pageTitle = 'Membership Portal API - Get ID Presented';
        $result = '';

        if (isset($_POST['yt0']))
            $result = $this->_getRegisterFor();

        $this->render('getregisterfor', array('result' => $result));
    }

    private function _login($username, $password) {
        $postdata = CJSON::encode(array('Username' => $username, 'Password' => $password));
        $result = $this->SubmitData(Yii::app()->params['urlMPAPI'] . 'login', $postdata);

        return $result[1];
    }

    private function _changePassword($cardNumber, $newPassword) {
        $postdata = CJSON::encode(array('CardNumber' => $cardNumber, 'NewPassword' => $newPassword));
        $result = $this->SubmitData(Yii::app()->params['urlMPAPI'] . 'changepassword', $postdata);

        return $result[1];
    }

    private function _forgotPassword($emailCardNumber) {
        $postdata = CJSON::encode(array('EmailCardNumber' => $emailCardNumber));
        $result = $this->SubmitData(Yii::app()->params['urlMPAPI'] . 'forgotpassword', $postdata);

        return $result[1];
    }

    private function _registerMember($firstname, $middlename, $lastname, $nickname, $mobileNumber, $alternateMobileNumber, $emailAddress, $alternateEmail, $gender, $idNumber, $birthdate, $password, $idPresented, $permanentAddress, $nationality, $occupation, $isSmoker, $referrerID, $referralCode, $emailSubscription, $smsSubscription, $CivilStatus, $RegisterFor) {
        $postdata = CJSON::encode(array('FirstName' => $firstname, 'MiddleName' => $middlename, 'LastName' => $lastname, 'NickName' => $nickname, 'MobileNo' => $mobileNumber, 'AlternateMobileNo' => $alternateMobileNumber, 'EmailAddress' => $emailAddress,
                    'AlternateEmail' => $alternateEmail, 'Gender' => $gender, 'IDNumber' => $idNumber, 'Birthdate' => $birthdate, 'Password' => $password, 'IDPresented' => $idPresented, 'PermanentAdd' => $permanentAddress, 'Nationality' => $nationality, 'Occupation' => $occupation, 'IsSmoker' => $isSmoker, 'ReferrerID' => $referrerID, 'ReferralCode' => $referralCode, 'EmailSubscription' => $emailSubscription, 'SMSSubscription' => $smsSubscription));
        $result = $this->SubmitData(Yii::app()->params['urlMPAPI'] . 'registermember', $postdata);

        return $result[1];
    }

    private function _autoregisterMember($firstname, $middlename, $lastname, $nickname, $mobileNumber, $alternateMobileNumber, $emailAddress, $alternateEmail, $gender, $idNumber, $birthdate, $password, $idPresented, $permanentAddress, $nationality, $occupation, $isSmoker, $referrerID, $referralCode, $emailSubscription, $smsSubscription, $CivilStatus, $RegisterFor, $UBCard, $AID, $SiteID) {
        $postdata = CJSON::encode(array('FirstName' => $firstname, 'MiddleName' => $middlename, 'LastName' => $lastname, 'NickName' => $nickname, 'MobileNo' => $mobileNumber, 'AlternateMobileNo' => $alternateMobileNumber, 'EmailAddress' => $emailAddress,
                    'AlternateEmail' => $alternateEmail, 'Gender' => $gender, 'IDNumber' => $idNumber, 'Birthdate' => $birthdate, 'Password' => $password, 'IDPresented' => $idPresented, 'PermanentAdd' => $permanentAddress, 'Nationality' => $nationality, 'Occupation' => $occupation,
                    'IsSmoker' => $isSmoker, 'ReferrerID' => $referrerID, 'ReferralCode' => $referralCode, 'EmailSubscription' => $emailSubscription, 'SMSSubscription' => $smsSubscription, 'CivilStatus' => $CivilStatus, 'RegisterFor' => $RegisterFor, 'UBCard' => $UBCard, 'AID' => $AID, 'SiteID' => $SiteID));
        $result = $this->SubmitData(Yii::app()->params['urlMPAPI'] . 'autoregistermember', $postdata);

        return $result[1];
    }

    private function _updateProfile($mpSessionID, $mobileNumber, $alternateMobileNumber, $emailAddress, $alternateEmail, $gender, $idNumber, $birthdate, $password, $idPresented, $permanentAddress, $nationality, $occupation, $isSmoker, $region, $city) {//$firstname, $middlename, $lastname, $nickname,
        $postdata = CJSON::encode(array('MPSessionID' => $mpSessionID, 'MobileNo' => $mobileNumber, 'AlternateMobileNo' => $alternateMobileNumber, 'EmailAddress' => $emailAddress,
                    'AlternateEmail' => $alternateEmail, 'Gender' => $gender, 'IDNumber' => $idNumber, 'Birthdate' => $birthdate, 'Password' => $password, 'IDPresented' => $idPresented, 'PermanentAdd' => $permanentAddress, 'Nationality' => $nationality, 'Occupation' => $occupation, 'IsSmoker' => $isSmoker, 'Region' => $region, 'City' => $city)); //'FirstName'=>$firstname,'MiddleName' => $middlename, 'LastName'=>$lastname, 'NickName' => $nickname, 
        $result = $this->SubmitData(Yii::app()->params['urlMPAPI'] . 'updateprofile', $postdata);

        return $result[1];
    }

    private function _checkPoints($cardNumber, $config) {
        $postdata = CJSON::encode(array('CardNumber' => $cardNumber, 'Config' => $config));
        $result = $this->SubmitData(Yii::app()->params['urlMPAPI'] . 'checkpoints', $postdata);

        return $result[1];
    }

    private function _listItems($playerClassID, $mpSessionID) {
        $postdata = CJSON::encode(array('PlayerClassID' => $playerClassID, 'MPSessionID' => $mpSessionID));

        $result = $this->SubmitData(Yii::app()->params['urlMPAPI'] . 'listitems', $postdata);

        return $result[1];
    }

    private function _redeemItems($mpSessionID, $cardNumber, $rewardID, $rewardItemID, $quantity, $source, $config) {
        $postdata = CJSON::encode(array('MPSessionID' => $mpSessionID, 'CardNumber' => $cardNumber, 'RewardID' => $rewardID, 'RewardItemID' => $rewardItemID, 'Quantity' => $quantity, 'Source' => $source, 'Config' => $config));

        $result = $this->SubmitData(Yii::app()->params['urlMPAPI'] . 'redeemitems', $postdata);

        return $result[1];
    }

    private function _getProfile($cardNumber, $mpSessionID, $config) {
        $postdata = CJSON::encode(array('CardNumber' => $cardNumber, 'MPSessionID' => $mpSessionID, 'Config' => $config));

        $result = $this->SubmitData(Yii::app()->params['urlMPAPI'] . 'getprofile', $postdata);

        return $result[1];
    }

    private function _getGender() {
        $postdata = CJSON::encode(array());

        $result = $this->SubmitData(Yii::app()->params['urlMPAPI'] . 'getgender', $postdata);


        return $result[1];
    }

    private function _getIDPresented() {
        $postdata = CJSON::encode(array());

        $result = $this->SubmitData(Yii::app()->params['urlMPAPI'] . 'getidpresented', $postdata);

        return $result[1];
    }

    private function _getNationality() {
        $postdata = CJSON::encode(array());

        $result = $this->SubmitData(Yii::app()->params['urlMPAPI'] . 'getnationality', $postdata);

        return $result[1];
    }

    private function _getOccupation() {
        $postdata = CJSON::encode(array());

        $result = $this->SubmitData(Yii::app()->params['urlMPAPI'] . 'getoccupation', $postdata);

        return $result[1];
    }

    private function _getIsSmoker() {
        $postdata = CJSON::encode(array());

        $result = $this->SubmitData(Yii::app()->params['urlMPAPI'] . 'getissmoker', $postdata);

        return $result[1];
    }

    private function _deleteSession() {
        //$url = "http://localhost/MPAPI/index.php/Cron/deletesession";
        $postdata = CJSON::encode(array());

        $result = $this->SubmitData($url, $postdata);

        return $result[1];
    }

    private function _logout($mpSessionID) {
        $postdata = CJSON::encode(array('MPSessionID' => $mpSessionID));

        $result = $this->SubmitData(Yii::app()->params['urlMPAPI'] . 'logout', $postdata);

        return $result[1];
    }

    private function _getReferrer() {
        $postdata = CJSON::encode(array());

        $result = $this->SubmitData(Yii::app()->params['urlMPAPI'] . 'getreferrer', $postdata);

        return $result[1];
    }

    private function _getRegion() {
        $postdata = CJSON::encode(array());

        $result = $this->SubmitData(Yii::app()->params['urlMPAPI'] . 'getregion', $postdata);

        return $result[1];
    }

    private function _getCity() {
        $postdata = CJSON::encode(array());

        $result = $this->SubmitData(Yii::app()->params['urlMPAPI'] . 'getcity', $postdata);

        return $result[1];
    }

    private function _registerMemberBT($firstname, $lastname, $mobileNumber, $emailAddress, $birthdate) {
        $postdata = CJSON::encode(array('FirstName' => $firstname, 'LastName' => $lastname, 'MobileNo' => $mobileNumber, 'EmailAddress' => $emailAddress,
                    'Birthdate' => $birthdate));
        $result = $this->SubmitData(Yii::app()->params['urlMPAPI'] . 'registermemberbt', $postdata);

        return $result[1];
    }

    /*
     * Date Created : August 10,2015
     * @javida
     */

    private function _registerMemberBTNoEmail($firstname, $lastname, $mobileNumber, $birthdate) {
        $postdata = CJSON::encode(array('FirstName' => $firstname, 'LastName' => $lastname, 'MobileNo' => $mobileNumber,
                    'Birthdate' => $birthdate));
        $result = $this->SubmitData(Yii::app()->params['urlMPAPI'] . 'registermemberbtnoemail', $postdata);

        return $result[1];
    }

    private function _getBalance($cardNumber, $mpSessionID) {
        $postdata = CJSON::encode(array('CardNumber' => $cardNumber, 'MPSessionID' => $mpSessionID));

        $result = $this->SubmitData(Yii::app()->params['urlMPAPI'] . 'getbalance', $postdata);

        return $result[1];
    }

    /*
     * Added 04-17-2016
     * John Aaron Vida 
     * @javida
     */

    private function _redeemCompPoints($mpSessionID, $cardNumber, $quantity, $source, $config) {
        $postdata = CJSON::encode(array('MPSessionID' => $mpSessionID, 'CardNumber' => $cardNumber, 'Quantity' => $quantity, 'Source' => $source));

        $result = $this->SubmitData(Yii::app()->params['urlMPAPI'] . 'redeemcomppoints', $postdata);

        return $result[1];
    }

    private function _getCivilStatus() {
        $postdata = CJSON::encode(array());

        $result = $this->SubmitData(Yii::app()->params['urlMPAPI'] . 'getcivilstatus', $postdata);


        return $result[1];
    }

    private function _getRegisterFor() {
        $postdata = CJSON::encode(array());

        $result = $this->SubmitData(Yii::app()->params['urlMPAPI'] . 'getregisterfor', $postdata);


        return $result[1];
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
