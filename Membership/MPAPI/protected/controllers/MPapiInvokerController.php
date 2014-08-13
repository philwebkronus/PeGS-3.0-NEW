<?php
/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of MPapiInvokerController
 *
 * @author fdlsison
 * @date 06-20-2014
 */
class MPapiInvokerController extends Controller{
    
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
    
    //url staging and localhost
    public $url = 'http://172.16.102.174/mpapi.dev.local/index.php/MPapi/';
    //public $url = 'http://localhost/MPAPI/index.php/MPapi/';
            
    
    public function actionOverview(){
        $this->pageTitle = 'Membership Portal API - Overview';
        $this->render('overview');
    }
    
    public function actionLogin(){
        $this->pageTitle = 'Membership Portal API - Login';
        $result = '';
        
        if(isset($_POST['Username']) || isset($_POST['Password'])) {
            $username = $_POST['Username'];
            $password = $_POST['Password'];
                    
            $result = $this->_login($username, $password);
        }
        
        $this->render('login', array('result'=>$result));
    }
    
//    public function actionAuthenticateSession(){
//        $this->pageTitle = 'Membership Portal API - Authenticate Session';
//        $result = '';
//        
//        if(isset($_POST['Username']) || isset($_POST['Password'])) {
//            $username = $_POST['Username'];
//            $password = $_POST['Password'];
//                    
//            $result = $this->_authenticateSession($username, $password);
//        }
//        
//        $this->render('authenticatesession', array('result'=>$result));
//    }
//    
//    public function actionGetActiveSession(){
//        $this->pageTitle = 'Membership Portal API - Get Active Session';
//        $result = '';
//        
//        if(isset($_POST['MPSessionID'])) {
//            $mpSessionID = $_POST['MPSessionID'];
//                    
//            $result = $this->_getActiveSession($mpSessionID);
//        }
//        
//        $this->render('getactivesession', array('result'=>$result));
//    }
    
    public function actionForgotPassword(){
        $this->pageTitle = 'Membership Portal API - Forgot Password';
        $result = '';
        
        if(isset($_POST['EmailCardNumber'])){
            $emailCardNumber = $_POST['EmailCardNumber'];
                    
            $result = $this->_forgotPassword($emailCardNumber);
        }
        
        $this->render('forgotpassword', array('result'=>$result));
    }
    
    public function actionRegisterMember() {
        $this->pageTitle = 'Membership Portal API - Register Member';
        $result = '';
        
        if(isset($_POST['FirstName']) || isset($_POST['LastName']) || isset($_POST['MobileNo']) || isset($_POST['EmailAddress'])
                || isset($_POST['IDNumber']) || isset($_POST['Birthdate']) || isset($_POST['Password']) || isset($_POST['IDPresented']) || isset($_POST['PermanentAdd'])) {
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
        
        $this->render('registermember', array('result'=>$result));
    }
        
    public function actionUpdateProfile(){
        $this->pageTitle = 'Membership Portal API - Update Profile';
        $result = '';
        
        if(isset($_POST['FirstName']) || isset($_POST['LastName']) || isset($_POST['MobileNo']) || isset($_POST['EmailAddress'])
                || isset($_POST['IDNumber']) || isset($_POST['Birthdate']) || isset($_POST['MPSessionID'])) {
            $mpSessionID = $_POST['MPSessionID'];
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
                    
            $result = $this->_updateProfile($mpSessionID, $firstname, $middlename, $lastname, $nickname, $mobileNumber, $alternateMobileNumber, $emailAddress, $alternateEmail, $gender, $idNumber, $birthdate, $password, $idPresented, $permanentAddress, $nationality, $occupation, $isSmoker);
        }
        
        $this->render('updateprofile', array('result'=>$result));
    }
        
    public function actionCheckPoints(){
        $this->pageTitle = 'Membership Portal API - Check Points';
        $result = '';
        
        if(isset($_POST['CardNumber'])){
            $cardNumber = $_POST['CardNumber'];
                    
            $result = $this->_checkPoints($cardNumber);
        }
        
        $this->render('checkpoints', array('result'=>$result));
    }
    
    public function actionListItems(){
        $this->pageTitle = 'Membership Portal API - List Items';
        $result = '';
        
        if(isset($_POST['PlayerClassID']) || isset($_POST['MPSessionID'])) {
            $playerClassID = $_POST['PlayerClassID'];
            $mpSessionID = $_POST['MPSessionID'];
                    
            $result = $this->_listItems($playerClassID, $mpSessionID);
        }
        
        $this->render('listitems', array('result'=>$result));
    }
    
    public function actionRedeemItems(){
        $this->pageTitle = 'Membership Portal API - Redeem Items';
        $result = '';
        
        if(isset($_POST['MPSessionID']) || isset($_POST['CardNumber']) || isset($_POST['RewardItemID']) || isset($_POST['RewardID']) || isset($_POST['Quantity'])
          || isset($_POST['Source'])) {
            $mpSessionID = $_POST['MPSessionID'];
            $cardNumber = $_POST['CardNumber'];
            $rewardItemID = $_POST['RewardItemID'];
            $rewardID = $_POST['RewardID'];
            $quantity = $_POST['Quantity'];
            //$itemQuantity = $_POST['ItemQuantity'];
            $source = $_POST['Source'];
                    
            $result = $this->_redeemItems($mpSessionID, $cardNumber, $rewardID, $rewardItemID, $quantity, $source);
        }
        
        $this->render('redeemitems', array('result'=>$result));
    }
    
    public function actionGetProfile(){
        $this->pageTitle = 'Membership Portal API - Get Profile';
        $result = '';
        
        if(isset($_POST['CardNumber']) || isset($_POST['MPSessionID'])) {
            $cardNumber = $_POST['CardNumber'];
            $mpSessionID = $_POST['MPSessionID'];
                    
            $result = $this->_getProfile($cardNumber, $mpSessionID);
        }
        
        $this->render('getprofile', array('result'=>$result));
    }
    
    public function actionGetGender() {
        $this->pageTitle = 'Membership Portal API - Get Gender';
        $result = '';
        
//        if(isset($_POST['TPSessionID'])) {
//            $tpSessionID = $_POST['TPSessionID'];
//      
        if(isset($_POST['yt0']))
            $result = $this->_getGender();
       // }
        
        $this->render('getgender', array('result'=>$result));
    }
    
    public function actionGetIDPresented() {
        $this->pageTitle = 'Membership Portal API - Get ID Presented';
        $result = '';
        
        if(isset($_POST['yt0']))
            $result = $this->_getIDPresented();
        
        $this->render('getidpresented', array('result'=>$result));
    }
    
    public function actionGetNationality() {
        $this->pageTitle = 'Membership Portal API - Get Nationality';
        $result = '';
        
        if(isset($_POST['yt0']))
            $result = $this->_getNationality();
        
        $this->render('getnationality', array('result'=>$result));
    }
    
    public function actionGetOccupation() {
        $this->pageTitle = 'Membership Portal API - Get Occupation';
        $result = '';
        
        if(isset($_POST['yt0']))
            $result = $this->_getOccupation();
        
        $this->render('getoccupation', array('result'=>$result));
    }
    
    public function actionGetIsSmoker() {
        $this->pageTitle = 'Membership Portal API - Get IsSmoker';
        $result = '';
        
        if(isset($_POST['yt0']))
            $result = $this->_getIsSmoker();
        
        $this->render('getissmoker', array('result'=>$result));
    }
    
    public function actionDeleteSession() {
        $this->pageTitle = 'Membership Portal API - Delete Session';
        $result = '';
        
        if(isset($_POST['yt0']))
            $result = $this->_deleteSession();
        
        $this->render('deletesession', array('result'=>$result));
    }
    
    public function actionLogout() {
        $this->pageTitle = 'Membership Portal API - Logout';
        $result = '';
        
        if(isset($_POST['MPSessionID'])) {
            $mpSessionID = $_POST['MPSessionID'];
                    
            $result = $this->_logout($mpSessionID);
        }
        
        $this->render('logout', array('result'=>$result));
    }
    
    public function actionGetReferrer() {
        $this->pageTitle = 'Membership Portal API - Get Referrer';
        $result = '';
        
        if(isset($_POST['yt0']))
            $result = $this->_getReferrer();
        
        $this->render('getreferrer', array('result'=>$result));
    }
    
    public function actionGetRegion() {
        $this->pageTitle = 'Membership Portal API - Get Region';
        $result = '';
        
        if(isset($_POST['yt0']))
            $result = $this->_getRegion();
        
        $this->render('getregion', array('result'=>$result));
    }
    
    public function actionGetCity() {
        $this->pageTitle = 'Membership Portal API - Get City';
        $result = '';
        
        if(isset($_POST['yt0']))
            $result = $this->_getCity();
        
        $this->render('getcity', array('result'=>$result));
    }
    
    private function _login($username, $password) {
        //$url = "http://172.16.102.174/mpapi.dev.local/index.php/MPapi/login";
        //$url = "http://localhost/MPAPI/index.php/MPapi/login";
        $postdata = CJSON::encode(array('Username' => $username, 'Password' => $password));
        $result = $this->SubmitData($this->url.'login', $postdata);
        
        return $result[1];
    }
    
//    private function _authenticateSession($username, $password) {
//        $url = "http://localhost/MPAPI/index.php/MPapi/authenticatesession";
//        //$url = "http://mpapi.dev.local/index.php/MPapi/login";
//        $postdata = CJSON::encode(array('Username' => $username, 'Password' => $password));
//        $result = $this->SubmitData($url, $postdata);
//        
//        return $result[1];
//    }
//    
//    private function _getActiveSession($mpSessionID) {
//        $url = "http://localhost/MPAPI/index.php/MPapi/getactivesession";
//        //$url = "http://mpapi.dev.local/index.php/MPapi/login";
//        $postdata = CJSON::encode(array('MPSessionID' => $mpSessionID));
//        $result = $this->SubmitData($url, $postdata);
//        
//        return $result[1];
//    }
    
    private function _forgotPassword($emailCardNumber) {
        //$url = "http://172.16.102.174/mpapi.dev.local/index.php/MPapi/forgotpassword";
        //$url = "http://localhost/MPAPI/index.php/MPapi/forgotpassword";
        $postdata = CJSON::encode(array('EmailCardNumber'=>$emailCardNumber));
        $result = $this->SubmitData($this->url.'forgotpassword', $postdata);
        
        return $result[1];
    }
    
    private function _registerMember($firstname, $middlename, $lastname, $nickname, $mobileNumber, $alternateMobileNumber, $emailAddress, $alternateEmail, $gender, $idNumber, $birthdate, $password, $idPresented, $permanentAddress, $nationality, $occupation, $isSmoker, $referrerID, $referralCode, $emailSubscription, $smsSubscription) {
        //$url = "http://172.16.102.174/mpapi.dev.local/index.php/MPapi/registermember";
        //$url = "http://localhost/MPAPI/index.php/MPapi/registermember";
        $postdata = CJSON::encode(array('FirstName'=>$firstname, 'MiddleName' => $middlename, 'LastName'=>$lastname,'NickName' => $nickname, 'MobileNo'=>$mobileNumber, 'AlternateMobileNo' => $alternateMobileNumber, 'EmailAddress'=>$emailAddress,
                                  'AlternateEmail' => $alternateEmail,'Gender' => $gender, 'IDNumber'=>$idNumber, 'Birthdate'=>$birthdate, 'Password' => $password, 'IDPresented' => $idPresented, 'PermanentAdd' => $permanentAddress, 'Nationality' => $nationality, 'Occupation' => $occupation, 'IsSmoker' => $isSmoker, 'ReferrerID' => $referrerID, 'ReferralCode' => $referralCode, 'EmailSubscription' => $emailSubscription, 'SMSSubscription' => $smsSubscription));
        $result = $this->SubmitData($this->url.'registermember', $postdata);
        
        return $result[1];
    }

    private function _updateProfile($mpSessionID, $firstname, $middlename, $lastname, $nickname, $mobileNumber, $alternateMobileNumber, $emailAddress, $alternateEmail, $gender, $idNumber, $birthdate, $password, $idPresented, $permanentAddress, $nationality, $occupation, $isSmoker) {
        //$url = "http://172.16.102.174/mpapi.dev.local/index.php/MPapi/updateprofile";
        //$url = "http://mpapi.dev.local/index.php/MPapi/updateProfile";
        $postdata = CJSON::encode(array('MPSessionID' => $mpSessionID, 'FirstName'=>$firstname,'MiddleName' => $middlename, 'LastName'=>$lastname, 'NickName' => $nickname, 'MobileNo'=>$mobileNumber,'AlternateMobileNo' => $alternateMobileNumber, 'EmailAddress'=>$emailAddress,
                                  'AlternateEmail' => $alternateEmail,'Gender' => $gender, 'IDNumber'=>$idNumber, 'Birthdate'=>$birthdate, 'Password' => $password, 'IDPresented' => $idPresented, 'PermanentAdd' => $permanentAddress, 'Nationality' => $nationality, 'Occupation' => $occupation, 'IsSmoker' => $isSmoker));
        $result = $this->SubmitData($this->url.'updateprofile', $postdata);
        
        return $result[1];
    }
    
    private function _checkPoints($cardNumber) {
        //$url = "http://172.16.102.174/mpapi.dev.local/index.php/MPapi/checkpoints";
        //$url = "http://localhost/MPAPI/index.php/MPapi/checkPoints";
        $postdata = CJSON::encode(array('CardNumber'=>$cardNumber));
        $result = $this->SubmitData($this->url.'checkpoints', $postdata);
        
        return $result[1];
    }
    
    private function _listItems($playerClassID, $mpSessionID) {
        //$url = "http://172.16.102.174/mpapi.dev.local/index.php/MPapi/listitems";
        //$url = "http://mpapi.dev.local/index.php/MPapi/listitems";
        $postdata = CJSON::encode(array('PlayerClassID' => $playerClassID, 'MPSessionID' => $mpSessionID));

        $result = $this->SubmitData($this->url.'listitems', $postdata);
        
        return $result[1];
    }
    
    private function _redeemItems($mpSessionID, $cardNumber, $rewardID, $rewardItemID, $quantity, $source) {
        //$url = "http://172.16.102.174/mpapi.dev.local/index.php/MPapi/redeemitems";
        //$url = "http://mpapi.dev.local/index.php/MPapi/redeemitems";
        $postdata = CJSON::encode(array('MPSessionID' => $mpSessionID, 'CardNumber' => $cardNumber, 'RewardID' => $rewardID, 'RewardItemID' => $rewardItemID, 'Quantity' => $quantity, 'Source' => $source));

        $result = $this->SubmitData($this->url.'redeemitems', $postdata);
        
        return $result[1];
    }
    
    private function _getProfile($cardNumber, $mpSessionID){
        //$url = "http://172.16.102.174/mpapi.dev.local/index.php/MPapi/getprofile";
        //$url = "http://localhost/MPAPI/index.php/MPapi/getprofile";
        $postdata = CJSON::encode(array('CardNumber' => $cardNumber, 'MPSessionID' => $mpSessionID));

        $result = $this->SubmitData($this->url.'getprofile', $postdata);
        
        return $result[1];
    }
    
    private function _getGender(){
        //$url = "http://172.16.102.174/mpapi.dev.local/index.php/MPapi/getgender";
        //$url = "http://mpapi.dev.local/index.php/MPapi/getprofile";
        $postdata = CJSON::encode(array());

        $result = $this->SubmitData($this->url.'getgender', $postdata);
        
        
        return $result[1];
    }
    
    private function _getIDPresented(){
        //$url = "http://172.16.102.174/mpapi.dev.local/index.php/MPapi/getidpresented";
        //$url = "http://mpapi.dev.local/index.php/MPapi/getprofile";
        $postdata = CJSON::encode(array());

        $result = $this->SubmitData($this->url.'getidpresented', $postdata);
        
        return $result[1];
    }
    
    private function _getNationality(){
        //$url = "http://172.16.102.174/mpapi.dev.local/index.php/MPapi/getnationality";
        //$url = "http://mpapi.dev.local/index.php/MPapi/getprofile";
        $postdata = CJSON::encode(array());

        $result = $this->SubmitData($this->url.'getnationality', $postdata);
        
        return $result[1];
    }
    
    private function _getOccupation(){
        //$url = "http://172.16.102.174/mpapi.dev.local/index.php/MPapi/getoccupation";
        //$url = "http://mpapi.dev.local/index.php/MPapi/getprofile";
        $postdata = CJSON::encode(array());

        $result = $this->SubmitData($this->url.'getoccupation', $postdata);
        
        return $result[1];
    }
    
    private function _getIsSmoker(){
        //$url = "http://172.16.102.174/mpapi.dev.local/index.php/MPapi/getissmoker";
        //$url = "http://mpapi.dev.local/index.php/MPapi/getprofile";
        $postdata = CJSON::encode(array());

        $result = $this->SubmitData($this->url.'getissmoker', $postdata);
        
        return $result[1];
    }
    
    private function _deleteSession(){
        //$url = "http://172.16.102.174/mpapi.dev.local/index.php/MPapi/getissmoker";
        $url = "http://localhost/MPAPI/index.php/Cron/deletesession";
        $postdata = CJSON::encode(array());

        $result = $this->SubmitData($url, $postdata);
        
        return $result[1];
    }
    
    private function _logout($mpSessionID){
        //$url = "http://172.16.102.174/mpapi.dev.local/index.php/MPapi/getissmoker";
        //$url = "http://mpapi.dev.local/index.php/MPapi/getprofile";
        $postdata = CJSON::encode(array('MPSessionID' => $mpSessionID));

        $result = $this->SubmitData($this->url.'logout', $postdata);
        
        return $result[1];
    }
    
    private function _getReferrer(){
        //$url = "http://172.16.102.174/mpapi.dev.local/index.php/MPapi/getissmoker";
        //$url = "http://mpapi.dev.local/index.php/MPapi/getprofile";
        $postdata = CJSON::encode(array());

        $result = $this->SubmitData($this->url.'getreferrer', $postdata);
        
        return $result[1];
    }
    
    private function _getRegion(){
        //$url = "http://172.16.102.174/mpapi.dev.local/index.php/MPapi/getissmoker";
        //$url = "http://mpapi.dev.local/index.php/MPapi/getprofile";
        $postdata = CJSON::encode(array());

        $result = $this->SubmitData($this->url.'getregion', $postdata);
        
        return $result[1];
    }
    
    private function _getCity(){
        //$url = "http://172.16.102.174/mpapi.dev.local/index.php/MPapi/getissmoker";
        //$url = "http://mpapi.dev.local/index.php/MPapi/getprofile";
        $postdata = CJSON::encode(array());

        $result = $this->SubmitData($this->url.'getcity', $postdata);
        
        return $result[1];
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
}

?>
