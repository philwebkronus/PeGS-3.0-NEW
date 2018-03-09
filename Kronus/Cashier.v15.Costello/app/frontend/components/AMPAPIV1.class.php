<?php

class AMPAPIV1 {

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

    public function AuthenticateSession($username, $password) {

        $modulename = "authenticatesession";
        $url = Mirage::app()->param['urlAMPAPI'] . $modulename;

        $username = trim($username);
        $password = trim($password);

        $postdata = json_encode(array('Username' => $username, 'Password' => $password,));

        $methodname = "Authenticate Session";
        $data = print_r($postdata, true);
        $message = "[$methodname] Input: $data";
        logger($message, "Request", '', true);
        $result = $this->SubmitData($url, $postdata, $methodname);

        return $result;
    }

    public function GetGender($TPSessionID) {

        $modulename = "getgender";
        $url = Mirage::app()->param['urlAMPAPI'] . $modulename;

        $postdata = json_encode(array('TPSessionID' => $TPSessionID));

        $methodname = "Get Gender";
        $data = print_r($postdata, true);
        $message = "[$methodname] Input: $data";
        logger($message, "Request", '', true);
        $result = $this->SubmitData($url, $postdata, $methodname);

        return $result;
    }

    public function GetCity($TPSessionID) {
        $modulename = "getcity";
        $url = Mirage::app()->param['urlAMPAPI'] . $modulename;


        $postdata = json_encode(array('TPSessionID' => $TPSessionID));

        $methodname = "Get City";
        $data = print_r($postdata, true);
        $message = "[$methodname] Input: $data";
        logger($message, "Request", '', true);
        $result = $this->SubmitData($url, $postdata, $methodname);

        return $result;
    }

    public function GetRegion($TPSessionID) {
        $modulename = "getregion";
        $url = Mirage::app()->param['urlAMPAPI'] . $modulename;

        $postdata = json_encode(array('TPSessionID' => $TPSessionID));

        $methodname = "Get Region";
        $data = print_r($postdata, true);
        $message = "[$methodname] Input: $data";
        logger($message, "Request", '', true);
        $result = $this->SubmitData($url, $postdata, $methodname);

        return $result;
    }

    public function GetIDPresented($TPSessionID) {
        $modulename = "getidpresented";
        $url = Mirage::app()->param['urlAMPAPI'] . $modulename;

        $postdata = json_encode(array('TPSessionID' => $TPSessionID));

        $methodname = "Get ID Presented";
        $data = print_r($postdata, true);
        $message = "[$methodname] Input: $data";
        logger($message, "Request", '', true);
        $result = $this->SubmitData($url, $postdata, $methodname);

        return $result;
    }

    public function GetNationality($TPSessionID) {
        $modulename = "getnationality";
        $url = Mirage::app()->param['urlAMPAPI'] . $modulename;

        $postdata = json_encode(array('TPSessionID' => $TPSessionID));

        $methodname = "Get Nationality";
        $data = print_r($postdata, true);
        $message = "[$methodname] Input: $data";
        logger($message, "Request", '', true);
        $result = $this->SubmitData($url, $postdata, $methodname);

        return $result;
    }

    public function GetOccupation($TPSessionID) {
        $modulename = "getoccupation";
        $url = Mirage::app()->param['urlAMPAPI'] . $modulename;

        $postdata = json_encode(array('TPSessionID' => $TPSessionID));

        $methodname = "Get Occupation";
        $data = print_r($postdata, true);
        $message = "[$methodname] Input: $data";
        logger($message, "Request", '', true);
        $result = $this->SubmitData($url, $postdata, $methodname);

        return $result;
    }

    public function GetIsSmoker($TPSessionID) {
        $modulename = "getissmoker";
        $url = Mirage::app()->param['urlAMPAPI'] . $modulename;

        $postdata = json_encode(array('TPSessionID' => $TPSessionID));

        $methodname = "Get IsSmoker";
        $data = print_r($postdata, true);
        $message = "[$methodname] Input: $data";
        logger($message, "Request", '', true);
        $result = $this->SubmitData($url, $postdata, $methodname);

        return $result;
    }

    public function GetReferrer($TPSessionID) {
        $modulename = "getreferrer";
        $url = Mirage::app()->param['urlAMPAPI'] . $modulename;

        $postdata = json_encode(array('TPSessionID' => $TPSessionID));

        $methodname = "Get Referrer ";
        $data = print_r($postdata, true);
        $message = "[$methodname] Input: $data";
        logger($message, "Request", '', true);
        $result = $this->SubmitData($url, $postdata, $methodname);

        return $result;
    }

    public function GetCivilStatus($TPSessionID) {
        $modulename = "getcivilstatus";
        $url = Mirage::app()->param['urlAMPAPI'] . $modulename;

        $postdata = json_encode(array('TPSessionID' => $TPSessionID));

        $methodname = "Get Referrer ";
        $data = print_r($postdata, true);
        $message = "[$methodname] Input: $data";
        logger($message, "Request", '', true);
        $result = $this->SubmitData($url, $postdata, $methodname);

        return $result;
    }

    public function GetRegisterFor($TPSessionID) {
        $modulename = "getregisterfor";
        $url = Mirage::app()->param['urlAMPAPI'] . $modulename;

        $postdata = json_encode(array('TPSessionID' => $TPSessionID));

        $methodname = "Get Referrer ";
        $data = print_r($postdata, true);
        $message = "[$methodname] Input: $data";
        logger($message, "Request", '', true);
        $result = $this->SubmitData($url, $postdata, $methodname);

        return $result;
    }


    public function RegisterMember($TPSessionID, $fname, $mname, $lname, $nname, $password, $barangay, $city, $region, $mobilenumber, $altermobilenumber, $emailaddress, $alteremailaddress, $gender, $idpresented, $idnumber, $nationality, $birthdate, $occupation, $issmoker, $refferalcode, $referrer, $EmailSubscription, $SMSSubscription, $civilstatus, $registerfor, $UBCard, $AID, $SiteID) {
        $modulename = "autoregistermember";
        $url = Mirage::app()->param['urlAMPAPI'] . $modulename;

        $PermanentAdd = $barangay . " , " . $city . "," . $region;

        $postdata = json_encode(array(
            'TPSessionID' => $TPSessionID,
            'FirstName' => $fname,
            'MiddleName' => $mname,
            'LastName' => $lname,
            'NickName' => $nname,
            'Password' => $password,
            'PermanentAdd' => $PermanentAdd,
            'MobileNo' => $mobilenumber,
            'AlternateMobileNo' => $altermobilenumber,
            'EmailAddress' => $emailaddress,
            'AlternateEmail' => $alteremailaddress,
            'Gender' => $gender,
            'IDPresented' => $idpresented,
            'IDNumber' => $idnumber,
            'Nationality' => $nationality,
            'Birthdate' => $birthdate,
            'Occupation' => $occupation,
            'IsSmoker' => $issmoker,
            'ReferralCode' => $refferalcode,
            'ReferrerID' => $referrer,
            'EmailSubscription' => $EmailSubscription,
            'SMSSubscription' => $SMSSubscription,
            'CivilStatus' => $civilstatus,
            'RegisterFor' => $registerfor,
            'UBCard' => $UBCard,
            'AID' => $AID,
            'SiteID' => $SiteID,
        ));

        $methodname = "Register Member ";
        $data = print_r($postdata, true);
        $message = "[$methodname] Input: $data";
        logger($message, "Request", '', true);
        $result = $this->SubmitData($url, $postdata, $methodname);

        return $result;
    }


    private function SubmitData($uri, $postdata, $methodname) {
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
        //curl_setopt( $curl, CURLOPT_SSLVERSION, 3 );
        curl_setopt($curl, CURLOPT_SSL_CIPHER_LIST, 'TLSv1');

        // Data+Files to be posted
        curl_setopt($curl, CURLOPT_POSTFIELDS, $postdata);
        $response = curl_exec($curl);

        $http_status = curl_getinfo($curl, CURLINFO_HTTP_CODE);

        curl_close($curl);

        return array($http_status, $response);
    }

    /*
     * Added June 14, 2016
     * John Aaron Vida
     */

    public function checkResponse($data) {
        $obj = json_decode($data);
        if ($obj === null) {
            $pattern = "/<p class=\"message\">([\w\W]*?)<\/p>/";
            preg_match($pattern, $data, $matches);
            $result = $matches[1];
            return trim($result);
        }
        return $data;
    }

}

?>

