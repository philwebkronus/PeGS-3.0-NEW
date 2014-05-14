<?php
/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of WsStapiInvokerController
 *
 * @author jshernandez
 * @date 11-04-13 03:30:05 PM
 */
class WsStackerApiInvokerController extends Controller{
    
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
        $this->pageTitle = 'Stacker API - Overview';
        $this->render('overview');
    }
    
    public function actionLogStackerSession(){
        $this->pageTitle = 'Stacker API - Log Stacker Session';
        $result = '';
        
        if(isset($_POST['TerminalName']) || isset($_POST['SerialNumber']) || isset($_POST['Action']) || isset($_POST['CollectedBy'])){
            $terminalName = $_POST['TerminalName'];
            $serialNumber = $_POST['SerialNumber'];
            $action = $_POST['Action'];
            $collectedBy = $_POST['CollectedBy'];
                    
            $result = $this->_logStackerSession($terminalName, $serialNumber, $action, $collectedBy);
        }
        
        $this->render('logStackerSession', array('result'=>$result));
    }
    //Get Stacker Batch ID
    public function actionGetStackerBatchId(){
        $this->pageTitle = 'Stacker API - Get Stacker Batch ID';
        $result = '';
        
        if(isset($_POST['TerminalName']) || isset($_POST['MembershipCardNumber'])){
            $terminalName = $_POST['TerminalName'];
            $mcardnumber = $_POST['MembershipCardNumber'];
                    
            $result = $this->_getStackerBatchId($terminalName, $mcardnumber);
        }
        
        $this->render('getStackerBatchId', array('result'=>$result));
    }
        
    public function actionLogStackerTransaction(){
        $this->pageTitle = 'Stacker API - Log Stacker Transaction';
        $result = '';
        
        if(isset($_POST['TrackingID']) || isset($_POST['TerminalName']) || isset($_POST['TransType']) || isset($_POST['Amount'])
                || isset($_POST['CashType']) || isset($_POST['VoucherTicketBarcode']) || isset($_POST['Source']) || isset($_POST['StackerbatchID']) || isset($_POST['MembershipCardNumber'])){
            $trackingID = $_POST['TrackingID'];
            $terminalName = $_POST['TerminalName'];
            $transType = $_POST['TransType'];
            $amount = $_POST['Amount'];
            $cashType = $_POST['CashType'];
            $voucherCode = $_POST['VoucherTicketBarcode'];
            $source = $_POST['Source'];
            $stackerBatchID = $_POST['StackerBatchID'];
            $cardNumber = $_POST['MembershipCardNumber'];
                    
            $result = $this->_logStackerTransaction($trackingID, $terminalName, $transType, $amount, $cashType, $voucherCode, $source, $stackerBatchID, $cardNumber);
        }
        
        $this->render('logStackerTransaction', array('result'=>$result));
    }
        
    public function actionVerifyLogStackerTransaction(){
        $this->pageTitle = 'Stacker API - Verify Log Stacker Transaction';
        $result = '';
        
        if(isset($_POST['TrackingID'])){
            $trackingID = $_POST['TrackingID'];
                    
            $result = $this->_VerifyLogStackerTransaction($trackingID);
        }
        
        $this->render('verifyLogStackerTransaction', array('result'=>$result));
    }
    
    public function actionAddStackerInfo(){
        $this->pageTitle = 'Stacker API - Add Stacker Info';
        $result = '';
        
        if(isset($_POST['StackerTagID']) || isset($_POST['SerialNumber']) || isset($_POST['TerminalName'])){
            $stackerTagID = $_POST['StackerTagID'];
            $serialNumber = $_POST['SerialNumber'];
            $terminalName = $_POST['TerminalName'];
                    
            $result = $this->_addStackerInfo($stackerTagID, $serialNumber, $terminalName);
        }
        
        $this->render('addStackerInfo', array('result'=>$result));
    }
    
    public function actionUpdateStackerInfo(){
        $this->pageTitle = 'Stacker API - Update Stacker Info';
        $result = '';
        
        if(isset($_POST['StackerTagID']) || isset($_POST['SerialNumber']) || isset($_POST['Status']) || isset($_POST['TerminalName'])){
            $stackerTagID = $_POST['StackerTagID'];
            $serialNumber = $_POST['SerialNumber'];
            $status = $_POST['Status'];
            $terminalName = $_POST['TerminalName'];
                    
            $result = $this->_updateStackerInfo($stackerTagID, $serialNumber, $status, $terminalName);
        }
        
        $this->render('updateStackerInfo', array('result'=>$result));
    }
    
    public function actionGetStackerInfo(){
        $this->pageTitle = 'Stacker API - Get Stacker Info';
        $result = '';
        
        if(isset($_POST['StackerTagID']) || isset($_POST['SerialNumber'])){
            $stackerTagID = $_POST['StackerTagID'];
            $serialNumber = $_POST['SerialNumber'];
                    
            $result = $this->_getStackerInfo($stackerTagID, $serialNumber);
        }
        
        $this->render('getStackerInfo', array('result'=>$result));
    }
    
    public function actionCancelDeposit(){
        $this->pageTitle = 'Stacker API - Cancel Deposit';
        $result = '';
        
        if(isset($_POST['TrackingID']) || isset($_POST['StackerBatchID']) || isset($_POST['TerminalName'])){
            $trackingID = $_POST['TrackingID'];
            $stackerBatchID = $_POST['StackerBatchID'];
            $terminalName = $_POST['TerminalName'];
                    
            $result = $this->_cancelDeposit($trackingID, $stackerBatchID, $terminalName);
        }
        
        $this->render('cancelDeposit', array('result'=>$result));
    }
    
    public function actionUpdateStackerSummaryStatus (){
        $this->pageTitle = 'Stacker API - Update Stacker Summary Status ';
        $result = '';
        
        if(isset($_POST['TerminalName']) || isset($_POST['MembershipCardNumber']) || isset($_POST['TransType']) || isset($_POST['CasinoID']) || isset($_POST['AID'])) {
            $terminalName = $_POST['TerminalName'];
            $membershipCardNumber = $_POST['MembershipCardNumber'];
            $transType = $_POST['TransType'];
            $casinoID = $_POST['CasinoID'];
            $AID = $_POST['AID'];
                    
            $result = $this->_updateStackerSummaryStatus($terminalName, $membershipCardNumber, $transType, $casinoID, $AID);
        }
        
        $this->render('updateStackerSummaryStatus', array('result'=>$result));
    }
    
    private function _logStackerSession($terminalName, $serialNumber, $action, $collectedBy) {
        //$url = "http://localhost/stacker-mgmt/stapi-ws/index.php/wsStackerApi/logstackersession";
        $url = "http://stapi.dev.local/index.php/wsStackerApi/logstackersession";
        $postdata = CJSON::encode(array('TerminalName'=>$terminalName, 'SerialNumber'=>$serialNumber, 'Action'=>$action, 'CollectedBy'=>$collectedBy));
        $result = $this->SubmitData($url, $postdata);
        
        return $result[1];
    }
    
    private function _getStackerBatchId($terminalName, $mcardnumber) {
        //$url = "http://localhost/stacker-mgmt/stapi-ws/index.php/wsStackerApi/getstackerbatchid";
        $url = "http://stapi.dev.local/index.php/wsStackerApi/getstackerbatchid";
        $postdata = CJSON::encode(array('TerminalName'=>$terminalName, 'MembershipCardNumber'=>$mcardnumber));
        $result = $this->SubmitData($url, $postdata);
        
        return $result[1];
    }

    private function _logStackerTransaction($trackingID, $terminalName, $transType, $amount, $cashType, $voucherCode, $source, $stackerBatchID, $cardNumber) {
        //$url = "http://localhost/stacker-mgmt/stapi-ws/index.php/wsStackerApi/logstackertransaction";
        $url = "http://stapi.dev.local/index.php/wsStackerApi/logstackertransaction";
        $postdata = CJSON::encode(array('TrackingID'=>$trackingID, 'TerminalName'=>$terminalName, 'TransType'=>$transType, 'Amount'=>$amount,
                                  'CashType'=>$cashType, 'VoucherTicketBarcode'=>$voucherCode, 'Source'=>$source, 'StackerBatchID'=>$stackerBatchID, 'MembershipCardNumber'=>$cardNumber));
        $result = $this->SubmitData($url, $postdata);
        
        return $result[1];
    }
    
    private function _VerifyLogStackerTransaction($trackingID) {
        //$url = "http://localhost/stacker-mgmt/stapi-ws/index.php/wsStackerApi/verifylogstackertransaction";
        $url = "http://stapi.dev.local/index.php/wsStackerApi/verifylogstackertransaction";
        $postdata = CJSON::encode(array('TrackingID'=>$trackingID));
        $result = $this->SubmitData($url, $postdata);
        
        return $result[1];
    }
    
    private function _addStackerInfo($stackerTagID, $serialNumber, $terminalName) {
        //$url = "http://localhost/stacker-mgmt/stapi-ws/index.php/wsStackerApi/addstackerinfo";
        $url = "http://stapi.dev.local/index.php/wsStackerApi/addstackerinfo";
        $postdata = CJSON::encode(array('StackerTagID'=>$stackerTagID, 'SerialNumber'=>$serialNumber, 'TerminalName'=>$terminalName));

        $result = $this->SubmitData($url, $postdata);
        
        return $result[1];
    }
    
    private function _updateStackerInfo($stackerTagID, $serialNumber, $status, $terminalName) {
        //$url = "http://localhost/stacker-mgmt/stapi-ws/index.php/wsStackerApi/updatestackerinfo";
        $url = "http://stapi.dev.local/index.php/wsStackerApi/updatestackerinfo";
        $postdata = CJSON::encode(array('StackerTagID'=>$stackerTagID, 'SerialNumber'=>$serialNumber, 'Status'=>$status, 'TerminalName'=>$terminalName));

        $result = $this->SubmitData($url, $postdata);
        
        return $result[1];
    }
    
    private function _getStackerInfo($stackerTagID, $serialNumber){
        //$url = "http://localhost/stacker-mgmt/stapi-ws/index.php/wsStackerApi/getstackerinfo";
        $url = "http://stapi.dev.local/index.php/wsStackerApi/getstackerinfo";
        $postdata = CJSON::encode(array('StackerTagID'=>$stackerTagID, 'SerialNumber'=>$serialNumber));

        $result = $this->SubmitData($url, $postdata);
        
        return $result[1];
    }
    
    private function _cancelDeposit($trackingID, $stackerBatchID, $terminalName) {
        //$url = "http://localhost/stacker-mgmt/stapi-ws/index.php/wsStackerApi/canceldeposit";
        $url = "http://stapi.dev.local/index.php/wsStackerApi/canceldeposit";
        $postdata = CJSON::encode(array('TrackingID'=>$trackingID, 'StackerBatchID'=>$stackerBatchID, 'TerminalName'=>$terminalName));

        $result = $this->SubmitData($url, $postdata);
        
        return $result[1];
    }
    
    private function _updateStackerSummaryStatus($terminalName, $membershipCardNumber, $transType, $casinoID, $AID) {
        //$url = "http://localhost/stacker-mgmt/stapi-ws/index.php/wsStackerApi/updatestackersummarystatus";
        $url = "http://stapi.dev.local/index.php/wsStackerApi/updatestackersummarystatus";
        $postdata = CJSON::encode(array('TerminalName'=>$terminalName, 'MembershipCardNumber'=>$membershipCardNumber, 'TransType'=>$transType, 'CasinoID'=>$casinoID, 'AID'=>$AID));

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
}

?>
