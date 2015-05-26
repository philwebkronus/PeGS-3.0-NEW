<?php

/**
 * Description of AmpapiInvokerController
 *
 * @author jdlachica
 * @date 07/21/2014
 */
class AmpapiInvokerController extends Controller {

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


//    public function actionIndex()
//    {
//            // renders the view file 'protected/views/site/index.php'
//            // using the default layout 'protected/views/layouts/main.php'
//        $this->render('index');
//
//    }
//
    public function actionOverview()
    {
            // renders the view file 'protected/views/site/index.php'
            // using the default layout 'protected/views/layouts/main.php'
        $this->render('overview');

    }
    //Function that authenticates session
    public function actionAuthenticateSession(){
        $this->pageTitle= $this->genTitlePage('Authenticate Session');
        $result='';
        $moduleName ='authenticatesession';

        if(isset($_POST['Username']) && isset($_POST['Password'])){
            $username = $_POST['Username'];
            $password = $_POST['Password'];

            $result = $this->_AuthenticateSession($username, $password, $moduleName);
        }

        $this->render($moduleName, array('result'=>$result));
    }

        private function _AuthenticateSession($username, $password, $moduleName){

            $url = $this->genURL($moduleName);
            $postData = CJSON::encode(array('Username'=>$username, 'Password'=>$password));
            $result = $this->SubmitData($url, $postData);

            return $result[1];
        }

    //Retrieves active session
    public function actionGetActiveSession(){
        $this->pageTitle= $this->genTitlePage('Get Active Session');
        $result='';

        $moduleName ='getactivesession';

        if(isset($_POST['Username'])){
            $Username = $_POST['Username'];
            $result = $this->_GetActiveSession($Username, $moduleName);
        }

        $this->render('getactivesession', array('result'=>$result));
    }
        private function _GetActiveSession($Username, $moduleName){
            $url = $this->genURL($moduleName);
            $postData = CJSON::encode(array('Username'=>$Username));
            $result = $this->SubmitData($url, $postData);

            return $result[1];
        }

    public function actionLogin(){
        $this->pageTitle= $this->genTitlePage('Login');
        $result='';

        $moduleName ='login';
        if(isset($_POST['TPSessionID']) && isset($_POST['Username']) && isset($_POST['Password']) && isset($_POST['AlterStr'])){
            $TPSessionID = $_POST['TPSessionID'];
            $Username = $_POST['Username'];
            $Password = $_POST['Password'];
            $AlterStr = $_POST['AlterStr'];
            $result = $this->_Login($TPSessionID,$Username, $Password, $AlterStr, $moduleName);
        }
        $this->render('login', array('result'=>$result));
    }
        private function _Login($TPSessionID, $Username, $Password, $AlterStr, $moduleName){
            $url = $this->genURL($moduleName);
            $postData = CJSON::encode(array('TPSessionID'=>$TPSessionID, 'Username'=>$Username, 'Password'=>$Password, 'AlterStr' => $AlterStr));
            $result = $this->SubmitData($url, $postData);

            return $result[1];
        }

   public function actionChangePassword(){
        $this->pageTitle= $this->genTitlePage('Change Password');
        $result='';

        $moduleName ='changepassword';
        if(isset($_POST['TPSessionID']) && isset($_POST['CardNumber']) && isset($_POST['NewPassword'])){
            $TPSessionID = $_POST['TPSessionID'];
            $cardNumber = $_POST['CardNumber'];
            $newPassword = $_POST['NewPassword'];
            $result = $this->_changePassword($TPSessionID,$cardNumber, $newPassword, $moduleName);
        }
        $this->render('changepassword', array('result'=>$result));
    }
        private function _changePassword($TPSessionID, $cardNumber, $newPassword, $moduleName){
            $url = $this->genURL($moduleName);
            $postData = CJSON::encode(array('TPSessionID'=>$TPSessionID, 'CardNumber'=>$cardNumber, 'NewPassword'=>$newPassword));
            $result = $this->SubmitData($url, $postData);

            return $result[1];
        }

    public function actionForgotPassword(){
        $this->pageTitle= $this->genTitlePage('Forgot Password');
        $result='';

        $moduleName ='forgotpassword';
        if(isset($_POST['TPSessionID']) && isset($_POST['EmailAddressOrCardNumber'])){
            $TPSessionID = $_POST['TPSessionID'];
            $EmailCardNumber = $_POST['EmailAddressOrCardNumber'];
            $result = $this->_forgotPassword($TPSessionID,$EmailCardNumber, $moduleName);
        }
        $this->render($moduleName, array('result'=>$result));
    }
        private function _forgotPassword($TPSessionID,$EmailCardNumber, $moduleName){
            $url = $this->genURL($moduleName);
            $postData = CJSON::encode(array('TPSessionID'=>$TPSessionID, 'EmailCardNumber'=>$EmailCardNumber));
            $result = $this->SubmitData($url, $postData);

            return $result[1];
        }

        public function actionRegisterMember(){
            $this->pageTitle= $this->genTitlePage('Register Member');
            $result='';

            $moduleName ='registermember';

            if(isset($_POST['TPSessionID']) || isset($_POST['FirstName']) || isset($_POST['LastName']) || isset($_POST['Password']) ||isset($_POST['PermanentAdd']) ||isset($_POST['MobileNo']) || isset($_POST['EmailAddress']) && isset($_POST['IDPresented']) && isset($_POST['IDNumber']) && isset($_POST['Birthdate'])){
                $TPSessionID = $_POST['TPSessionID'];
                $FirstName = $_POST['FirstName'];
                $MiddleName= $_POST['MiddleName'];
                $LastName = $_POST['LastName'];
                $NickName = $_POST['NickName'];
                $Password = $_POST['Password'];
                $PermanentAdd = $_POST['PermanentAdd'];
                $MobileNo = $_POST['MobileNo'];
                $AlternateMobileNo = $_POST['AlternateMobileNo'];
                $EmailAddress = $_POST['EmailAddress'];
                $AlternateEmail = $_POST['AlternateEmail'];
                $Gender = $_POST['Gender'];
                $IDPresented = $_POST['IDPresented'];
                $IDNumber = $_POST['IDNumber'];
                $Nationality = $_POST['Nationality'];
                $Birthdate = $_POST['Birthdate'];
                $Occupation = $_POST['Occupation'];
                $IsSmoker = $_POST['IsSmoker'];
                $ReferralCode = $_POST['ReferralCode'];
                $ReferrerID = $_POST['ReferrerID'];
                $EmailSubscription = $_POST['EmailSubscription'];
                $SMSubscription = $_POST['SMSSubscription'];


                $result = $this->_registerMember($TPSessionID ,$FirstName ,$MiddleName , $LastName,$NickName ,$Password ,$PermanentAdd ,$MobileNo , $AlternateMobileNo,$EmailAddress ,$AlternateEmail ,$Gender ,$IDPresented ,$IDNumber, $Nationality,$Birthdate, $Occupation, $IsSmoker, $ReferralCode, $ReferrerID, $EmailSubscription, $SMSubscription,$moduleName);
            }
            $this->render($moduleName, array('result'=>$result));
        }

            private function _registerMember($TPSessionID ,$FirstName ,$MiddleName , $LastName,$NickName ,$Password ,$PermanentAdd ,$MobileNo , $AlternateMobileNo,$EmailAddress ,$AlternateEmail ,$Gender ,$IDPresented ,$IDNumber, $Nationality,$Birthdate, $Occupation, $IsSmoker, $ReferralCode,$ReferrerID, $EmailSubscription, $SMSubscription, $moduleName){
                $url = $this->genURL($moduleName);
                $postData = CJSON::encode(array('TPSessionID'=>$TPSessionID,'FirstName'=>$FirstName ,'MiddleName'=>$MiddleName , 'LastName'=>$LastName,'NickName'=>$NickName ,'Password'=>$Password ,'PermanentAdd'=>$PermanentAdd ,'MobileNo'=>$MobileNo , 'AlternateMobileNo'=>$AlternateMobileNo,'EmailAddress'=>$EmailAddress ,'AlternateEmail'=>$AlternateEmail ,'Gender'=>$Gender ,'IDPresented'=>$IDPresented ,'IDNumber'=>$IDNumber, 'Nationality'=>$Nationality, 'Birthdate'=>$Birthdate, 'Occupation'=>$Occupation, 'IsSmoker'=>$IsSmoker, 'ReferralCode'=>$ReferralCode, 'ReferrerID'=>$ReferrerID, 'EmailSubscription'=>$EmailSubscription, 'SMSSubscription'=>$SMSubscription));
                $result = $this->SubmitData($url, $postData);

                return $result[1];
            }

    public function actionUpdateProfile(){
        $this->pageTitle= $this->genTitlePage('Update Profile');

        $result='';
        $moduleName ='updateprofile';

        if(isset($_POST['TPSessionID']) && isset($_POST['MPSessionID']) && isset($_POST['FirstName']) && isset($_POST['LastName']) && isset($_POST['MobileNo']) && isset($_POST['EmailAddress']) && isset($_POST['Birthdate'])){
            $TPSessionID = $_POST['TPSessionID'];
            $MPSessionID = $_POST['MPSessionID'];
            $FirstName = $_POST['FirstName'];
            $MiddleName= $_POST['MiddleName'];
            $LastName = $_POST['LastName'];
            $NickName = $_POST['NickName'];
            $Password = $_POST['Password'];
            $PermanentAdd = $_POST['PermanentAdd'];
            $MobileNo = $_POST['MobileNo'];
            $AlternateMobileNo = $_POST['AlternateMobileNo'];
            $EmailAddress = $_POST['EmailAddress'];
            $AlternateEmail = $_POST['AlternateEmail'];
            $Gender = $_POST['Gender'];
            $IDPresented = $_POST['IDPresented'];
            $IDNumber = $_POST['IDNumber'];
            $Nationality = $_POST['Nationality'];
            $Occupation = $_POST['Occupation'];
            $IsSmoker = $_POST['IsSmoker'];
            $Birthdate = $_POST['Birthdate'];
            $Region = $_POST['Region'];
            $City = $_POST['City'];

            $result = $this->_UpdateProfile($TPSessionID,$MPSessionID ,$FirstName ,$MiddleName , $LastName,$NickName ,$Password ,$PermanentAdd ,$MobileNo , $AlternateMobileNo,$EmailAddress ,$AlternateEmail ,$Gender ,$IDPresented ,$IDNumber, $Nationality, $Occupation, $IsSmoker, $Birthdate, $Region, $City, $moduleName);
        }

        $this->render($moduleName, array('result'=>$result));
    }
        private function _UpdateProfile($TPSessionID,$MPSessionID ,$FirstName ,$MiddleName , $LastName,$NickName ,$Password ,$PermanentAdd ,$MobileNo , $AlternateMobileNo,$EmailAddress ,$AlternateEmail ,$Gender ,$IDPresented ,$IDNumber, $Nationality, $Occupation, $IsSmoker, $Birthdate, $Region, $City, $moduleName){
            $url = $this->genURL($moduleName);
            $postData = CJSON::encode(array('TPSessionID'=>$TPSessionID,'MPSessionID'=>$MPSessionID ,'FirstName'=>$FirstName ,'MiddleName'=>$MiddleName , 'LastName'=>$LastName,'NickName'=>$NickName ,'Password'=>$Password ,'PermanentAdd'=>$PermanentAdd ,'MobileNo'=>$MobileNo , 'AlternateMobileNo'=>$AlternateMobileNo,'EmailAddress'=>$EmailAddress ,'AlternateEmail'=>$AlternateEmail ,'Gender'=>$Gender ,'IDPresented'=>$IDPresented ,'IDNumber'=>$IDNumber, 'Nationality'=>$Nationality, 'Occupation'=>$Occupation, 'IsSmoker'=>$IsSmoker, 'Birthdate'=>$Birthdate, 'Region' => $Region, 'City' => $City));
            $result = $this->SubmitData($url, $postData);

            return $result[1];
        }

    public function actionCheckPoints(){
        $this->pageTitle= $this->genTitlePage('Check Points');

        $result='';
        $moduleName ='checkpoints';

        if(isset($_POST['TPSessionID']) && isset($_POST['CardNumber'])){
            $TPSessionID = trim($_POST['TPSessionID']);
            $CardNumber = trim($_POST['CardNumber']);
            $result = $this->_checkPoints($TPSessionID,$CardNumber,$moduleName);
        }

        $this->render($moduleName, array('result'=>$result));
    }
        private function _checkPoints($TPSessionID,$CardNumber,$moduleName){
            $url = $this->genURL($moduleName);
            $postData = CJSON::encode(array('TPSessionID'=>$TPSessionID, 'CardNumber'=>$CardNumber));
            //print_r($postData);
            $result = $this->SubmitData($url, $postData);

            return $result[1];
        }


    public function actionListItems(){
        $this->pageTitle= $this->genTitlePage('List Items');

        $result='';
        $moduleName ='listitems';

        if(isset($_POST['TPSessionID']) && isset($_POST['MPSessionID']) && isset($_POST['PlayerClassID'])){
            $TPSessionID = $_POST['TPSessionID'];
            $MPSessionID = $_POST['MPSessionID'];
            $PlayerClassID = $_POST['PlayerClassID'];
            $result = $this->_listItems($TPSessionID,$MPSessionID,$PlayerClassID,$moduleName);
        }

        $this->render($moduleName, array('result'=>$result));
    }
        private function _listItems($TPSessionID,$MPSessionID,$PlayerClassID,$moduleName){
            $url = $this->genURL($moduleName);
            $postData = CJSON::encode(array('TPSessionID'=>$TPSessionID, 'MPSessionID'=>$MPSessionID, 'PlayerClassID'=>$PlayerClassID));
            $result = $this->SubmitData($url, $postData);

            return $result[1];
        }

    public function actionRedeemItems(){
        $this->pageTitle= $this->genTitlePage('Redeem Items');

        $result='';
        $moduleName ='redeemitems';

        if(isset($_POST['TPSessionID']) || isset($_POST['MPSessionID']) || isset($_POST['CardNumber']) || isset($_POST['RewardID']) || isset($_POST['REwardItemID'])|| isset($_POST['Quantity']) || isset($_POST['Source']) || isset($_POST['Tracking1']) || isset($_POST['Tracking2'])){
            $TPSessionID = $_POST['TPSessionID'];
            $MPSessionID = $_POST['MPSessionID'];
            $CardNumber = $_POST['CardNumber'];
            $RewardID = $_POST['RewardID'];
            $RewardItemID = $_POST['RewardItemID'];
            $Quantity = $_POST['Quantity'];
            $Source = $_POST['Source'];
            $Tracking1 = $_POST['Tracking1'];
            $Tracking2 = $_POST['Tracking2'];
            $result = $this->_redeemItems($TPSessionID,$MPSessionID,$CardNumber,$RewardID,$RewardItemID,$Quantity,$Source,$Tracking1,$Tracking2,$moduleName);
        }

        $this->render($moduleName, array('result'=>$result));
    }
        private function _redeemItems($TPSessionID,$MPSessionID,$CardNumber,$RewardID,$RewardItemID,$Quantity,$Source,$Tracking1,$Tracking2,$moduleName){
            $url = $this->genURL($moduleName);
            $postData = CJSON::encode(array('TPSessionID'=>$TPSessionID, 'MPSessionID'=>$MPSessionID, 'CardNumber'=>$CardNumber, 'RewardID'=>$RewardID, 'RewardItemID'=>$RewardItemID, 'Quantity'=>$Quantity, 'Source'=>$Source, 'Tracking1' => $Tracking1, 'Tracking2' => $Tracking2));
            $result = $this->SubmitData($url, $postData);

            return $result[1];
        }

    public function actionGetProfile(){
        $this->pageTitle= $this->genTitlePage('Get Profile');

        $result='';
        $moduleName ='getprofile';

        if(isset($_POST['TPSessionID']) && isset($_POST['MPSessionID']) && isset($_POST['CardNumber'])){
            $TPSessionID = $_POST['TPSessionID'];
            $MPSessionID = $_POST['MPSessionID'];
            $CardNumber = $_POST['CardNumber'];
            $result = $this->_getProfile($TPSessionID,$MPSessionID,$CardNumber,$moduleName);
        }

        $this->render($moduleName, array('result'=>$result));
    }
        private function _getProfile($TPSessionID,$MPSessionID,$CardNumber,$moduleName){
            $url = $this->genURL($moduleName);
            $postData = CJSON::encode(array('TPSessionID'=>$TPSessionID, 'MPSessionID'=>$MPSessionID, 'CardNumber'=>$CardNumber));
            $result = $this->SubmitData($url, $postData);

            return $result[1];
        }

        public function actionGetBalance(){
        $this->pageTitle= $this->genTitlePage('Get Balance');

        $result='';
        $moduleName ='getbalance';

        if(isset($_POST['TPSessionID']) && isset($_POST['MPSessionID']) && isset($_POST['CardNumber'])){
            $TPSessionID = $_POST['TPSessionID'];
            $MPSessionID = $_POST['MPSessionID'];
            $CardNumber = $_POST['CardNumber'];
            $result = $this->_getBalance($TPSessionID,$MPSessionID,$CardNumber,$moduleName);
        }

        $this->render($moduleName, array('result'=>$result));
    }
        private function _getBalance($TPSessionID,$MPSessionID,$CardNumber,$moduleName){
            $url = $this->genURL($moduleName);
            $postData = CJSON::encode(array('TPSessionID'=>$TPSessionID, 'MPSessionID'=>$MPSessionID, 'CardNumber'=>$CardNumber));
            $result = $this->SubmitData($url, $postData);

            return $result[1];
        }

    public function actionGetGender(){
        $this->pageTitle= $this->genTitlePage('Get Gender');

        $result='';
        $moduleName ='getgender';
        if(isset($_POST['TPSessionID'])){
            $TPSessionID = $_POST['TPSessionID'];
            $result = $this->_getGender($TPSessionID, $moduleName);
        }

        $this->render($moduleName, array('result'=>$result));
    }
        private function _getGender($TPSessionID, $moduleName){
            $url = $this->genURL($moduleName);
            $postData = CJSON::encode(array('TPSessionID'=>$TPSessionID));
            $result = $this->SubmitData($url, $postData);

            return $result[1];
        }

    public function actionGetIDPresented(){
        $this->pageTitle= $this->genTitlePage('Get ID Presented');

        $result='';
        $moduleName ='getidpresented';

        if(isset($_POST['TPSessionID'])){
            $TPSessionID = $_POST['TPSessionID'];
            $result = $this->_getIDPresented($TPSessionID, $moduleName);
        }

        $this->render($moduleName, array('result'=>$result));
    }
        private function _getIDPresented($TPSessionID, $moduleName){
            $url = $this->genURL($moduleName);
            $postData = CJSON::encode(array('TPSessionID'=>$TPSessionID));
            $result = $this->SubmitData($url, $postData);

            return $result[1];
        }

    public function actionGetNationality(){
        $this->pageTitle= $this->genTitlePage('Get Nationality');

        $result='';
        $moduleName ='getnationality';

        if(isset($_POST['TPSessionID'])){
            $TPSessionID = $_POST['TPSessionID'];
            $result = $this->_getNationality($TPSessionID, $moduleName);
        }

        $this->render($moduleName, array('result'=>$result));
    }
        private function _getNationality($TPSessionID, $moduleName){
            $url = $this->genURL($moduleName);
            $postData = CJSON::encode(array('TPSessionID'=>$TPSessionID));
            $result = $this->SubmitData($url, $postData);

            return $result[1];
        }

    public function actionGetOccupation(){
        $this->pageTitle= $this->genTitlePage('Get Occupation');

        $result='';
        $moduleName ='getoccupation';

        if(isset($_POST['TPSessionID'])){
            $TPSessionID = $_POST['TPSessionID'];
            $result = $this->_getNationality($TPSessionID, $moduleName);
        }

        $this->render($moduleName, array('result'=>$result));
    }
        private function _getOccupation($TPSessionID, $moduleName){
            $url = $this->genURL($moduleName);
            $postData = CJSON::encode(array('TPSessionID'=>$TPSessionID));
            $result = $this->SubmitData($url, $postData);

            return $result[1];
        }

    public function actionGetIsSmoker(){
        $this->pageTitle= $this->genTitlePage('Get IsSmoker');
        $result='';

        $moduleName = 'getissmoker';
        if(isset($_POST['TPSessionID'])){
            $TPSessionID = $_POST['TPSessionID'];
            $result = $this->_GetIsSmoker($TPSessionID, $moduleName);
        }
        $this->render($moduleName, array('result'=>$result));
    }
        private function _GetIsSmoker($TPSessionID, $moduleName){
            $url = $this->genURL($moduleName);
            $postData = CJSON::encode(array('TPSessionID'=>$TPSessionID));
            $result = $this->SubmitData($url, $postData);

            return $result[1];
        }

    public function actionGetReferrer(){
        $this->pageTitle= $this->genTitlePage('Get Referrer');
        $result='';

        $moduleName = 'getreferrer';
        if(isset($_POST['TPSessionID'])){
            $TPSessionID = $_POST['TPSessionID'];
            $result = $this->_GetReferrer($TPSessionID, $moduleName);
        }
        $this->render($moduleName, array('result'=>$result));
    }
        private function _GetReferrer($TPSessionID, $moduleName){
            $url = $this->genURL($moduleName);
            $postData = CJSON::encode(array('TPSessionID'=>$TPSessionID));
            $result = $this->SubmitData($url, $postData);

            return $result[1];
        }

    public function actionGetRegion(){
        $this->pageTitle= $this->genTitlePage('Get Region');
        $result='';

        $moduleName = 'getregion';
        if(isset($_POST['TPSessionID'])){
            $TPSessionID = $_POST['TPSessionID'];
            $result = $this->_GetRegion($TPSessionID, $moduleName);
        }
        $this->render($moduleName, array('result'=>$result));
    }
        private function _GetRegion($TPSessionID, $moduleName){
            $url = $this->genURL($moduleName);
            $postData = CJSON::encode(array('TPSessionID'=>$TPSessionID));
            $result = $this->SubmitData($url, $postData);

            return $result[1];
        }

    public function actionGetCity(){
        $this->pageTitle= $this->genTitlePage('Get City');
        $result='';

        $moduleName = 'getcity';
        if(isset($_POST['TPSessionID'])){
            $TPSessionID = $_POST['TPSessionID'];
            $result = $this->_GetCity($TPSessionID, $moduleName);
        }
        $this->render($moduleName, array('result'=>$result));
    }
        private function _GetCity($TPSessionID, $moduleName){
            $url = $this->genURL($moduleName);
            $postData = CJSON::encode(array('TPSessionID'=>$TPSessionID));
            $result = $this->SubmitData($url, $postData);

            return $result[1];
        }

    public function actionLogout(){
        $this->pageTitle= $this->genTitlePage('Logout');
        $result='';

        $moduleName = 'logout';
        if(isset($_POST['TPSessionID'])){
            $TPSessionID = $_POST['TPSessionID'];
            $MPSessionID = $_POST['MPSessionID'];
            $result = $this->_Logout($TPSessionID,$MPSessionID, $moduleName);
        }
        $this->render($moduleName, array('result'=>$result));
    }
        private function _Logout($TPSessionID,$MPSessionID, $moduleName){
            $url = $this->genURL($moduleName);
            $postData = CJSON::encode(array('TPSessionID'=>$TPSessionID, 'MPSessionID'=>$MPSessionID));
            $result = $this->SubmitData($url, $postData);

            return $result[1];
        }
        
    //@date 10-27-2014
    //@author fdlsison
    public function actionCreateMobileInfo(){
        $this->pageTitle= $this->genTitlePage('Create Mobile Info');
        $result='';
        
        $moduleName ='createmobileinfo';
        if(isset($_POST['TPSessionID']) && isset($_POST['Username']) && isset($_POST['Password']) && isset($_POST['AlterStr'])){
            $TPSessionID = $_POST['TPSessionID'];
            $Username = $_POST['Username'];
            $Password = $_POST['Password'];
            $AlterStr = $_POST['AlterStr'];
            $result = $this->_CreateMobileInfo($TPSessionID,$Username, $Password, $AlterStr, $moduleName);
        }
        $this->render('createmobileinfo', array('result'=>$result));
    }

    private function _CreateMobileInfo($TPSessionID, $Username, $Password, $AlterStr, $moduleName){
        $url = $this->genURL($moduleName);
        $postData = CJSON::encode(array('TPSessionID'=>$TPSessionID, 'Username'=>$Username, 'Password'=>$Password, 'AlterStr' => $AlterStr));
        $result = $this->SubmitData($url, $postData);

        return $result[1];
    }  

    //-------------------------Generator Functions-------------

    //This function dynamically generates string for title page
    private function genTitlePage($moduleName = null){
        return "AMPAPI - ".$moduleName;
    }
    //This function dynamically generates URL string for the use of certain method
    private function genURL($moduleName = null){
        return Yii::app()->params['urlAMPAPI'].$moduleName;
    }
    //----------------------------------------------

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

            //print_r($uri);exit;
            return array( $http_status, $response );
    }

    public function actionError()
    {
            if($error=Yii::app()->errorHandler->error)
            {
                    if(Yii::app()->request->isAjaxRequest)
                            echo $error['message'];
                    else
                            $this->render('error', $error);
            }
    }

}

?>
