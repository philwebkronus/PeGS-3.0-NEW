<?php

class CronController extends Controller {

    private $_apiUrl;
    private $_appId;
    private $_fullUri;
    private $_postData;

    public function __construct($apiUrl, $appId) {
        $this->_apiUrl = $apiUrl;
        $this->_appId = $appId;
        $this->_fullUri = $apiUrl;
    }

    public function actionMigrateByMID() {
        $card = $_GET['CardNumber'];
        $page = 'https://mpapi.egamescasino.ph/index.php/cron/MigrateByMID/';
        $requestParameters = array('CardNumber' => $card);

        $this->_postData = json_encode($requestParameters);

        $this->_fullUri = $page;

        $result = $this->submitData($this->_fullUri, $this->_postData);

        if ($result[0] == 200) {
            $response = $this->XML2Array($result[1]);
        } else {
            $response = "HTTP Error";
        }

        echo $response;
    }

    public function actionMigrateByBatch() {
        $page = 'https://mpapi.egamescasino.ph/index.php/cron/MigrateByBatch/';
        $cards = $_GET['CardNumbers'];
        $requestParameters = array('CardNumbers' => $cards);

        $this->_postData = json_encode($requestParameters);
        $this->_fullUri = $page;
        $result = $this->submitData($this->_fullUri, $this->_postData);

        if ($result[0] == 200) {
            $response = $this->XML2Array($result[1]);
        } else {
            $response = "HTTP Error";
        }
        
        echo $response;
    }

    private function submitData($url, $postdata) {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
        curl_setopt($curl, CURLOPT_FRESH_CONNECT, 1);
        curl_setopt($curl, CURLOPT_FORBID_REUSE, 1);
        curl_setopt($curl, CURLOPT_HEADER, FALSE);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($curl, CURLOPT_POST, TRUE);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $postdata);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($curl, CURLOPT_VERBOSE, TRUE);

        $response = curl_exec($curl);
        var_dump(curl_error($curl));

        $http_status = curl_getinfo($curl, CURLINFO_HTTP_CODE);

        curl_close($curl);

        return array($http_status, $response);
    }

    private function XML2Array($json) {
        return json_decode($json, TRUE);
    }

}

?>