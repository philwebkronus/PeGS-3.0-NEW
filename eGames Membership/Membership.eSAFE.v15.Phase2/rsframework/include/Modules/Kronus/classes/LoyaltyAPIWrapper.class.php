<?php

/*
 * @Author Joene Floresca
 * @Desc Wrapper for PCWS API
 * @Date 02-02-2015
 */

Class LoyaltyAPIWrapper extends BaseEntity {

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

    /*
     * Required variables to call PCWS API 
     */
    private $_accessdate;
    private $_dt;
    private $_tkn;
    private $_username;
    private $_logger;

    public function __construct() {
        //App::getParam('pcws_getComppoints');
        App::LoadCore("Logger.class.php");

        $this->_accessdate = date("Y-m-d H:i:s");
        $this->_dt2 = new DateTime($this->_accessdate);
        $this->_dt = $this->_dt2->format('YmdHis');
        $this->_username = array('madmin', 'mportal');
        $this->_tkn[$this->_username[0]] = sha1($this->_dt . '4896816');
        $this->_tkn[$this->_username[1]] = sha1($this->_dt . '48452098');

        $this->_logger = new Logger();
        $this->_logger->setPrefix("Pcws");
    }

    public function curlApi($uri, $postdata) {
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

    public function getCompPoints($CardNumber, $index) {
        $postdata = json_encode(array('CardNumber' => $CardNumber, 'SystemUsername' => $this->_username[$index], 'AccessDate' => $this->_accessdate,
            'Token' => $this->_tkn[$this->_username[$index]], 'ServiceID' => App::getParam('serviceid')));
        $this->_logger->_logRequest("GetCompPoints", $postdata);
        $result = $this->curlApi(App::getParam('getcomppoints'), $postdata);
        $this->_logger->_logResponse("GetCompPoints", $result[1]);

        return json_decode($result[1], true);
    }

    public function getCardInfo($card_number, $return_transfer = false, $isReg = 0, $siteid = '') {

        $postdata = json_encode(array('cardnumber' => $card_number, 'isreg' => $isReg, 'siteid' => $siteid));
        $this->_logger->_logRequest("GetCardInfo", $postdata);
        $result = $this->curlApi(App::getParam('GetCardInfo'), $postdata);
        $this->_logger->_logResponse("GetCardInfo", $result[1]);

        return json_decode($result[1], true);
    }

    public function transferPoints($oldnumber, $newnumber, $aid, $return_transfer = false) {

        $postdata = json_encode(array('oldnumber' => $oldnumber, 'newnumber' => $newnumber, 'aid' => $aid));
        $this->_logger->_logRequest("GetCardInfo", $postdata);
        $result = $this->curlApi(App::getParam('GetCardInfo'), $postdata);
        $this->_logger->_logResponse("GetCardInfo", $result[1]);

        return json_decode($result[1], true);
    }

    public function processPoints($card_number, $transdate, $transtype, $amount, $site_id, $iscreditable, $vouchercode = '', $service_id = 7, $return_transfer = false) {

        $card_number = urlencode(trim($card_number));
        $transid = 0;
        $transdate = urlencode(trim($transdate));
        $transtype = urlencode(trim($transtype));
        $payment_type = 1;
        $amount = urlencode(trim($amount));
        $site_id = urlencode(trim($site_id));
        $service_id = App::getParam('serviceid');
        $terminal_login = 0;
        $iscreditable = urlencode(trim($iscreditable));
        $vouchercode = urlencode(trim($vouchercode));
        
        $ch = curl_init();
        curl_setopt($ch,CURLOPT_URL, App::getParam('processpoints') . '?cardnumber=' . $card_number . '&transactionid=' . $transid . '&transdate=' . $transdate .
                '&transtype=' . $transtype . '&paymenttype=' . $payment_type. '&amount=' . $amount . '&siteid=' . $site_id .
                '&serviceid='  . $service_id  . '&terminallogin=' . $terminal_login . '&iscreditable=' . $iscreditable .
                '&vouchercode=' . $vouchercode);

//        
//        curl_setopt( $ch, CURLOPT_FRESH_CONNECT, FALSE );
//        curl_setopt( $ch, CURLOPT_CONNECTTIMEOUT, 10 );
//        curl_setopt( $ch, CURLOPT_TIMEOUT, 500 );
//        curl_setopt( $ch, CURLOPT_USERAGENT, 'PEGS Station Manager' );
//        curl_setopt( $ch, CURLOPT_SSL_VERIFYHOST, FALSE );
//        curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, FALSE );
//        curl_setopt( $ch, CURLOPT_POST, TRUE);
//        curl_setopt( $ch, CURLOPT_HTTPHEADER, array( 'Content-Type: application/json' ) );
//        curl_setopt( $ch, CURLOPT_RETURNTRANSFER, TRUE);
//        curl_setopt( $ch, CURLOPT_SSLVERSION, 3 );
        
        $result = curl_exec( $ch );
        $http_status = curl_getinfo( $ch, CURLINFO_HTTP_CODE );
        curl_close( $ch );

        $isSuccessful = json_decode($result, true);

        if ($isSuccessful['AddPoints']['StatusCode']==1) {
            return true;
        } else {
            return false;
        }
    
    }
}
