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
        
        if(isset($_POST['MID']) && isset($_POST['ServiceID']) && isset($_POST['Amount']) && isset($_POST['Method']) && isset($_POST['Tracking'])&& isset($_POST['BetSlipID'])&& isset($_POST['BetRefID'])){
            
            $mid = $_POST['MID'];
            $serviceID = $_POST['ServiceID'];
            $amount = $_POST['Amount'];
            $method = $_POST['Method'];
            $tracking = $_POST['Tracking'];
            $betSlipID = $_POST['BetSlipID'];
            $betRefID = $_POST['BetRefID'];
            
            $result = $this->_depositMSW($mid, $serviceID, $amount, $method,$tracking, $betSlipID, $betRefID);
        }
        
        $this->render('depositmsw', array('result'=>$result));
    }
    
    private function _depositMSW($mid, $serviceID, $amount, $method,$tracking, $betSlipID, $betRefID) {
        $url = Yii::app()->params['deposit_msw'];
        
        $postdata = CJSON::encode(array('MID'=>$mid, 'ServiceID' => $serviceID, 'Amount' => $amount, 'Method'=> $method, 'Tracking'=>$tracking, 'BetSlipID'=>$betSlipID, 'BetRefID'=>$betRefID));
       
        $result = $this->SubmitData($url, $postdata);

        return $result[1];
    }

    //updated 04272016
    //mcatangan
    public function actionWithdrawMSW() {
        $this->pageTitle = 'KAPI - e-SAFE Withdraw MSW';
        $result = '';
        
        if(isset($_POST['MID']) && isset($_POST['ServiceID']) && isset($_POST['Amount']) && isset($_POST['Amount']) && isset($_POST['Tracking'])&& isset($_POST['BetSlipID'])&& isset($_POST['BetRefID'])){
            
            $mid = $_POST['MID'];
            $serviceID = $_POST['ServiceID'];
            $amount = $_POST['Amount'];
            $method = $_POST['Method'];
            $tracking = $_POST['Tracking'];
            $betSlipID = $_POST['BetSlipID'];
            $betRefID = $_POST['BetRefID'];
            $result = $this->_withdrawMSW($mid, $serviceID, $amount, $method, $tracking,$betSlipID, $betRefID);
        }
        
        $this->render('withdrawmsw', array('result'=>$result));
    }
    
    private function _withdrawMSW($mid, $serviceID, $amount, $method, $tracking,$betSlipID, $betRefID) {
        $url = Yii::app()->params['withdraw_msw'];
        
        $postdata = CJSON::encode(array('MID'=>$mid, 'ServiceID' => $serviceID, 'Amount' => $amount, 'Method' => $method, 'Tracking'=>$tracking, 'BetSlipID'=>$betSlipID, 'BetRefID'=>$betRefID));
       
        $result = $this->SubmitData($url, $postdata);

        return $result[1];
    }
}
