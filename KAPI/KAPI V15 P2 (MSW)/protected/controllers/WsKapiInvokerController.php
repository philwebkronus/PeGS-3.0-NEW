<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of WsKapiInvokerController
 *
 * @author elperez
 */
class WsKapiInvokerController extends Controller{
    
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
        $this->pageTitle = 'KAPI - Overview';
        $this->render('overview');
    }
    
    public function actionGetTerminalInfo(){
        $this->pageTitle = 'KAPI - Get Terminal Info';
        $result = '';
        
        if(isset($_POST['terminalname'])){
            $terminalname = $_POST['terminalname'];
            
            $result = $this->_getTerminalInfo($terminalname);
        }
        
        $this->render('getterminalinfo', array('result'=>$result));
    }
    
    public function actionGetPlayingBalance(){
        $this->pageTitle = 'KAPI - Get Playing Balance';
        $result = '';
        
        if(isset($_POST['terminalname'])){
            $terminalname = $_POST['terminalname'];
            
            $result = $this->_getPlayingBalance($terminalname);
        }
        
        $this->render('getplayingbalance', array('result'=>$result));
    }
    
    public function actionGetMembershipInfo(){
        $this->pageTitle = 'KAPI - Get Membership Info';
        $result = '';
        
        //if(isset($_POST['terminalname']) || isset($_POST['cardnumber'])){
        if(isset($_POST['cardnumber'])){
            //$terminalname = $_POST['terminalname'];
            $cardnumber = $_POST['cardnumber'];
            
            //$result = $this->_getMemebershipInfo($terminalname, $cardnumber);
            $result = $this->_getMemebershipInfo($cardnumber);
        }
        
        $this->render('getmembershipinfo', array('result'=>$result));
    }
    
    public function actionCheckTransaction(){
        $this->pageTitle = 'KAPI - Check Transaction';
        $result = '';
        
        if(isset($_POST['terminalname']) || isset($_POST['trackingid'])){
            $terminalname = $_POST['terminalname'];
            $trackingid = $_POST['trackingid'];
            
            $result = $this->_checkTransaction($terminalname, $trackingid);
        }
        
        $this->render('checktransaction', array('result'=>$result));
    }
    
    public function actionGetLoginInfo(){
        $this->pageTitle = 'KAPI - Get Login Info';
        $result = '';
        
        if(isset($_POST['terminalname']) || isset($_POST['casinoid'])){
            $terminalname = $_POST['terminalname'];
            $casinoID = $_POST['casinoid'];
            
            $result = $this->_getLoginInfo($terminalname, $casinoID);
        }
        
        $this->render('getlogininfo', array('result'=>$result));
    }
    
    private function _getTerminalInfo($terminalName){
        $url = Yii::app()->params['get_terminal_info'];
        $postdata = CJSON::encode(array('TerminalName'=>$terminalName));
        
        $result = $this->SubmitData($url, $postdata);
        
        return $result[1];
    }
    
   private function _getPlayingBalance($terminalName){
        $url = Yii::app()->params['get_playing_balance'];
        $postdata = CJSON::encode(array('TerminalName'=>$terminalName));
       
        $result = $this->SubmitData($url, $postdata);
        
        return $result[1];
    }
    
    //private function _getMemebershipInfo($terminalName, $CardNumber){
    private function _getMemebershipInfo($CardNumber){
        $url = Yii::app()->params['get_membership_info'];
//        $postdata = CJSON::encode(array('TerminalName'=>$terminalName, 'MembershipCardNumber'=>$CardNumber));
        $postdata = CJSON::encode(array('MembershipCardNumber'=>$CardNumber));
       
        $result = $this->SubmitData($url, $postdata);
        
        return $result[1];
    }
    
    private function _checkTransaction($terminalName, $trackingID){
        $url = Yii::app()->params['check_transaction'];
        $postdata = CJSON::encode(array('TerminalName'=>$terminalName, 'TrackingID'=>$trackingID));
       
        $result = $this->SubmitData($url, $postdata);
        
        return $result[1];
    }
    
    private function _getLoginInfo($terminalName, $casinoID){
        $url = Yii::app()->params['get_login_info'];
        $postdata = CJSON::encode(array('TerminalName'=>$terminalName, 'CasinoID'=>$casinoID));
       
        $result = $this->SubmitData($url, $postdata);
        
        return $result[1];
    }
    
    private function _createEgmSession($membershipcardnumber, $terminalName, $casinoID){
        $url = Yii::app()->params['create_egm_session'];
        //$url = "http://localhost/kronus-egm-ws-abbott/index.php/wsKapi/createegmsession";
        $postdata = CJSON::encode(array('MembershipCardNumber'=>$membershipcardnumber,'TerminalName'=>$terminalName, 'CasinoID'=>$casinoID));
       
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
            // Data+Files to be posted
            curl_setopt($curl, CURLOPT_POSTFIELDS, $postdata);
            $response = curl_exec( $curl );

            $http_status = curl_getinfo( $curl, CURLINFO_HTTP_CODE );

            curl_close( $curl );

            return array( $http_status, $response );
    }
    
    public function actionStartSession(){
        $this->pageTitle = 'KAPI - Start Session';
        $result = '';
        
        if(isset($_POST['casinoid']) && isset($_POST['terminalname']) && isset($_POST['playermode']) 
                && isset($_POST['cardnumber']) && isset($_POST['amount']) && isset($_POST['trackingid'])
                && isset($_POST['stackerbatchid'])){
            
            $casinoid = $_POST['casinoid'];
            $terminalname = $_POST['terminalname'];
            $playermode = $_POST['playermode'];
            $cardnumber = $_POST['cardnumber'];
            $amount = $_POST['amount'];
            $tracking = $_POST['trackingid'];
            $stackerbatchid = $_POST['stackerbatchid'];
            
            $result = $this->_startSesssion($casinoid, $terminalname, $playermode, $cardnumber,
                    $amount, $tracking, $stackerbatchid);
        }
        
        $this->render('startsession', array('result'=>$result));
    }
    
    
    public function actionReloadSession(){
        $this->pageTitle = 'KAPI - Reload Session';
        $result = '';
        
        if(isset($_POST['terminalname']) && isset($_POST['amount']) && isset($_POST['trackingid']) && isset($_POST['stackerbatchid'])){
            
            $terminalname = $_POST['terminalname'];
            $amount = $_POST['amount'];
            $tracking = $_POST['trackingid'];
            $stackerbatchid = $_POST['stackerbatchid'];
            
            $result = $this->_reloadSesssion($terminalname, $amount, $tracking, $stackerbatchid);
        }
        
        $this->render('reloadsession', array('result'=>$result));
    }
    
    
    public function actionRedeemSession(){
        $this->pageTitle = 'KAPI - Redeem Session';
        $result = '';
        
        if(isset($_POST['terminalname']) && isset($_POST['trackingid']) && isset($_POST['stackerbatchid'])){
            
            $terminalname = $_POST['terminalname'];
            $tracking = $_POST['trackingid'];
            $stackerBatchID = $_POST['stackerbatchid'];
            
            $result = $this->_redeemSesssion($terminalname, $tracking, $stackerBatchID);
        }
        
        $this->render('redeemsession', array('result'=>$result));
    }
    
    public function actionCreateEgmSession(){
        $this->pageTitle = 'KAPI - Create EGM Session';
        $result = '';
        
        if(isset($_POST['membershipcardnumber']) && isset($_POST['terminalname']) 
                && isset($_POST['serviceid'])){
            
            $membershipcardnumber = $_POST['membershipcardnumber'];
            $terminalname = $_POST['terminalname'];
            $serviceid = $_POST['serviceid'];
            
            $result = $this->_createEgmSession($membershipcardnumber, $terminalname, $serviceid);
        }
        
        $this->render('createegmsession', array('result'=>$result));
    }
    
    public function actionStartSpyder(){
        Yii::import('application.components.Spyder');
        
        $spyder = new Spyder();
        
        $spyder->runAction();
    }
    
    
    private function _startSesssion($casinoID, $terminalName, $playerMode, 
            $cardNumber, $amount, $trackingID, $stackerbatchid){
        $url = Yii::app()->params['start_session'];
        //$url = "http://localhost/kronus-egm-ws-abbott/index.php/wsKapi/startsession";
        
        $postdata = CJSON::encode(array('CasinoID'=>$casinoID,'TerminalName'=>$terminalName,
            'PlayerMode'=>$playerMode,'MembershipCardNumber'=>$cardNumber,
            'Amount'=>$amount,'TrackingID'=>$trackingID, 'StackerBatchID'=>$stackerbatchid));
       
        $result = $this->SubmitData($url, $postdata);
        
        return $result[1];
    }
    
    private function _reloadSesssion($terminalName, $amount, $trackingID, $stackerbatchID){
        $url = Yii::app()->params['reload_session'];
        //$url = "http://localhost/kronus-egm-ws-abbott/index.php/wsKapi/reloadsession";
        
        $postdata = CJSON::encode(array('TerminalName'=>$terminalName,
            'Amount'=>$amount,'TrackingID'=>$trackingID, 'StackerBatchID'=>$stackerbatchID));
       
        $result = $this->SubmitData($url, $postdata);
        
        return $result[1];
    }
    
    
    private function _redeemSesssion($terminalName, $trackingID, $stackerBatchID){
        $url = Yii::app()->params['redeem_session'];
        //$url = "http://localhost/kronus-egm-ws-abbott/index.php/wsKapi/redeemsession";
        
        $postdata = CJSON::encode(array('TerminalName'=>$terminalName,'TrackingID'=>$trackingID, 'StackerBatchID'=>$stackerBatchID));
       
        $result = $this->SubmitData($url, $postdata);
        
        return $result[1];
    }
    
    //Remove Egm Session Invoker Controller
    public function actionRemoveegmsession(){
        $this->pageTitle = 'KAPI - Remove EGM Session';
        $result = '';
        
        if(isset($_POST['membershipcardnumber']) && isset($_POST['terminalname']) 
                && isset($_POST['serviceid'])){
            
            $membershipcardnumber = $_POST['membershipcardnumber'];
            $terminalname = $_POST['terminalname'];
            $serviceid = $_POST['serviceid'];
            
            $result = $this->_removeEgmSession($membershipcardnumber, $terminalname, $serviceid);
        }
        
        $this->render('removeegmsession', array('result'=>$result));
    }
    private function _removeEgmSession($membershipcardnumber, $terminalName, $casinoID){
        $url = Yii::app()->params['remove_egm_session'];
        //$url = "http://localhost/kronus-egm-ws-abbott/index.php/wsKapi/removeegmsession";
        $postdata = CJSON::encode(array('MembershipCardNumber'=>$membershipcardnumber,
                                        'TerminalName'=>$terminalName, 
                                        'CasinoID'=>$casinoID));
       
        $result = $this->SubmitData($url, $postdata);
        
        return $result[1];
    }
    
    public function actionGetSiteBalance() 
    {
        $this->pageTitle = "KAPI - Get Site Balance";
        $result = '';
        
        if(isset($_POST['txtsitecode'])){
            
            $sitecode = $_POST['txtsitecode'];
            
            $result = $this->_getSiteBalance($sitecode);
        }
        
        $this->render('getsitebalance', array('result' => $result));
    }
    
    private function _getSiteBalance($sitecode)
    {
        //$url = "http://localhost/kronus-egm-ws-abbott/index.php/wsKapi/getsitebalance";
        $url = Yii::app()->params['get_site_balance'];
        $postdata = CJSON::encode(array('SiteCode'=>$sitecode
                                  ));
       
        $result = $this->SubmitData($url, $postdata);
        
        return $result[1];
    }
    /**
     * e-SAFE Reload Genesis
     */
    public function actionEsafegenesisreload(){
        $this->pageTitle = "e-SAFE Reload Genesis";
        $result = '';
        
        if(isset($_POST['terminalname']) && isset($_POST['amount']) && isset($_POST['trackingid']) && isset($_POST['stackerbatchid'])){
            
            $terminalname = $_POST['terminalname'];
            $amount = $_POST['amount'];
            $tracking = $_POST['trackingid'];
            $stackerbatchid = $_POST['stackerbatchid'];
            
            $result = $this->_eSAFEReloadGen($terminalname, $amount, $tracking, $stackerbatchid);
        }
        $this->render('esafereloadgen', array('result' => $result));
    }
    
    private function _eSAFEReloadGen($terminalName, $amount, $trackingID, $stackerbatchID) {
        $url = Yii::app()->params['esafe_reload_genesis'];
        
        $postdata = CJSON::encode(array('TerminalName'=>$terminalName,
            'Amount'=>$amount,'TrackingID'=>$trackingID, 'StackerBatchID'=>$stackerbatchID));
       
        $result = $this->SubmitData($url, $postdata);

        return $result[1];
    }
    public function actionEsafegenesisredemption(){
        $this->pageTitle = 'KAPI - e-SAFE Genesis Redemption';
        $result = '';
        
        if(isset($_POST['terminalname']) && isset($_POST['trackingid']) && isset($_POST['stackerbatchid'])){
            
            $terminalname = $_POST['terminalname'];
            $tracking = $_POST['trackingid'];
            $stackerBatchID = $_POST['stackerbatchid'];
            $amount = $_POST['amount'];
            
            $result = $this->_eSAFERedemptionGen($terminalname, $amount, $tracking, $stackerBatchID);
        }
        
        $this->render('esafegenredemption', array('result'=>$result));
    }
    
    private function _eSAFERedemptionGen($terminalName, $amount, $trackingID, $stackerbatchID) {
        $url = Yii::app()->params['esafe_redemption_genesis'];
        
        $postdata = CJSON::encode(array('TerminalName'=>$terminalName,
            'Amount'=>$amount,'TrackingID'=>$trackingID, 'StackerBatchID'=>$stackerbatchID));
       
        $result = $this->SubmitData($url, $postdata);

        return $result[1];
    }
    
    //@author: ralph sison
    //@dateadded: 12-22-2015
    public function actionGetbalancemsw() {
        $this->pageTitle = 'KAPI - e-SAFE Get Balance MSW';
        $result = '';
        
        if(isset($_POST['MID']) && isset($_POST['ServiceID'])){
            
            $mid = $_POST['MID'];
            $serviceID = $_POST['ServiceID'];
            
            $result = $this->_getBalanceMSW($mid, $serviceID);
        }
        
        $this->render('getbalancemsw', array('result'=>$result));
    }
    
    private function _getBalanceMSW($mid, $serviceID) {
        $url = Yii::app()->params['get_balance_msw'];
        
        $postdata = CJSON::encode(array('MID'=>$mid, 'ServiceID' => $serviceID));
        
        $result = $this->SubmitData($url, $postdata);

        return $result[1];
    }
    
    //@dateadded: 12-28-2015
    public function actionDepositMSW() {
        $this->pageTitle = 'KAPI - e-SAFE Deposit MSW';
        $result = '';
        
        if(isset($_POST['MID']) && isset($_POST['ServiceID']) && isset($_POST['Amount'])){
            
            $mid = $_POST['MID'];
            $serviceID = $_POST['ServiceID'];
            $amount = $_POST['Amount'];
            
            $result = $this->_depositMSW($mid, $serviceID, $amount);
        }
        
        $this->render('depositmsw', array('result'=>$result));
    }
    
    private function _depositMSW($mid, $serviceID, $amount) {
        $url = Yii::app()->params['deposit_msw'];
        
        $postdata = CJSON::encode(array('MID'=>$mid, 'ServiceID' => $serviceID, 'Amount' => $amount));
       
        $result = $this->SubmitData($url, $postdata);

        return $result[1];
    }

    public function actionWithdrawMSW() {
        $this->pageTitle = 'KAPI - e-SAFE Withdraw MSW';
        $result = '';
        
        if(isset($_POST['MID']) && isset($_POST['ServiceID']) && isset($_POST['Amount'])){
            
            $mid = $_POST['MID'];
            $serviceID = $_POST['ServiceID'];
            $amount = $_POST['Amount'];
            
            $result = $this->_withdrawMSW($mid, $serviceID, $amount);
        }
        
        $this->render('withdrawmsw', array('result'=>$result));
    }
    
    private function _withdrawMSW($mid, $serviceID, $amount) {
        $url = Yii::app()->params['withdraw_msw'];
        
        $postdata = CJSON::encode(array('MID'=>$mid, 'ServiceID' => $serviceID, 'Amount' => $amount));
       
        $result = $this->SubmitData($url, $postdata);

        return $result[1];
    }
}
