<?php

/**
 * Description of MPapiInvokerController
 *
 * @author fdlsison
 * @date 06-20-2014
 */
class MPapiInvokerController extends Controller
{

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

    public function actionOverview()
    {
        $this->pageTitle = 'Membership Portal API - Overview';
        $this->render('overview');
    }

    public function actionRegisterMemberNaboo()
    {
        $this->pageTitle = 'Member Registration (Naboo)';
        $result = '';

        if (isset($_POST['MobileNo']) || isset($_POST['PlayerName']))
        {
            $mobileno = $_POST['MobileNo'];
            $playername = $_POST['PlayerName'];

            $result = $this->_registerMemberNaboo($mobileno, $playername);
        }

        $this->render('registermembernaboo', array('result' => $result));
    }

    public function actionReloadNaboo()
    {
        $this->pageTitle = 'Member Reload (Naboo)';
        $result = '';

        if (isset($_POST['Password']) || isset($_POST['MobileNo']) || isset($_POST['Amount']))
        {
            $pw = $_POST['Password'];
            $mobileno = $_POST['MobileNo'];
            $amount = $_POST['Amount'];

            $result = $this->_reloadNaboo($pw, $mobileno, $amount);
        }

        $this->render('reloadnaboo', array('result' => $result));
    }

    public function actionGetReferrer()
    {
        $this->pageTitle = 'Get Referrer (Naboo)';
        $result = '';

        if (isset($_POST['yt0']))
            $result = $this->_getReferrer();

        $this->render('getreferrer', array('result' => $result));
    }

    private function _registerMemberNaboo($mobileno, $playername)
    {
        $postdata = CJSON::encode(array('MobileNo' => $mobileno, 'PlayerName' => $playername));
        $result = $this->SubmitData(Yii::app()->params['urlMPAPI'] . 'registermembernaboo', $postdata);

        return $result[1];
    }

    private function _reloadNaboo($password, $mobileno, $amount)
    {
        $postdata = CJSON::encode(array('Password' => $password, 'MobileNo' => $mobileno, 'Amount' => $amount));
        $result = $this->SubmitData(Yii::app()->params['urlMPAPI'] . 'reloadnaboo', $postdata);

        return $result[1];
    }

    private function _getReferrer()
    {
        $postdata = CJSON::encode(array());

        $result = $this->SubmitData(Yii::app()->params['urlMPAPI'] . 'getreferrer', $postdata);

        return $result[1];
    }

    private function SubmitData($uri, $postdata)
    {
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

}

?>
