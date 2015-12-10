<?php

class InterfaceController extends Controller {

    private $_caching = FALSE;
    var $MID;

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

    public function actionRegister() {
        $return[2]="";
        if (isset($_POST['submit'])) {
            $uri = Yii::app()->params['urlMPAPI'] . 'registermembernaboo';
            $data['MobileNo'] = $_POST['mobileNo'];
            $data['PlayerName'] = $_POST['Name'];

            $result = json_encode($data);
            $return = $this->SubmitData($uri, $result);
            $message = strstr($return[1], "ReturnMessage");
            $return = explode("\"", $message);
            //echo '<script>alert("' . $return[2] . '");</script>';
            
        }
        $this->render('register', array('return' => $return[2]));
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
        return array($http_status, $response);
    }

}
