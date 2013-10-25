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
    
    public function actionVoucherTicketValidation(){
        $this->pageTitle = 'VAPI - Voucher Ticket Validation';
        $result = '';
        
        if(isset($_POST['trackingid']) || isset($_POST['terminalname']) || isset($_POST['voucherticketbarcode']) || isset($_POST['source']) || isset($_POST['aid'])){
                $trackingid = $_POST['trackingid'];
                $terminalname = $_POST['terminalname'];
                $voucherticketbarcode = $_POST['voucherticketbarcode'];
                $source = $_POST['source'];
                $aid = $_POST['aid'];
                $result = $this->_voucherTicketValidation($trackingid, $terminalname, $voucherticketbarcode, $source, $aid);
                }

        $this->render('voucherTicketValidation', array('result'=>$result));
    }
    
    public function actionGetVoucherTicket(){
        $this->pageTitle = 'VAPI - Get Voucher Ticket';
        $result = '';
        
        if(isset($_POST['trackingid']) || isset($_POST['terminalname']) || isset($_POST['amount']) || isset($_POST['source']) || isset($_POST['aid'])){
                $trackingid = $_POST['trackingid'];
                $terminalname = $_POST['terminalname'];
                $amount = $_POST['amount'];
                $source = $_POST['source'];
                $aid = $_POST['aid'];
                $result = $this->_getVoucherTicket($trackingid, $terminalname, $amount, $source, $aid);
                }

        $this->render('getVoucherTicket', array('result'=>$result));
    }
    
    public function actionUseVoucher(){
        $this->pageTitle = 'VAPI - Use Voucher';
        $result = '';
        
        if(isset($_POST['trackingid']) || isset($_POST['terminalname']) || isset($_POST['voucherticketbarcode']) || isset($_POST['source']) || isset($_POST['aid'])){
                $trackingid = $_POST['trackingid'];
                $terminalname = $_POST['terminalname'];
                $voucherticketbarcode = $_POST['voucherticketbarcode'];
                $source = $_POST['source'];
                $aid = $_POST['aid'];
                $result = $this->_useVoucher($trackingid, $terminalname, $voucherticketbarcode, $source, $aid);
                }
                
        $this->render('useVoucher', array('result'=>$result));
    }
    
    public function actionVerifyVoucher(){
        $this->pageTitle = 'VAPI - Verify Voucher';
        $result = '';
        
        if(isset($_POST['vouchercode']) && isset($_POST['aid']) && isset($_POST['trackingid']) 
           && isset($_POST['source'])){
            $voucherCode = $_POST['vouchercode'];
            $aid = $_POST['aid'];
            $trackingID = $_POST['trackingid'];
            $source = $_POST['source'];
            
            $result = $this->_verifyVoucher($voucherCode, $aid, $trackingID, $source);
        } 
        
        $this->render('verifyvoucher', array('result'=>$result));
    }
    
    private function _verifyVoucher($voucherCode, $aid, $trackingID, $source){
        $url = "http://localhost/vms/index.php/wsvoucher/verify";
        $postdata = CJSON::encode(array('trackingid'=>$trackingID, 'vouchercode'=>$voucherCode, 'aid'=>$aid, 'source'=>$source));
        $result = $this->SubmitData($url, $postdata);
        return $result[1];
    }
    
    private function _voucherTicketValidation($trackingid, $terminalname, $voucherticketbarcode, $source, $aid){
        //$url = Yii::app()->params['authenticate_client'];
        $url = "http://localhost/vouchermanagementsystem/VMS/index.php/Wsvoucher/voucherTicketValidation";
        $postdata = CJSON::encode(array('TrackingID'=>$trackingid, 'TerminalName'=>$terminalname, 'VoucherTicketBarcode'=>$voucherticketbarcode, 'Source'=>$source, 'AID'=>$aid));
        $result = $this->SubmitData($url, $postdata);
        return $result[1];
    }
    
    private function _getVoucherTicket($trackingid, $terminalname, $amount, $source, $aid){
        //$url = Yii::app()->params['authenticate_client'];
        $url = "http://localhost/vouchermanagementsystem/VMS/index.php/Wsvoucher/getVoucherTicket";
        $postdata = CJSON::encode(array('TrackingID'=>$trackingid, 'TerminalName'=>$terminalname, 'Amount'=>$amount, 'Source'=>$source, 'AID'=>$aid));
        $result = $this->SubmitData($url, $postdata);
        return $result[1];
    }
    
    private function _useVoucher($trackingid, $terminalname, $voucherticketbarcode, $source, $aid){
        //$url = Yii::app()->params['authenticate_client'];
        $url = "http://localhost/vouchermanagementsystem/VMS/index.php/Wsvoucher/useVoucher";
        $postdata = CJSON::encode(array('TrackingID'=>$trackingid, 'TerminalName'=>$terminalname, 'VoucherTicketBarcode'=>$voucherticketbarcode, 'Source'=>$source, 'AID'=>$aid));
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
