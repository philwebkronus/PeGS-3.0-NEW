<?php
/**
 * Invoker for ForceT Authentication webservice
 * @author gvjagolino,aqdepliyan,jefloresca,jdlachica,flsison
 */
class PcwsInvokerController extends CController{
    
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
    
    public function actionOverview(){
        $this->pageTitle = 'PCWS - Overview';
        $this->render('overview');
    }
    
    public function actionDeposit(){
        $this->pageTitle = 'PCWS - Deposit';
        $result = '';
        if(isset($_POST['ServiceID']) && isset($_POST['CardNumber']) && isset($_POST['Amount']) && isset($_POST['PaymentType']) && isset($_POST['SiteID']) && isset($_POST['AID']) && isset($_POST['AccessDate']) && isset($_POST['Token'])){
            $serviceid = $_POST['ServiceID'];
            $cardnumber = $_POST['CardNumber'];
            $amount = $_POST['Amount'];
            $paymenttype = $_POST['PaymentType'];
            $siteid = $_POST['SiteID'];
            $aid = $_POST['AID'];
            $username = $_POST['SystemUsername'];
            $syscode = empty(Yii::app()->params['SystemCode'][$username])?'':Yii::app()->params['SystemCode'][$username];
            $accessdate = $_POST['AccessDate'];
            //$accessdate = date('Y-m-d H:i:s');
            $date1 = new DateTime($accessdate);
            $dt = $date1->format('YmdHis');
            $tkn = $_POST['Token'];
            $tracenumber = $_POST['TraceNumber'];
            $referencenumber = $_POST['ReferenceNumber'];
            $couponCode = $_POST['CouponCode'];
            $paymentTrackingID = $_POST['PaymentTrackingID'];
            //$tkn = sha1($dt.$syscode);
            
            $result = $this->_deposit($serviceid, $cardnumber, $amount, $paymenttype, $siteid, $aid, $username,$accessdate,$tkn,$tracenumber,$referencenumber, $couponCode, $paymentTrackingID);
        }
        
        $this->render('deposit', array('result'=>$result));
    }
    
        
    private function _deposit($serviceid, $cardnumber, $amount, $paymenttype, $siteid, $aid, $username,$accessdate,$tkn,$tracenumber,$referencenumber, $couponCode, $paymentTrackingID){
        $url = Yii::app()->params['deposit'];
        
        $postdata = CJSON::encode(array('ServiceID'=>$serviceid, 'CardNumber'=>$cardnumber, 'Amount'=>$amount, 'PaymentType'=>$paymenttype, 'SiteID'=>$siteid, 'AID'=>$aid,
                                                                            'SystemUsername'=>$username, 'AccessDate'=>$accessdate, 'Token'=>$tkn, 'TraceNumber'=>$tracenumber, 'ReferenceNumber'=>$referencenumber, 'CouponCode'=>$couponCode, 'PaymentTrackingID'=>$paymentTrackingID));
       
        $result = $this->SubmitData($url, $postdata);
        
        return $result[1];
    }
    
    public function actionWithdraw(){
        $this->pageTitle = 'PCWS - Withdraw';
        $result = '';
        if(isset($_POST['ServiceID']) && isset($_POST['CardNumber']) && isset($_POST['Amount']) && isset($_POST['SiteID']) && isset($_POST['AID']) && isset($_POST['AccessDate']) && isset($_POST['Token'])){
            $serviceid = $_POST['ServiceID'];
            $cardnumber = $_POST['CardNumber'];
            $amount = $_POST['Amount'];
            $siteid = $_POST['SiteID'];
            $aid = $_POST['AID'];
            $username = $_POST['SystemUsername'];
            $syscode = empty(Yii::app()->params['SystemCode'][$username])?'':Yii::app()->params['SystemCode'][$username];
            $accessdate = $_POST['AccessDate'];
            //$accessdate = date('Y-m-d H:i:s');
            $date1 = new DateTime($accessdate);
            $dt = $date1->format('YmdHis');
            $tkn = $_POST['Token'];
            //$tkn = sha1($dt.$syscode);

            
            $result = $this->_withdraw($serviceid, $cardnumber, $amount, $siteid, $aid, $username, $accessdate, $tkn);
        }
        
        $this->render('withdraw', array('result'=>$result));
    }
    
    private function _withdraw($serviceid, $cardnumber, $amount, $siteid, $aid, $username, $accessdate, $tkn){
        $url = Yii::app()->params['withdraw'];
        
        $postdata = CJSON::encode(array('ServiceID'=>$serviceid, 'CardNumber'=>$cardnumber, 'Amount'=>$amount, 'SiteID'=>$siteid, 'AID'=>$aid, 
            'SystemUsername'=>$username, 'AccessDate'=>$accessdate, 'Token'=>$tkn));
       
        $result = $this->SubmitData($url, $postdata);
        
        return $result[1];
    }
       public function actionGetTermsAndCondition(){
        $this->pageTitle = 'PCWS - Get Terms And Condition';
        $result = '';
        if(isset($_POST['SystemUsername']) && isset($_POST['AccessDate']) && isset($_POST['Token'])){
            $username = $_POST['SystemUsername'];
            $syscode = empty(Yii::app()->params['SystemCode'][$username])?'':Yii::app()->params['SystemCode'][$username];
            $accessdate = $_POST['AccessDate'];
            //$accessdate = date('Y-m-d H:i:s');
            $date1 = new DateTime($accessdate);
            $dt = $date1->format('YmdHis');
            $tkn = $_POST['Token'];
            //$tkn = sha1($dt.$syscode);
            
            $result = $this->_getTermsAndCondition($username,$accessdate,$tkn);
        }
        
        $this->render('gettermsandcondition', array('result'=>$result));
    }
        private function _getTermsAndCondition($username,$accessdate,$tkn){
        $url = Yii::app()->params['gettermsandcondition'];
        
        $postdata = CJSON::encode(array('SystemUsername'=>$username, 'AccessDate'=>$accessdate, 'Token'=>$tkn));
       
        $result = $this->SubmitData($url, $postdata);
        
        return $result[1];
    }
    public function actionGetbalance(){
        $this->pageTitle = 'PCWS - Get Balance';
        $result = '';
        if(isset($_POST['CardNumber']) && isset($_POST['SystemUsername']) && isset($_POST['AccessDate']) && isset($_POST['Token'])){
            $cardnumber = $_POST['CardNumber'];
            $username = $_POST['SystemUsername'];
            $syscode = empty(Yii::app()->params['SystemCode'][$username])?'':Yii::app()->params['SystemCode'][$username];
            $accessdate = $_POST['AccessDate'];
            //$accessdate = date('Y-m-d H:i:s');
            $date1 = new DateTime($accessdate);
            $dt = $date1->format('YmdHis');
            $tkn = $_POST['Token'];
            //$tkn = sha1($dt.$syscode);
            
            $result = $this->_getbalance($cardnumber,$username,$accessdate,$tkn);
        }
        
        $this->render('getbalance', array('result'=>$result));
    }
    
    private function _getbalance($cardnumber,$username,$accessdate,$tkn){
        $url = Yii::app()->params['getbalance'];
        
        $postdata = CJSON::encode(array('CardNumber'=>$cardnumber,'SystemUsername'=>$username, 'AccessDate'=>$accessdate, 'Token'=>$tkn));
       
        $result = $this->SubmitData($url, $postdata);
        
        return $result[1];
    }
    
    
    
    public function actionUpdateTerminalState() {
        $this->pageTitle = 'PCWS - Update Terminal State';
        $result = '';
        if(isset($_POST['CardNumber']) && isset($_POST['TerminalName']) && isset($_POST['SystemUsername']) && isset($_POST['ServiceID']) && isset($_POST['AccessDate']) && isset($_POST['Token'])){
            $cardnumber = $_POST['CardNumber'];
            $terminalname = $_POST['TerminalName'];
            $match = preg_match("/^ICSA-/",$terminalname);
            $terminalname = $match ? substr($terminalname, strlen("ICSA-")):$terminalname;
            $serviceid = $_POST['ServiceID'];
            $username = $_POST['SystemUsername'];
            $syscode = empty(Yii::app()->params['SystemCode'][$username])?'':Yii::app()->params['SystemCode'][$username];
            $accessdate = $_POST['AccessDate'];
            //$accessdate = date('Y-m-d H:i:s');
            $date1 = new DateTime($accessdate);
            $dt = $date1->format('YmdHis');
            $tkn = $_POST['Token'];
            //$tkn = sha1($dt.$syscode);
            
            $result = $this->_updateterminalstate($cardnumber, $terminalname, $serviceid, $username,$accessdate,$tkn);
        }
        
        $this->render('updateterminalstate', array('result'=>$result));
    }
    
    private function _updateterminalstate($cardnumber, $terminalname, $serviceid, $username,$accessdate,$tkn){
        $url = Yii::app()->params['updateterminalstate'];
        
        $postdata = CJSON::encode(array('CardNumber'=>$cardnumber, 'TerminalName'=>$terminalname, 'ServiceID'=>$serviceid, 
                                                                            'SystemUsername'=>$username, 'AccessDate'=>$accessdate, 'Token'=>$tkn));
       
        $result = $this->SubmitData($url, $postdata);
        
        return $result[1];
    }
    
    public function actionGetCompPoints() {
        $this->pageTitle = 'PCWS - Get Comp Points';
        $result = '';
        
        if(isset($_POST['CardNumber']) && isset($_POST['SystemUsername']) && isset($_POST['AccessDate']) && isset($_POST['Token'])){
            $cardnumber = $_POST['CardNumber'];
            $username = $_POST['SystemUsername'];
            $syscode = empty(Yii::app()->params['SystemCode'][$username])?'':Yii::app()->params['SystemCode'][$username];
            $accessdate = $_POST['AccessDate'];
            //$accessdate = date('Y-m-d H:i:s');
            $date1 = new DateTime($accessdate);
            $dt = $date1->format('YmdHis');
            $tkn = $_POST['Token'];
            //$tkn = sha1($dt.$syscode);
            
            $result = $this->_getcomppoints($cardnumber,$username,$accessdate,$tkn);
        }
        
        $this->render('getcomppoints', array('result'=>$result));
    }
    
    private function _getcomppoints($cardnumber,$username,$accessdate,$tkn){
        $url = Yii::app()->params['getcomppoints'];
        
        $postdata = CJSON::encode(array('CardNumber'=>$cardnumber,'SystemUsername'=>$username, 'AccessDate'=>$accessdate, 'Token'=>$tkn));
       
        $result = $this->SubmitData($url, $postdata);
        
        return $result[1];
    }
    
    public function actionAddCompPoints() {
        $this->pageTitle = 'PCWS - Add Comp Points';
        $result = '';
        if(isset($_POST['CardNumber']) && isset($_POST['Amount']) && isset($_POST['SiteID']) && isset($_POST['ServiceID']) && isset($_POST['SystemUsername']) && isset($_POST['AccessDate']) && isset($_POST['Token'])){
            $cardnumber = $_POST['CardNumber'];
            $amount = $_POST['Amount'];
            $siteID = $_POST['SiteID'];
            $serviceID = $_POST['ServiceID'];
            $username = $_POST['SystemUsername'];
            $syscode = empty(Yii::app()->params['SystemCode'][$username])?'':Yii::app()->params['SystemCode'][$username];
            $accessdate = $_POST['AccessDate'];
            //$accessdate = date('Y-m-d H:i:s');
            $date1 = new DateTime($accessdate);
            $dt = $date1->format('YmdHis');
            $tkn = $_POST['Token'];
            //$tkn = sha1($dt.$syscode);
            
            $result = $this->_addcomppoints($cardnumber, $amount, $siteID, $serviceID, $username,$accessdate,$tkn);
        }
        
        $this->render('addcomppoints', array('result'=>$result));
    }
    
    private function _addcomppoints($cardnumber, $amount, $siteID, $serviceID, $username,$accessdate,$tkn){
        $url = Yii::app()->params['addcomppoints'];
        
        $postdata = CJSON::encode(array('CardNumber'=>$cardnumber, 'Amount'=>$amount,'SiteID'=>$siteID,'ServiceID'=>$serviceID, 'SystemUsername'=>$username, 'AccessDate'=>$accessdate, 'Token'=>$tkn));
       
        $result = $this->SubmitData($url, $postdata);
        
        return $result[1];
    }
    
    public function actionDeductCompPoints() {
        $this->pageTitle = 'PCWS - Deduct Comp Points';
        $result = '';
        
        if(isset($_POST['CardNumber']) && isset($_POST['Amount']) && isset($_POST['SystemUsername']) && isset($_POST['AccessDate']) && isset($_POST['Token'])){
            $cardnumber = $_POST['CardNumber'];
            $amount = $_POST['Amount'];
            $siteID = $_POST['SiteID'];
            $accessdate = $_POST['AccessDate'];
            $username = $_POST['SystemUsername'];
            $syscode = empty(Yii::app()->params['SystemCode'][$username])?'':Yii::app()->params['SystemCode'][$username];
            //$accessdate = date('Y-m-d H:i:s');
            $date1 = new DateTime($accessdate);
            $dt = $date1->format('YmdHis');
            $tkn = $_POST['Token'];
            //$tkn = sha1($dt.$syscode);
            
            $result = $this->_deductcomppoints($siteID, $cardnumber, $amount, $username,$accessdate,$tkn);
            
            
        }
        
        $this->render('deductcomppoints', array('result'=>$result));
    }

        private function _deductcomppoints($siteID, $cardnumber, $amount, $username,$accessdate,$tkn){
        $url = Yii::app()->params['deductcomppoints'];
        
        $postdata = CJSON::encode(array('CardNumber'=>$cardnumber, 'Amount'=>$amount,'SiteID'=>$siteID, 'SystemUsername'=>$username, 'AccessDate'=>$accessdate, 'Token'=>$tkn));
        
        $result = $this->SubmitData($url, $postdata);

        return $result[1];
    }
    
    public function actionCheckpin(){
        $this->pageTitle = 'PCWS - Check PIN';
        $result = '';
        if(isset($_POST['PIN']) && isset($_POST['CardNumber']) && isset($_POST['SystemUsername']) && isset($_POST['AccessDate']) && isset($_POST['Token'])){
            $cardnumber = $_POST['CardNumber'];
            $pin = $_POST['PIN'];
            $username = $_POST['SystemUsername'];
            $syscode = empty(Yii::app()->params['SystemCode'][$username])?'':Yii::app()->params['SystemCode'][$username];
            //$accessdate = date('Y-m-d H:i:s');
            $accessdate = $_POST['AccessDate'];
            $date1 = new DateTime($accessdate);
            $dt = $date1->format('YmdHis');
            //$tkn = sha1($dt.$syscode);
            $tkn = $_POST['Token'];
            
            $result = $this->_checkpin($cardnumber,$pin,$username,$accessdate,$tkn);
        }
        
        $this->render('checkpin', array('result'=>$result));
    }
    
    private function _checkpin($cardnumber,$pin,$username,$accessdate,$tkn){
        $url = Yii::app()->params['checkpin'];
        $postdata = CJSON::encode(array('CardNumber' => $cardnumber,'PIN'=>$pin,'SystemUsername'=>$username, 'AccessDate'=>$accessdate, 'Token'=>$tkn));
        $result = $this->SubmitData($url, $postdata);

        return $result[1];
    }
    
    public function actionChangepin(){
        $this->pageTitle = 'PCWS - Change PIN';
        $result = '';
        if(isset($_POST['ActionCode']) && isset($_POST['CardNumber'])  && isset($_POST['CurrentPin']) && isset($_POST['NewPin']) && isset($_POST['SystemUsername']) && isset($_POST['AccessDate']) && isset($_POST['Token'])){
            $actionCode = $_POST['ActionCode'];
            $cardnumber = $_POST['CardNumber'];
            $currentPin = $_POST['CurrentPin'];
            $newPin = $_POST['NewPin'];
            $username = $_POST['SystemUsername'];
            $syscode = empty(Yii::app()->params['SystemCode'][$username])?'':Yii::app()->params['SystemCode'][$username];
            //$accessdate = date('Y-m-d H:i:s');
            $accessdate = $_POST['AccessDate'];
            $date1 = new DateTime($accessdate);
            $dt = $date1->format('YmdHis');
            //$tkn = sha1($dt.$syscode);
            $tkn = $_POST['Token'];
            
            $result = $this->_changepin($actionCode,$cardnumber,$currentPin,$newPin,$username,$accessdate,$tkn);
        }
        
        $this->render('changepin', array('result'=>$result));
    }
    
    private function _changepin($actionCode,$cardnumber,$currentPin,$newPin,$username,$accessdate,$tkn){
        $url = Yii::app()->params['changepin'];
        
        $postdata = CJSON::encode(array('ActionCode' => $actionCode,'CardNumber'=>$cardnumber ,'CurrentPin'=>$currentPin,'NewPin'=>$newPin,
                                                                            'SystemUsername'=>$username, 'AccessDate'=>$accessdate, 'Token'=>$tkn));
       
        $result = $this->SubmitData($url, $postdata);
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
            curl_setopt( $curl, CURLOPT_SSLVERSION, 3 );
            // Data+Files to be posted
            curl_setopt($curl, CURLOPT_POSTFIELDS, $postdata);
            $response = curl_exec( $curl );

            $http_status = curl_getinfo( $curl, CURLINFO_HTTP_CODE );

            curl_close( $curl );

            return array( $http_status, $response );
    }
    
    public function actionUnlock(){
        $this->pageTitle = 'Unlock';
        $result = '';
        
        if(isset($_POST['TerminalCode']) && isset($_POST['ServiceID']) && isset($_POST['CardNumber']) && isset($_POST['SystemUsername']) && isset($_POST['AccessDate']) && isset($_POST['Token'])){
            $username = $_POST['SystemUsername'];
            $syscode = empty(Yii::app()->params['SystemCode'][$username])?'':Yii::app()->params['SystemCode'][$username];
            $accessdate = $_POST['AccessDate'];
            //$accessdate = date('Y-m-d H:i:s');
            $date1 = new DateTime($accessdate);
            $dt = $date1->format('YmdHis');
            $tkn = $_POST['Token'];
            //$tkn = sha1($dt.$syscode);
            
            $result = $this->_unlock($_POST['TerminalCode'], $_POST['ServiceID'], $_POST['CardNumber'],$username,$accessdate,$tkn);
        }
        $this->render('unlock', array('result'=>$result));
        
    }
    
        private function _unlock($terminalCode, $serviceID, $cardNumber, $username, $accessdate, $tkn){
            $uri = Yii::app()->params['unlock'];
            $postdata = CJSON::encode(array('TerminalCode'=>$terminalCode, 'ServiceID'=>$serviceID,'CardNumber'=>$cardNumber,'SystemUsername'=>$username, 'AccessDate'=>$accessdate, 'Token'=>$tkn));
            
            $result = $this->SubmitData($uri, $postdata);
            return $result[1];
        }
    
    public function actionForceLogout(){
        $this->pageTitle='Force Logout';
        $result = '';
        
        if(isset($_POST['Login']) && isset($_POST['SystemUsername']) && isset($_POST['AccessDate']) && isset($_POST['Token'])){
            $username = $_POST['SystemUsername'];
            $syscode = empty(Yii::app()->params['SystemCode'][$username])?'':Yii::app()->params['SystemCode'][$username];
            $accessdate = $_POST['AccessDate'];
            //$accessdate = date('Y-m-d H:i:s');
            $date1 = new DateTime($accessdate);
            $dt = $date1->format('YmdHis');
            $tkn = $_POST['Token'];
            //$tkn = sha1($dt.$syscode);
            $result = $this->_forceLogout($_POST['Login'],$username,$accessdate,$tkn);
        }
        $this->render('forcelogout',array('result'=>$result));
    }
        private function _forceLogout($login, $username, $accessdate, $tkn){
            $uri = Yii::app()->params['forcelogout'];
            $postdata = CJSON::encode(array('Login'=>$_POST['Login'],'SystemUsername'=>$username, 'AccessDate'=>$accessdate, 'Token'=>$tkn));
            
            $result = $this->SubmitData($uri, $postdata);
            return $result[1];
        }
        
    public function actionCreateSession(){
       $this->pageTitle = 'Create Session';
        $result = '';
        
        if(isset($_POST['TerminalCode']) && isset($_POST['ServiceID']) && isset($_POST['CardNumber']) && isset($_POST['SystemUsername']) && isset($_POST['AccessDate']) && isset($_POST['Token'])){
            $username = $_POST['SystemUsername'];
            $syscode = empty(Yii::app()->params['SystemCode'][$username])?'':Yii::app()->params['SystemCode'][$username];
            $accessdate = $_POST['AccessDate'];
            //$accessdate = date('Y-m-d H:i:s');
            $date1 = new DateTime($accessdate);
            $dt = $date1->format('YmdHis');
            $tkn = $_POST['Token'];
            //$tkn = sha1($dt.$syscode);
            
            $result = $this->_createsession($_POST['TerminalCode'], $_POST['ServiceID'], $_POST['CardNumber'],$username,$accessdate,$tkn);
        }
        $this->render('createsession', array('result'=>$result));
    }
        private function _createsession($terminalCode, $serviceID, $cardNumber, $username, $accessdate, $tkn){
            $uri = Yii::app()->params['createsession'];
            $postdata = CJSON::encode(array('TerminalCode'=>$terminalCode, 'ServiceID'=>$serviceID,'CardNumber'=>$cardNumber,'SystemUsername'=>$username, 'AccessDate'=>$accessdate, 'Token'=>$tkn));

            $result = $this->SubmitData($uri, $postdata);
            return $result[1];
        }
    
    public function actionRemovesession() {
        $result = '';
        
        if(isset($_POST['TerminalCode']) && isset($_POST['CardNumber'])
                                         && isset($_POST['SystemUsername']) 
                                         && isset($_POST['AccessDate']) 
                                         && isset($_POST['Token'])){
            
            $username = $_POST['SystemUsername'];
            $syscode = empty(Yii::app()->params['SystemCode'][$username])?'':Yii::app()->params['SystemCode'][$username];
            $accessdate = $_POST['AccessDate'];
            //$accessdate = date('Y-m-d H:i:s');
            $date1 = new DateTime($accessdate);
            $dt = $date1->format('YmdHis');
            $tkn = $_POST['Token'];
            
            //$tkn = sha1($dt.$syscode);
            $result = $this->_removesession($_POST['TerminalCode'], $_POST['CardNumber'],$username,$accessdate,$tkn);
        }
        $this->render('removesession', array('result' => $result));
    }
    
    private function _removesession($terminalcode, $cardnumber, $username, $accessdate, $tkn){
            $uri = Yii::app()->params['removesession'];
            $postdata = CJSON::encode(array('TerminalCode'=>$terminalcode, 
                                            'CardNumber' => $cardnumber, 
                                            'SystemUsername'=>$username, 
                                            'AccessDate'=>$accessdate, 
                                            'Token'=>$tkn));
            
            $result = $this->SubmitData($uri, $postdata);
            return $result[1];
    }
    public function actionUnlockgenesis() {
        $this->pageTitle = 'Unlock Genesis';
        $result = '';
        
        if(isset($_POST['TerminalCode']) && isset($_POST['ServiceID']) && isset($_POST['CardNumber']) && isset($_POST['SystemUsername']) && isset($_POST['AccessDate']) && isset($_POST['Token'])){
            $username = $_POST['SystemUsername'];
            $syscode = empty(Yii::app()->params['SystemCode'][$username])?'':Yii::app()->params['SystemCode'][$username];
            $accessdate = $_POST['AccessDate'];
            //$accessdate = date('Y-m-d H:i:s');
            $date1 = new DateTime($accessdate);
            $dt = $date1->format('YmdHis');
            $tkn = $_POST['Token'];
            //$tkn = sha1($dt.$syscode);
            
            $result = $this->_unlockgenesis($_POST['TerminalCode'], $_POST['ServiceID'], $_POST['CardNumber'],$username,$accessdate,$tkn);
        }
        $this->render('unlockgenesis', array('result'=>$result));
    }
    private function _unlockgenesis($terminalCode, $serviceID, $cardNumber, $username, $accessdate, $tkn){
            $uri = Yii::app()->params['unlockgenesis'];
            $postdata = CJSON::encode(array('TerminalCode'=>$terminalCode, 'ServiceID'=>$serviceID,'CardNumber'=>$cardNumber,'SystemUsername'=>$username, 'AccessDate'=>$accessdate, 'Token'=>$tkn));
            
            $result = $this->SubmitData($uri, $postdata);
            return $result[1];
    }
    public function actionForceLogoutGen(){
        $this->pageTitle='Force Logout Genesis';
        $result = '';
        
        if(isset($_POST['Login']) && isset($_POST['SystemUsername']) && isset($_POST['AccessDate']) && isset($_POST['Token'])){
            $username = $_POST['SystemUsername'];
            $syscode = empty(Yii::app()->params['SystemCode'][$username])?'':Yii::app()->params['SystemCode'][$username];
            $accessdate = $_POST['AccessDate'];
            //$accessdate = date('Y-m-d H:i:s');
            $date1 = new DateTime($accessdate);
            $dt = $date1->format('YmdHis');
            $tkn = $_POST['Token'];
            //$tkn = sha1($dt.$syscode);
            $result = $this->_forceLogoutgen($_POST['Login'],$username,$accessdate,$tkn);
        }
        $this->render('forcelogoutgen',array('result'=>$result));
    }
    private function _forceLogoutgen($login, $username, $accessdate, $tkn){
        $uri = Yii::app()->params['forcelogoutgen'];
        $postdata = CJSON::encode(array('Login'=>$login,'SystemUsername'=>$username, 'AccessDate'=>$accessdate, 'Token'=>$tkn));

        $result = $this->SubmitData($uri, $postdata);
        return $result[1];
    }
    
    public function actionEsafeConversion(){
        $this->pageTitle = 'PCWS - e-SAFE Conversion';
        $result = '';
        if(isset($_POST['PIN']) && isset($_POST['CardNumber']) && isset($_POST['Password']) && isset($_POST['ConfirmPIN']) && isset($_POST['SystemUsername']) && isset($_POST['AccessDate']) && isset($_POST['Token'])){
            $cardnumber = $_POST['CardNumber'];
            $pin = $_POST['PIN'];
            $username = $_POST['SystemUsername'];
            $syscode = empty(Yii::app()->params['SystemCode'][$username])?'':Yii::app()->params['SystemCode'][$username];
            //$accessdate = date('Y-m-d H:i:s');
            $accessdate = $_POST['AccessDate'];
            $date1 = new DateTime($accessdate);
            $dt = $date1->format('YmdHis');
            //$tkn = sha1($dt.$syscode);
            $tkn = $_POST['Token'];
            $password = $_POST['Password'];
            $confirmpin = $_POST['ConfirmPIN'];
            
            $result = $this->_esafeconversion($cardnumber,$password,$pin,$confirmpin,$username,$accessdate,$tkn);
        }
        
        $this->render('esafeconversion', array('result'=>$result));
    }
    
    private function _esafeconversion($cardnumber,$password,$pin,$confirmpin,$username,$accessdate,$tkn){
        $url = Yii::app()->params['esafeconversion'];
        $postdata = CJSON::encode(array('CardNumber' => $cardnumber,'Password'=>$password,'PIN'=>$pin,'ConfirmPIN'=>$confirmpin,'SystemUsername'=>$username, 'AccessDate'=>$accessdate, 'Token'=>$tkn));
        $result = $this->SubmitData($url, $postdata);

        return $result[1];
    }
}
?>
