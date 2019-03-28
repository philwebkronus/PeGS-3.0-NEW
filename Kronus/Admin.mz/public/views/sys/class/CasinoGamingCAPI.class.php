<?php
/**
 * Common API calls to RTG, Habanero
 * @author elperez
 * @created on: April 29, 2012
 */
include 'CasinoCAPIHandler.class.php';

class CasinoGamingCAPI 
{
    /**
     * Configuration parameters that must pass to Habanero
     * @author Claire Marie Tamayo
     * @created on: November 24, 2017
     * @param string $url
     * @param string $brandID
     * @param string $apiKey 
     * @return array CasinoCAPIHandler 
     */
    public function configureHAB($url, $brandID, $apiKey)
    {
        $configuration = array( 'URI' => $url, 'isCaching' => FALSE, 'isDebug' => TRUE,'brandID' => $brandID, 'apiKey'=>$apiKey);
            
        $_CasinoAPIHandler = new CasinoCAPIHandler(CasinoCAPIHandler::HAB, $configuration);
        
        // check if connected
        if (!(bool)$_CasinoAPIHandler->IsAPIServerOK()) 
        {
            $message = 'Can\'t connect to Habanero';
            self::throwError($message);
        }
        
        return $_CasinoAPIHandler;
    }
    
    public function validateHabCasinoAccount($url, $capiusername, $capipassword, $login, $password)
    {
        $casinoApiHandler = $this->configureHAB($url, $capiusername, $capipassword); //BrandID, APIKey
        $accExists = $casinoApiHandler->GetAccountInfo( $login, $password);
        return $accExists;
    }    

    /**
     * Configuration parameters that must pass to RTG
     * @param int $serverID
     * @param string $url
     * @param int $isplayerAPI
     * @return array CasinoCAPIHandler 
     */
    public function configureRTG($serverID, $url, $isplayerAPI, $usermode = 0)
    {
        //check if player api was called
        if($isplayerAPI == 1)
        {
            $configuration = array( 'URI' => $url,
                                'isCaching' => FALSE,
                                'isDebug' => TRUE,
                                'certFilePath' => RTGCerts_DIR . $serverID  . '/cert.pem',
                                'keyFilePath' => RTGCerts_DIR . $serverID  . '/key.pem',
                                'userMode' => $usermode
                             );
        }
        else
        {
            $gdeposit = 503;
            $gwithdraw = 502;
            
            $configuration = array( 'URI' => $url,
                                'isCaching' => FALSE,
                                'isDebug' => TRUE,
                                'certFilePath' => RTGCerts_DIR . $serverID  . '/cert.pem',
                                'keyFilePath' => RTGCerts_DIR . $serverID  . '/key.pem',
                                'depositMethodId' => $gdeposit,
                                'withdrawalMethodId'=>$gwithdraw,
                                'userMode' => $usermode
                             );
        }
            
        $_CasinoAPIHandler = new CasinoCAPIHandler(CasinoCAPIHandler::RTG, $configuration);
        
        // check if connected
        if (!(bool)$_CasinoAPIHandler->IsAPIServerOK()) 
        {
            $message = 'Can\'t connect to RTG';
            self::throwError($message);
        }
        return $_CasinoAPIHandler;
    }
    
    /**
     * Configuration parameters that must pass to RTG
     * @param int $serverID
     * @param string $url
     * @param int $isplayerAPI
     * @return array CasinoCAPIHandler 
     */
    public function configureRTG2($serverID, $url, $isplayerAPI)
    {
        //check if player api was called
        if($isplayerAPI == 1)
        {
            $configuration = array( 'URI' => $url,
                                'isCaching' => FALSE,
                                'isDebug' => TRUE,
                                //COMMENT OUT CCT 07/18/2018 BEGIN
                                'certFilePath' => RTGCerts_DIR . $serverID  . '/cert-key.pem',
                                'keyFilePath' => RTGCerts_DIR . $serverID  . '/cert-key.pem',
                                //COMMENT OUT CCT 07/18/2018 END
                                // EDITED CCT 07/18/2018 BEGIN
                                //'certFilePath' => RTGCerts_DIR . $serverID  . '/cert.pem',
                                //'keyFilePath' => RTGCerts_DIR . $serverID  . '/key.pem',
                                // EDITED CCT 07/18/2018 END
                            );
        }
        else
        {
            $gdeposit = 503;
            $gwithdraw = 502;
            
            $configuration = array( 'URI' => $url,
                                'isCaching' => FALSE,
                                'isDebug' => TRUE,
                                'certFilePath' => RTGCerts_DIR . $serverID  . '/cert.pem',
                                'keyFilePath' => RTGCerts_DIR . $serverID  . '/key.pem',
                                'depositMethodId' => $gdeposit,
                                'withdrawalMethodId'=>$gwithdraw
                             );
        }

        $_CasinoAPIHandler = new CasinoCAPIHandler(CasinoCAPIHandler::RTG2, $configuration);

        // check if connected
        if (!(bool)$_CasinoAPIHandler->IsAPIServerOK()) 
        {
            $message = 'Can\'t connect to RTG';
            self::throwError($message);
        }
        return $_CasinoAPIHandler;
    }
    
    /**
     * handles error if api connection is not reachable
     */
    public static function throwError($message)
    {
        header('HTTP/1.0 404 Not Found');
        return $message;
    }
    
    /**
     * create terminal account
     * @param type $vprovidername
     * @param type $serviceID
     * @param type $url
     * @param type $login
     * @param type $password
     * @param type $aid
     * @param type $currency
     * @param type $email
     * @param type $fname
     * @param type $lname
     * @param type $dayphone
     * @param type $evephone
     * @param type $addr1
     * @param type $addr2
     * @param type $city
     * @param type $country
     * @param type $province
     * @param type $zip
     * @param type $userID
     * @param type $birthdate
     * @param type $fax
     * @param type $occupation
     * @param type $sex
     * @param type $alias
     * @param type $casinoID
     * @param type $ip
     * @param type $mac
     * @param type $downloadID
     * @param type $clientID
     * @param type $putInAffPID
     * @param type $calledFromCasino
     * @param type $hashedPassword
     * @param type $agentID
     * @param type $currentPosition
     * @param type $thirdPartyPID
     * @param type $capiusername
     * @param type $capipassword
     * @param type $capiplayername
     * @param type $capiserverID
     * @return type 
     */
    public function createTerminalAccount($vprovidername, $serviceID, $url, $login, $password, $aid, $currency, $email, $fname,
        $lname, $dayphone, $evephone, $addr1, $addr2, $city, $country, $province, $zip, $userID, $birthdate, $fax, $occupation,
        $sex, $alias, $casinoID, $ip, $mac, $downloadID, $clientID, $putInAffPID, $calledFromCasino,
        $hashedPassword,$agentID,$currentPosition, $thirdPartyPID, $capiusername, $capipassword,
        $capiplayername, $capiserverID,$isVIP='', $usermode='')
    {
        switch ($vprovidername)
        {
            case "EB":
                $createTerminalResult = array('IsSucceed'=>true, 'ErrorMessage'=>'Service Successfully Created', 'ErrorCode'=> 0, 'ErrorID'=> 0); 
                break;            

            case "HAB":
                $casinoApiHandler = $this->configureHAB($url, $capiusername, $capipassword);  //BrandID, APIKey
                $createTerminalResult = $casinoApiHandler->CreateTerminalAccount($login, $password,
                $aid, $currency, $email, $fname, $lname, $dayphone, $evephone,
                $addr1, $addr2, $city, $country, $province, $zip, $userID,
                $birthdate, $fax, $occupation, $sex, $alias, $casinoID, $ip,
                $mac, $downloadID, $clientID, $putInAffPID, $calledFromCasino,
                $hashedPassword, $agentID, $currentPosition, $thirdPartyPID, $isVIP, $usermode);
                break;            

            case "RTG2":
                $isplayerAPI = 1;
                $casinoApiHandler = $this->configureRTG2($serviceID, $url, $isplayerAPI);
                $createTerminalResult = $casinoApiHandler->CreateTerminalAccount($login, $password,
                $aid, $currency, $email, $fname, $lname, $dayphone, $evephone,
                $addr1, $addr2, $city, $country, $province, $zip, $userID,
                $birthdate, $fax, $occupation, $sex, $alias, $casinoID, $ip,
                $mac, $downloadID, $clientID, $putInAffPID, $calledFromCasino,
                $hashedPassword, $agentID, $currentPosition, $thirdPartyPID,$isVIP);
                break;    
            
            case "RTG":
                $isplayerAPI = 1;
                // EDITED CCT 06/29/2018 BEGIN
                //if($usermode == '0') 
                if (($usermode == '0') || ($usermode == '3'))
                // EDITED CCT 06/29/2018 END
                {
                    $casinoApiHandler = $this->configureRTG($serviceID, $url, $isplayerAPI);
                } 
                else 
                {
                    $casinoApiHandler = $this->configureRTG2($serviceID, $url, $isplayerAPI);
                }
                $createTerminalResult = $casinoApiHandler->CreateTerminalAccount($login, $password,
                $aid, $currency, $email, $fname, $lname, $dayphone, $evephone,
                $addr1, $addr2, $city, $country, $province, $zip, $userID,
                $birthdate, $fax, $occupation, $sex, $alias, $casinoID, $ip,
                $mac, $downloadID, $clientID, $putInAffPID, $calledFromCasino,
                $hashedPassword, $agentID, $currentPosition, $thirdPartyPID, $isVIP, $usermode);
                break;

            default:
                echo 'Invalid Casino Name.';
                break;
        }
        return $createTerminalResult;
    }
    
    /**
     * Updates terminal password 
     * @param type $vprovidername
     * @param type $serviceID
     * @param type $url
     * @param type $casinoID
     * @param type $login
     * @param type $oldpassword
     * @param type $newpassword
     * @param type $capiusername
     * @param type $capipassword
     * @param type $capiplayername
     * @param type $capiserverID
     * @return type 
     */
    public function changeTerminalPassword($vprovidername, $serviceID, $url, $casinoID, $login, $oldpassword, $newpassword,
        $capiusername, $capipassword, $capiplayername, $capiserverID, $usermode="")
    {
        switch($vprovidername)
        {
            case "RTG":
                $isplayerAPI = 1;
                if($usermode == '0') 
                {
                    $casinoApiHandler = $this->configureRTG($serviceID, $url, $isplayerAPI);
                } 
                else 
                {
                    $casinoApiHandler = $this->configureRTG2($serviceID, $url, $isplayerAPI);
                }
                $changePwdResult = $casinoApiHandler->ChangeTerminalPassword($casinoID, $login, $oldpassword, $newpassword, $usermode);
                break;
                
            case "RTG2":
                $isplayerAPI = 1;
                $casinoApiHandler = $this->configureRTG2($serviceID, $url, $isplayerAPI);
                // EDITED CCT 07/23/2018 BEGIN
                //$changePwdResult = $casinoApiHandler->ChangeTerminalPassword($casinoID, $login, $oldpassword, $newpassword);
                $changePwdResult = $casinoApiHandler->ChangeTerminalPassword($casinoID, $login, $oldpassword, $newpassword, $usermode);
                // EDITED CCT 07/23/2018 END
                break;
            
            case "HAB":
                $casinoApiHandler = $this->configureHAB($url, $capiusername, $capipassword);  //BrandID, APIKey
                $changePwdResult = $casinoApiHandler->ChangeTerminalPassword($casinoID, $login, $oldpassword, $newpassword, $usermode);
                break;   

            default :
                echo 'Invalid Provider Name';
        }
        return $changePwdResult;
    }

    public function getBalance($providername, $serviceID, $url, $login, $capiusername, $capipassword, $capiplayername, $capiserverID, $usermode="", 
            $password = "") 
    {
        switch ($providername)
        {
            case "EB":
                $balanceinfo = array( 'IsSucceed' => true, 'ErrorCode' => 0, 'ErrorMessage' => null, 'BalanceInfo' => array( 'Balance' => 0 ));
                break;

            case "RTG2":
                $isplayerAPI = 0;
                $casinoApiHandler = $this->configureRTG2($serviceID, $url, $isplayerAPI);
                $balanceinfo = $casinoApiHandler->GetBalance($login);
                break;
            
            case "RTG":
                $isplayerAPI = 0;
                if($usermode == '0') 
                {
                    $casinoApiHandler = $this->configureRTG($serviceID, $url, $isplayerAPI, $usermode);
                } 
                else 
                {
                    $casinoApiHandler = $this->configureRTG2($serviceID, $url, $isplayerAPI);
                }
                $balanceinfo = $casinoApiHandler->GetBalance($login);
                break;
                
            case "HAB":
                $casinoApiHandler = $this->configureHAB($url, $capiusername, $capipassword); //BrandID, APIKey
                $balanceinfo = $casinoApiHandler->GetBalance($login, $password);
                break;            
        }

        if(!isset($balanceinfo['BalanceInfo']['Balance'])) 
        {
            $message = 'Error: Cannot get balance';
        }
        
        if(isset($balanceinfo['BalanceInfo']['Balance']))
        {
            $terminal_balance = $balanceinfo['BalanceInfo']['Balance'];
            $redeemable_amount = 0;
            if(isset($balanceinfo['BalanceInfo']['Redeemable'])) 
            {
                $redeemable_amount = $balanceinfo['BalanceInfo']['Redeemable'];
            } 
            else 
            {
                $redeemable_amount = $terminal_balance;
            }
            return $redeemable_amount;
        }
        else
        {
            return $message;
        }
    }
    
    public function Withdraw($providername, $serviceID, $url, $login, $capiusername, $capipassword, $capiplayername, 
                            $capiserverID,$amount, $tracking1, $tracking2, $tracking3, $tracking4, $methodname, $usermode = 0, $locatorName = null, 
                            $password= "") 
    {
        switch ($providername)
        {
            case "RTG":
                $isplayerAPI = 0;
                $casinoApiHandler = $this->configureRTG($serviceID, $url, $isplayerAPI, $usermode);
                if (is_null($locatorName)) 
                    $locatorName = '';
                $withdraw = $casinoApiHandler->Withdraw( $login, $amount, $tracking1, $tracking2, $tracking3, $tracking4, $methodname, $locatorName );
                break;

            case "RTG2":
                $isplayerAPI = 0;
                $casinoApiHandler = $this->configureRTG2($serviceID, $url, $isplayerAPI);
                if (is_null($locatorName)) 
                    $locatorName = '';
                $withdraw = $casinoApiHandler->Withdraw( $login, $amount, $tracking1, $tracking2, $tracking3, $tracking4, $methodname, $locatorName );
                break;
                
            case "HAB":
                $casinoApiHandler = $this->configureHAB($url, $capiusername, $capipassword); //BrandID, APIKey
                //EDITED CCT 07/18/2018 BEGIN
                //$withdraw = $casinoApiHandler->Withdraw( $login, $amount, $password, $tracking1 );
                $withdraw = $casinoApiHandler->Withdraw( $login, $amount, $tracking1, $tracking2, $tracking3, $tracking4, $methodname, $locatorName, $password);
                //EDITED CCT 07/18/2018 END
                break;
        }
        return $withdraw;
    }

    public function getCasinoAccountInfo($login, $serviceID, $url,$password, $providername='', $usermode='')
    {
        $isplayerAPI = 0;
        
        switch($providername)
        {
            case "RTG":
                if($usermode == '0') 
                {
                    $casinoApiHandler = $this->configureRTG($serviceID, $url, $isplayerAPI);
                } 
                else 
                {
                    $casinoApiHandler = $this->configureRTG2($serviceID, $url, $isplayerAPI);
                }
                break;
            case "RTG2":
                $casinoApiHandler = $this->configureRTG2($serviceID, $url, $isplayerAPI);
                break;
        } 
        $accountInfo = $casinoApiHandler->GetAccountInfo($login,$password);
        return $accountInfo;
    }

    public function TransSearchInfo($providername, $serviceID, $url, $login, $capiusername, $capipassword, $capiplayername, 
                               $capiserverID, $tracking1, $tracking2, $tracking3, $tracking4, $usermode='')
    {
        switch ($providername)
        {
            case "RTG":
                $isplayerAPI = 0;
                if($usermode == '0') 
                {
                    $casinoApiHandler = $this->configureRTG($serviceID, $url, $isplayerAPI);
                } 
                else 
                {
                    $casinoApiHandler = $this->configureRTG2($serviceID, $url, $isplayerAPI);
                }
                $transSearchInfo = $casinoApiHandler->TransactionSearchInfo( $login, $tracking1, $tracking2, $tracking3, $tracking4 );
                break;
                
            case "RTG2":
                $isplayerAPI = 0;
                $casinoApiHandler = $this->configureRTG2($serviceID, $url, $isplayerAPI);
                $transSearchInfo = $casinoApiHandler->TransactionSearchInfo( $login, $tracking1, $tracking2, $tracking3, $tracking4 );
                break;

            case "HAB":
                $casinoApiHandler = $this->configureHAB($url, $capiusername, $capipassword); //BrandID, APIKey
                $transSearchInfo = $casinoApiHandler->TransactionSearchInfo($login, $tracking1, $tracking2, $tracking3, $tracking4);
                break;

            case "EB":
                return array('IsSucceed'=>true, 'ErrorCode' => 0, 'ErrorMessage'=>null, 
                            'TransactionInfo' => array('EB'=>array('TransactionStatus'=>'TRANSACTIONSTATUS_APPROVED', 'TransactionId'=>'')));              
                break;
        }
        return $transSearchInfo;
    }
    
    public function Deposit($providername, $serviceID, $url, $login, $capiusername, $capipassword, $capiplayername, 
                               $capiserverID, $amount, $tracking1, $tracking2, $tracking3, $tracking4, $usermode='')
    {
        switch ($providername)
        {
            case "RTG2":
                $isplayerAPI = 0;
                $casinoApiHandler = $this->configureRTG2($serviceID, $url, $isplayerAPI);
                $deposit = $casinoApiHandler->Deposit($login, $amount, $tracking1, $tracking2, $tracking3, $tracking4);
                break;
            
            case "RTG":
                $isplayerAPI = 0;
                if($usermode == '0') 
                {
                    $casinoApiHandler = $this->configureRTG($serviceID, $url, $isplayerAPI);
                } 
                else 
                {
                    $casinoApiHandler = $this->configureRTG2($serviceID, $url, $isplayerAPI);
                }
                $deposit = $casinoApiHandler->Deposit($login, $amount, $tracking1, $tracking2, $tracking3, $tracking4);
                break;
        }
        return $deposit;
    }
    
    public function ChangePlayerClassification($casinoName, $url, $pid, $playerClassID, $userID, $serverID)
    {
        if(strpos($casinoName, 'RTG2') !== false)
        {
            $isplayerAPI = 1;
            $casinoApiHandler = $this->configureRTG2($serverID, $url, $isplayerAPI);
        }

        if(!$casinoApiHandler)
        {
            $response = false;
        }
        else
        {
            $response = $casinoApiHandler->ChangePlayerClassification($pid, $playerClassID, $userID);
        }
        return $response;
    }
}
?>
