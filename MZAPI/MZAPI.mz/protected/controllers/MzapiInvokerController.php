<?php

/**
 * Description of AmpapiInvokerController
 *
 * @author jdlachica
 * @date 07/21/2014
 */
class MzapiInvokerController extends Controller {

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
    public function actionOverview() {
        // renders the view file 'protected/views/site/index.php'
        // using the default layout 'protected/views/layouts/main.php'
        $this->render('overview');
    }

    //Function that authenticates session
    public function actionTransferWallet() {
        $this->pageTitle = 'Transfer Wallet';
        $result = '';
        $moduleName = 'transferwallet';

        if (isset($_POST['TerminalCode']) && isset($_POST['ServiceID']) && isset($_POST['Usermode'])) {
            $TerminalCode = htmlspecialchars($_POST['TerminalCode']);
            $ServiceID = htmlspecialchars($_POST['ServiceID']);
            $UserMode = htmlspecialchars($_POST['Usermode']);

            $result = $this->_TransferWallet($TerminalCode, $ServiceID, $UserMode);
        }

        $this->render($moduleName, array('result' => $result));
    }

    private function _TransferWallet($TerminalCode, $ServiceID, $UserMode) {

        $url = Yii::app()->params['transferwallet'];
        $postData = CJSON::encode(array('TerminalCode' => $TerminalCode, 'ServiceID' => $ServiceID, 'Usermode' => $UserMode));
        $result = $this->SubmitData($url, $postData);

        return $result[1];
    }

    //Function that authenticates session
    public function actionValidateLogin() {
        $this->pageTitle = 'Transfer Wallet';
        $result = '';
        $moduleName = 'transferwallet';

        if (isset($_POST['Referrer']) && isset($_POST['SiteCode']) && isset($_POST['Username']) && isset($_POST['Password'])) {
            $sitecode = htmlspecialchars($_POST['SiteCode']);
            $username = htmlspecialchars($_POST['Username']);
            $password = htmlspecialchars($_POST['Password']);
            $referrer = htmlspecialchars($_POST['Referrer']);

            $result = $this->_ValidateLogin($sitecode, $username, $password, $referrer, $moduleName);
        }

        $this->render($moduleName, array('result' => $result));
    }

    private function _ValidateLogin($sitecode, $username, $password, $referrer, $moduleName) {

        $url = Yii::app()->params['transferwallet'];
        $postData = CJSON::encode(array('Referrer' => $referrer, 'SiteCode' => $sitecode, 'Username' => $username, 'Password' => $password));
        $result = $this->SubmitData($url, $postData);

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

        //print_r($uri);exit;
        return array($http_status, $response);
    }

    public function actionError() {
        if ($error = Yii::app()->errorHandler->error) {
            if (Yii::app()->request->isAjaxRequest)
                echo $error['message'];
            else
                $this->render('error', $error);
        }
    }

}

?>
