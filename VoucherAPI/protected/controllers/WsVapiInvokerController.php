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
class WsVapiInvokerController extends Controller{
    
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
        $this->pageTitle = 'VAPI - Overview';
        $this->render('overview');
    }
    
        
    public function actionVerify(){
        $this->pageTitle = 'VAPI - Verify Coupon';
        $result = '';
        
        if(isset($_POST['vouchercode']) || isset($_POST['aid']) || isset($_POST['trackingid']) 
           || isset($_POST['source'])){
            $trackingID = $_POST['trackingid'];
            $terminalName = $_POST['terminalname'];
            $voucherCode = $_POST['vouchercode'];
            $source = $_POST['source'];
            $aid = $_POST['aid'];
            $result = $this->_verify($trackingID, $terminalName, $voucherCode, $source, $aid);
        } 
        $this->render('verify', array('result'=>$result));
    }

    private function _verify($trackingID, $terminalName, $voucherCode, $source, $aid){
        $url = Yii::app()->params['verifyCoupon'];
        //$url = "localhost/voucher-api/index.php/Wsvoucher/verify";
        
        $postdata = CJSON::encode(array('trackingid'=>$trackingID, 'terminalname'=>$terminalName,'vouchercode'=>$voucherCode, 'source'=>$source, 'aid'=>$aid));
        
        $result = $this->SubmitData($url, $postdata);
        
        return $result[1];
    }
        
    public function actionUse(){
        $this->pageTitle = 'VAPI - Use Coupon';
        $result = '';
        
        if(isset($_POST['trackingid']) || isset($_POST['terminalid']) || isset($_POST['voucherticketbarcode']) || isset($_POST['source']) || isset($_POST['aid'])){
                $trackingid = $_POST['trackingid'];
                $terminalid = $_POST['terminalid'];
                $voucherticketbarcode = $_POST['voucherticketbarcode'];
                $source = $_POST['source'];
                $aid = $_POST['aid'];
                $mid = '';
                if(isset($_POST['mid'])){
                    $mid = $_POST['mid'];
                }
                
                $result = $this->_use($trackingid, $terminalid, $voucherticketbarcode, $source, $aid, $mid);
                }
                
        $this->render('use', array('result'=>$result));
    }
    
    private function _use($trackingid, $terminalid, $voucherticketbarcode, $source, $aid, $mid){
        $url = Yii::app()->params['use'];
        //$url = "localhost/voucher.api/index.php/Wsvoucher/use";
        $postdata = CJSON::encode(array('TrackingID'=>$trackingid, 'TerminalID'=>$terminalid, 'VoucherTicketBarcode'=>$voucherticketbarcode, 'Source'=>$source, 'AID'=>$aid, 'MID'=>$mid));
        $result = $this->SubmitData($url, $postdata);
        return $result[1];
    }
    public function actionVerifyTicket(){
        $this->pageTitle = 'VAPI - Verify Ticket';
        $result = '';
        
        if(isset($_POST['TrackingID']) || isset($_POST['TerminalName']) || isset($_POST['VoucherTicketBarcode']) 
           || isset($_POST['Source']) || isset($_POST['AID']) || isset($_POST['MembershipCardNumber'])){
            $trackingID = $_POST['TrackingID'];
            $terminalName = $_POST['TerminalName'];
            $voucherCode = $_POST['VoucherTicketBarcode'];
            $source = $_POST['Source'];
            $aid = $_POST['AID'];
            $cardNumber = $_POST['MembershipCardNumber'];
            $result = $this->_verifyTicket($trackingID, $terminalName, $voucherCode, $source, $aid, $cardNumber);
        } 
        $this->render('verifyTicket', array('result'=>$result));
    }
    
    private function _verifyTicket($trackingID, $terminalName, $voucherCode, $source, $aid, $cardNumber){
        $url = Yii::app()->params['verifyTicket'];
        //$url = "localhost/voucher-api/index.php/Wsvoucher/verifyTicket";
        
        $postdata = CJSON::encode(array('TrackingID'=>$trackingID, 'TerminalName'=>$terminalName,
                                        'VoucherTicketBarcode'=>$voucherCode, 'Source'=>$source, 'AID'=>$aid,
                                        'MembershipCardNumber'=>$cardNumber));
        $result = $this->SubmitData($url, $postdata);
        
        return $result[1];
    }
    
    public function actionAddTicket(){
        $this->pageTitle = 'VAPI - Add Ticket';
        $result = '';
        
        if(isset($_POST['trackingid']) || isset($_POST['terminalname'])|| isset($_POST['cardnumber']) || isset($_POST['amount']) || isset($_POST['source']) || isset($_POST['aid']) || isset($_POST['purpose']) || isset($_POST['stackerbatchid'])  || isset($_POST['voucherticketbarcode'])){
                $trackingid = $_POST['trackingid'];
                $terminalname = $_POST['terminalname'];
                $cardnumber = $_POST['cardnumber'];
                $amount = $_POST['amount'];
                $source = $_POST['source'];
                $aid = $_POST['aid'];
                $purpose = $_POST['purpose'];
                $stackerbatchid = $_POST['stackerbatchid'];
                $vouchercode = $_POST['voucherticketbarcode'];
                $result = $this->_addTicket($trackingid, $terminalname, $cardnumber, $amount, $source, $aid, $purpose, $stackerbatchid, $vouchercode);
                }

        $this->render('addTicket', array('result'=>$result));
    }

    private function _addTicket($trackingid, $terminalname, $cardnumber, $amount, $source, $aid, $purpose, $stackerbatchid, $vouchercode){
        $url = Yii::app()->params['addTicket'];
        //$url = "localhost/voucher-api/index.php/Wsvoucher/addTicket";
        $postdata = CJSON::encode(array('TrackingID'=>$trackingid, 'TerminalName'=>$terminalname, 'MembershipCardNumber'=>$cardnumber, 'Amount'=>$amount, 'Source'=>$source, 'AID'=>$aid, 'Purpose'=>$purpose, 'StackerBatchID'=>$stackerbatchid, 'VoucherTicketBarcode'=>$vouchercode));
        $result = $this->SubmitData($url, $postdata);
        return $result[1];
    }
    
        
    public function actionUseTicket(){
        $this->pageTitle = 'VAPI - Use Ticket';
        $result = '';
        
        if(isset($_POST['trackingid']) || isset($_POST['terminalname']) || isset($_POST['voucherticketbarcode']) || isset($_POST['source']) || isset($_POST['aid'])  || isset($_POST['cardnumber']) || isset($_POST['Amount'])){
                $trackingid = $_POST['trackingid'];
                $terminalname = $_POST['terminalname'];
                $voucherticketbarcode = $_POST['voucherticketbarcode'];
                $source = $_POST['source'];
                $aid = $_POST['aid'];
                $cardnumber = $_POST['cardnumber'];
                $amount = $_POST['amount'];
                
                $result = $this->_useTicket($trackingid, $terminalname, $voucherticketbarcode, $source, $aid, $cardnumber, $amount);
                }
                
        $this->render('useTicket', array('result'=>$result));
    }

    private function _useTicket($trackingid, $terminalname, $voucherticketbarcode, $source, $aid, $cardnumber, $amount){
        $url = Yii::app()->params['useTicket'];
        //$url = "localhost/voucher.api/index.php/Wsvoucher/useTicket";
        $postdata = CJSON::encode(array('TrackingID'=>$trackingid, 'TerminalName'=>$terminalname, 'VoucherTicketBarcode'=>$voucherticketbarcode, 'Source'=>$source, 'AID'=>$aid, 'MembershipCardNumber'=>$cardnumber, 'Amount'=>$amount));
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
	    // CCT ADDED 01/07/2019 BEGIN
	    // FORCE TLS 1.2
	    curl_setopt($curl, CURLOPT_SSLVERSION, 6 );
            // CCT ADDED 01/07/2019 END
            $response = curl_exec( $curl );

            $http_status = curl_getinfo( $curl, CURLINFO_HTTP_CODE );

            curl_close( $curl );

            return array( $http_status, $response );
    }
}

?>
