<?php
#Name: PlayTechAPI.class.php
#Author: FTG
#Version: 1.0.0
#Copyright 2010 PhilWeb Corporation

//require_once('libs/nusoap/nusoap.php');
//$_PlayTechAPI = new PlayTechAPI($apiUrl, $casino, $secretKey, $responseType);

class PlayTechAPI
{
    private $_apiUrl;
    private $_casino;
    private $_secretKey;
    private $_scriptFileName;
    private $_responseType = 'xml';
    private $_postData = '';
    private $_fullUri;
    
    public function __construct($apiUrl, $casino, $secretKey, $responseType = 'xml')
    {
        $this->_apiUrl = $apiUrl;
        $this->_casino = $casino;
        $this->_secretKey = $secretKey;
        $this->_responseType = $responseType;

        $this->InitPostData();
        
        $this->_fullUri = $apiUrl;
    }
    
    private function InitPostData()
    {
        $this->_postData = '';
        $this->_postData = '?';
        $this->_postData = $this->_postData . 'responsetype=' . $this->_responseType;
        $this->_postData = $this->_postData . '&casino=' . $this->_casino;
        $this->_postData = $this->_postData . '&secretkey=' . $this->_secretKey;
    }
    
    public function GetPlayerInfo($userName, $password)
    {
        $_scriptFileName = 'get_playerinfo.php';
        
        $this->InitPostData();        
        $this->_postData = $this->_postData . '&username=' . $userName;
        $this->_postData = $this->_postData . '&password=' . $password;
        
        $this->_fullUri = $this->_apiUrl;
        
        $this->_fullUri = $this->_fullUri . '/' . $_scriptFileName . $this->_postData;
        
        return $this->SubmitData($this->_fullUri, $this->_postData);
    }
    
    public function ExternalDeposit($userName, $password, $amount, $currency = 'PHP', $externalTranId, $comments = '')
    {
        $_scriptFileName = 'externaldeposit.php';
        
        $this->InitPostData();
        $this->_postData = $this->_postData . '&username=' . $userName;
        $this->_postData = $this->_postData . '&password=' . $password;
        $this->_postData = $this->_postData . '&amount=' . $amount;
        $this->_postData = $this->_postData . '&currency=' . $currency;        
        $this->_postData = $this->_postData . '&externaltranid=' . $externalTranId;
        $this->_postData = $this->_postData . '&comments=' . $comments;
        
        $this->_fullUri = $this->_apiUrl;
        
        $this->_fullUri = $this->_fullUri . '/' . $_scriptFileName . $this->_postData;
        
        return $this->SubmitData($this->_fullUri, $this->_postData);
    }
    
    public function ExternalWithdraw($userName, $password, $amount, $currency = 'PHP', $externalTranId, $comments = '')
    {
        $_scriptFileName = 'externalwithdraw.php';
        
        $this->InitPostData();
        $this->_postData = $this->_postData . '&username=' . $userName;
        $this->_postData = $this->_postData . '&password=' . $password;
        $this->_postData = $this->_postData . '&amount=' . $amount;
        $this->_postData = $this->_postData . '&currency=' . $currency;        
        $this->_postData = $this->_postData . '&externaltranid=' . $externalTranId;
        $this->_postData = $this->_postData . '&comments=' . $comments;
        
        $this->_fullUri = $this->_apiUrl;
        
        $this->_fullUri = $this->_fullUri . '/' . $_scriptFileName . $this->_postData;
        
        return $this->SubmitData($this->_fullUri, $this->_postData);
    }
    
    private function SubmitData($uri, $postData)
    {
        $ch = curl_init();
        $ret = curl_setopt($ch, CURLOPT_POST, 1);
        $ret = curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
        $ret = curl_setopt($ch, CURLOPT_URL, $uri);
        $ret = curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        $ret = curl_setopt($ch, CURLOPT_HEADER, 0);
        $ret = curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 0);
        $ret = curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $ret = curl_setopt($ch, CURLOPT_TIMEOUT,30);
        $str = curl_exec($ch);
        
        return $str;
    }
}

?>