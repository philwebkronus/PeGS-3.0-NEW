<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of CurlPostController
 *
 * @author elperez
 */
class CurlPostController {
    public function runAction(){
        Mirage::loadModels('SpyderRequestLogsModel');
        $spyderRequestLogsModel = new SpyderRequestLogsModel();
        
        $terminalName = '';
        $commandId = '';
        $username = '';
        $password = '';
        $type = '';
        $spyderReqLogID = '';
        $casinoId = '';
        
        //catch get variables
        if(isset($_GET['TerminalName']))
            $terminalName = $_GET['TerminalName'];
        if(isset($_GET['CommandID']))
            $commandId = $_GET['CommandID'];
        if(isset($_GET['UserName']))
            $username = $_GET['UserName'];
        if(isset($_GET['Password']))
            $password = $_GET['Password'];
        if(isset($_GET['TerminalName']))
            $type = $_GET['Type'];
        if(isset($_GET['SpyderReqID']))
            $spyderReqLogID = $_GET['SpyderReqID']; 
        if(isset($_GET['CasinoID']))
            $casinoId = $_GET['CasinoID'];
        
        $queryString =  '?';
        $queryString = $queryString. 'TerminalName='.$terminalName;
        $queryString = $queryString. '&CommandID='.$commandId;
        $queryString = $queryString. '&UserName='.$username;
        $queryString = $queryString. '&Password='.$password;
        $queryString = $queryString. '&Type='.$type;
        $queryString = $queryString. '&CasinoID='.$casinoId;
        
        $sapi = Mirage::app()->param['SAPI_URI'].$queryString;
        
        $response = $this->SubmitData($sapi);
        if($response[0] == 200 ){
            if($response[1] == "1"){
                  //update spyder request logs as successful
                  $status = 1;
                  $spyderRequestLogsModel->update($status, $spyderReqLogID);
                  
            } else {
                  //update spyder request logs as failed
                  $status = 2;
                  $spyderRequestLogsModel->update($status, $spyderReqLogID);
            }
        } else {
            //update spyder request logs as failed
           $status = 2;
           $spyderRequestLogsModel->update($status, $spyderReqLogID);
        }
    }

    private function SubmitData( $uri )
    {
            $curl = curl_init( $uri );

            curl_setopt( $curl, CURLOPT_FRESH_CONNECT, $this->_caching );
            curl_setopt( $curl, CURLOPT_CONNECTTIMEOUT, $this->_connectionTimeout );
            curl_setopt( $curl, CURLOPT_TIMEOUT, $this->_timeout );
            curl_setopt( $curl, CURLOPT_USERAGENT, $this->_userAgent );
            curl_setopt( $curl, CURLOPT_SSL_VERIFYHOST, FALSE );
            curl_setopt( $curl, CURLOPT_SSL_VERIFYPEER, FALSE );
            curl_setopt( $curl, CURLOPT_POST, FALSE );
            curl_setopt( $curl, CURLOPT_HTTPHEADER, array( 'Content-Type: text/plain; charset=utf-8' ) );
            curl_setopt( $curl, CURLOPT_RETURNTRANSFER, TRUE );

            $response = curl_exec( $curl );

            $http_status = curl_getinfo( $curl, CURLINFO_HTTP_CODE );

            curl_close( $curl );

            return array( $http_status, $response );
    }
}

?>
