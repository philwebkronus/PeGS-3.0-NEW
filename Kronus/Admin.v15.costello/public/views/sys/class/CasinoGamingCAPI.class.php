<?php
/**
 * Common API calls to RTG and MG
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
    
    // CCT ADDED 12/14/2017 BEGIN
    public function validateHabCasinoAccount($url, $capiusername, $capipassword, $login, $password)
    {
        $casinoApiHandler = $this->configureHAB($url, $capiusername, $capipassword); //BrandID, APIKey
        $accExists = $casinoApiHandler->GetAccountInfo( $login, $password);
        return $accExists;
    }    
    // CCT ADDED 12/14/2017 END

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
                                'certFilePath' => RTGCerts_DIR . $serverID  . '/cert-key.pem',
                                'keyFilePath' => RTGCerts_DIR . $serverID  . '/cert-key.pem',
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
     * Configuration parameters that must pass to MG
     * @param int $serverID
     * @param string $url
     * @param string $capiusername
     * @param string $capipassword
     * @param string $capiplayername
     * @param int $capiserverID
     * @return array CasinoCAPIHandler 
     */
    // Comment Out CCT 02/06/2018 BEGIN
    //public function configureMG($serverID, $url, $capiusername, $capipassword, $capiplayername, $capiserverID)
    //{
    //    $configuration = array('URI' => $url,
    //                           'isCaching' => FALSE,
    //                           'isDebug' => TRUE,
    //                           'authLogin'=>$capiusername,
    //                           'authPassword'=>$capipassword,
    //                           'playerName'=>$capiplayername,
    //                           'serverID'=>$capiserverID);
    //    
    //    $_CasinoAPIHandler = new CasinoCAPIHandler(CasinoCAPIHandler::MG, $configuration);
    //    
    //    // check if connected
    //    if (!(bool)$_CasinoAPIHandler->IsAPIServerOK()) 
    //    {
    //        $message = 'Can\'t connect to MG';
    //        self::throwError($message);
    //    }
    //    return $_CasinoAPIHandler;
    //}
    // Comment Out CCT 02/06/2018 END
    
    /**
     * Configuration parameters that must pass to PT
     * @param string $url
     * @param string $capiusername
     * @param string $capipassword
     * @param string $capisecretkey
     * @return array CasinoCAPIHandler 
     */
    // Comment Out CCT 02/06/2018 BEGIN
    //public function configurePT($url,$capiusername,$capisecretkey)
    //{
    //    $configuration = array('URI' => $url,
    //                           'isCaching' => FALSE,
    //                           'isDebug' => TRUE,
    //                           'authLogin'=>$capiusername,
    //                            'secretKey'=>$capisecretkey);
    //
    //    $_CasinoAPIHandler = new CasinoCAPIHandler(CasinoCAPIHandler::PT, $configuration);
    //    
    //    // check if connected
    //    if (!(bool)$_CasinoAPIHandler->IsAPIServerOK()) 
    //    {
    //        $message = 'Can\'t connect to PT';
    //        self::throwError($message);
    //    }
    //    return $_CasinoAPIHandler;
    //}
    // Comment Out CCT 02/06/2018 END
    
    /**
     * handles error if api connection is not reachable
     */
    public static function throwError($message)
    {
        header('HTTP/1.0 404 Not Found');
        return $message;
    }
    
    /**
     * create terminal account whether RTG / MG
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
        //check if this will be created to MG
        switch ($vprovidername)
        {
            // ADDED CCT 01/19/2018 BEGIN
            case "EB":
                $createTerminalResult = array('IsSucceed'=>true, 'ErrorMessage'=>'Service Successfully Created', 'ErrorCode'=> 0, 'ErrorID'=> 0); 
                break;            
            // ADDED CCT 01/19/2018 END            
            // ADDED CCT 12/14/2017 BEGIN
            case "HAB":
                $casinoApiHandler = $this->configureHAB($url, $capiusername, $capipassword);  //BrandID, APIKey
                $createTerminalResult = $casinoApiHandler->CreateTerminalAccount($login, $password,
                $aid, $currency, $email, $fname, $lname, $dayphone, $evephone,
                $addr1, $addr2, $city, $country, $province, $zip, $userID,
                $birthdate, $fax, $occupation, $sex, $alias, $casinoID, $ip,
                $mac, $downloadID, $clientID, $putInAffPID, $calledFromCasino,
                $hashedPassword, $agentID, $currentPosition, $thirdPartyPID, $isVIP, $usermode);
                break;            
            // ADDED CCT 12/14/2017 END
            // Comment Out CCT 02/06/2018 BEGIN
            //case  "MG":
            //    $casinoApiHandler = $this->configureMG($serviceID, $url, $capiusername,
            //    $capipassword, $capiplayername,
            //    $capiserverID);
            //    $createTerminalResult = $casinoApiHandler->CreateTerminalAccount($login, $password,
            //    $aid, $currency, $email, $fname, $lname, $dayphone, $evephone,
            //    $addr1, $addr2, $city, $country, $province, $zip, $userID,
            //    $birthdate, $fax, $occupation, $sex, $alias, $casinoID, $ip,
            //    $mac, $downloadID, $clientID, $putInAffPID, $calledFromCasino,
            //    $hashedPassword, $agentID, $currentPosition, $thirdPartyPID);
            //    break;
            // Comment Out CCT 02/06/2018 END
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
                if($usermode == '0') 
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
            // Comment Out CCT 02/06/2018 BEGIN
            //case "PT":
            //    $casinoApiHandler = $this->configurePT($url, $capiusername, $capipassword);
//                $createTerminalResult = $casinoApiHandler->CreateTerminalAccount($login, $password,
//                $aid, $currency, $email, $fname, $lname, $dayphone, $evephone,
//                $addr1, $addr2, $city, $country, $province, $zip, $userID,
//                $birthdate, $fax, $occupation, $sex, $alias, $casinoID, $ip,
//                $mac, $downloadID, $clientID, $putInAffPID, $calledFromCasino,
//                $hashedPassword, $agentID, $currentPosition, $thirdPartyPID,$isVIP);
//                always pass true in order to mapped PT casino in a specific terminal
            //    $createTerminalResult = array("IsSucceed"=>true); 
            //    break;
            // Comment Out CCT 02/06/2018 END
            default:
                echo 'Invalid Casino Name.';
                break;
        }
        return $createTerminalResult;
    }
    
    /**
     * Updates terminal password whether RTG / MG
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
                $changePwdResult = $casinoApiHandler->ChangeTerminalPassword($casinoID, $login, $oldpassword, $newpassword);
                break;
            // Comment Out CCT 02/06/2018 BEGIN
            //case "MG":
            //    $casinoApiHandler = $this->configureMG($serviceID, $url, $capiusername, $capipassword, $capiplayername, $capiserverID);
            //    $changePwdResult = $casinoApiHandler->ChangeTerminalPassword($casinoID, $login, $oldpassword, $newpassword);
            //    break;
            //case "PT":
                /** Disable change of terminal based account password in PT 05/29/13
                $casinoApiHandler = $this->configurePT($url, $capiusername, $capipassword);
                $changePwdResult = $casinoApiHandler->ChangeTerminalPassword($casinoID, $login, $oldpassword, $newpassword);
                 */
                //always pass true in order to mapped PT casino in a specific terminal
            //    $changePwdResult = array('IsSucceed'=>true,'PlayerInfo'=>array('transaction'=>
            //                       array('@attributes'=>array('result'=>'OK')))); 
                //['PlayerInfo']['transaction']['@attributes']['result'] != "OK"
            //    break;
            // Comment Out CCT 02/06/2018 END
            // ADDED CCT 12/14/2017 BEGIN
            case "HAB":
                $casinoApiHandler = $this->configureHAB($url, $capiusername, $capipassword);  //BrandID, APIKey
                $changePwdResult = $casinoApiHandler->ChangeTerminalPassword($casinoID, $login, $oldpassword, $newpassword, $usermode);
                break;   
            // ADDED CCT 12/14/2017 END
            default :
                echo 'Invalid Provider Name';
        }
        return $changePwdResult;
    }

    //public function getBalance($providername, $serviceID, $url, $login, $capiusername, $capipassword, $capiplayername, $capiserverID, $usermode="")
    public function getBalance($providername, $serviceID, $url, $login, $capiusername, $capipassword, $capiplayername, $capiserverID, $usermode="", 
            $password = "") // EDITED CCT 11/24/2017
    {
        switch ($providername)
        {
            // CCT ADDED 01/22/2018 BEGIN
            case "EB":
                $balanceinfo = array( 'IsSucceed' => true, 'ErrorCode' => 0, 'ErrorMessage' => null, 'BalanceInfo' => array( 'Balance' => 0 ));
                break;
            // CCT ADDED 01/22/2018 END    
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
            // Comment Out CCT 02/06/2018 BEGIN
            //case "MG":
            //    $casinoApiHandler = $this->configureMG($serviceID, $url, $capiusername, $capipassword, $capiplayername, $capiserverID);
            //    $balanceinfo = $casinoApiHandler->GetBalance($login);
            //    break;
            //case "PT":
            //    $casinoApiHandler = $this->configurePT($url, $capiusername, $capipassword);
            //    $balanceinfo = $casinoApiHandler->GetBalance($login);
            //    break;
            // Comment Out CCT 02/06/2018 END
            case "HAB": // ADDED CCT 11/24/2017
                $casinoApiHandler = $this->configureHAB($url, $capiusername, $capipassword); //BrandID, APIKey
                $balanceinfo = $casinoApiHandler->GetBalance($login, $password);
                break;            
        }

        if(!isset($balanceinfo['BalanceInfo']['Balance'])) 
        {
            $message = 'Error: Cannot get balance';
            //self::throwError($message);
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
    
    //public function Withdraw($providername, $serviceID, $url, $login, $capiusername, $capipassword, $capiplayername, 
    //                        $capiserverID,$amount, $tracking1, $tracking2, $tracking3, $tracking4, $methodname, $usermode = 0, $locatorName = null)
    public function Withdraw($providername, $serviceID, $url, $login, $capiusername, $capipassword, $capiplayername, 
                            $capiserverID,$amount, $tracking1, $tracking2, $tracking3, $tracking4, $methodname, $usermode = 0, $locatorName = null, 
                            $password= "") // EDITED CCT 11/27/2017
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
            // Comment Out CCT 02/06/2018 BEGIN                
            //case "MG":
            //    $casinoApiHandler = $this->configureMG($serviceID, $url, $capiusername, $capipassword, $capiplayername, $capiserverID);
            //    $withdraw = $casinoApiHandler->Withdraw( $login, $amount, $tracking1, $tracking2, $tracking3, $tracking4, $methodname );
            //    break;
            //case "PT":
            //    $casinoApiHandler = $this->configurePT($url, $capiusername, $capipassword);
            //    $withdraw = $casinoApiHandler->Withdraw($login,$amount,$tracking1,$tracking2);
            //    break;
            // Comment Out CCT 02/06/2018 END
            //ADDED CCT 11/27/2017 BEGIN
            case "HAB":
                $casinoApiHandler = $this->configureHAB($url, $capiusername, $capipassword); //BrandID, APIKey
                $withdraw = $casinoApiHandler->Withdraw( $login, $amount, $password, $tracking1 );
                break;
            //ADDED CCT 11/27/2017 END
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

    // Comment Out CCT 02/06/2018 BEGIN
    //public function validateCasinoAccount($login, $serviceID, $url, $capiusername, $capipassword, $capiplayername, $capiserverID)
    //{
    //    $casinoApiHandler = $this->configureMG($serviceID, $url, $capiusername, $capipassword, $capiplayername, $capiserverID);
    //    $accExists = $casinoApiHandler->GetAccountInfo($login,$password = '');
    //    return $accExists;
    //}
    // Comment Out CCT 02/06/2018 END
    // 
    // Comment Out CCT 02/06/2018 BEGIN
    //public function validatePTCasinoAccount($login, $url,$capiusername,$capisecretkey,$password)
    //{
    //    $casinoApiHandler = $this->configurePT($url, $capiusername, $capisecretkey);
    //    $accExists = $casinoApiHandler->GetAccountInfo($login,$password);
    //    return $accExists;
    //}
    // Comment Out CCT 02/06/2018 END
    
    // Comment Out CCT 02/06/2018 BEGIN
    //public function resetCasinoPassword($login, $password, $serviceID, $url, $capiusername, $capipassword, $capiplayername, $capiserverID)
    //{
    //    $casinoApiHandler = $this->configureMG($serviceID, $url, $capiusername,
    //    $capipassword, $capiplayername, $capiserverID);
    //    $resetPassword = $casinoApiHandler->ResetPassword($login, $password);
    //    return $resetPassword;
    //}
    // Comment Out CCT 02/06/2018 END
    
    // Comment Out CCT 02/06/2018 BEGIN
    //public function unlockCasinoAccount($login, $serviceID, $url, $capiusername, $capipassword, $capiplayername, $capiserverID)
    //{
    //    $casinoApiHandler = $this->configureMG($serviceID, $url, $capiusername, $capipassword, $capiplayername, $capiserverID);
    //    $frozen = 0;
    //    $resetPassword = $casinoApiHandler->unfreezePlayer($login, $frozen);
    //    return $resetPassword;
    //}
    // Comment Out CCT 02/06/2018 END
    
    // Comment Out CCT 02/06/2018 BEGIN
    //public function unfreeze($login,$url,$capiusername, $capisecretkey, $frozen)
    //{
    //    $casinoApiHandler = $this->configurePT($url, $capiusername, $capisecretkey);
    //    $unfreeze = $casinoApiHandler->unfreezePlayer($login, $frozen);
    //    return $unfreeze;
    //}
    // Comment Out CCT 02/06/2018 END
    
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
            // Comment Out CCT 02/06/2018 BEGIN
            //case "MG":
            //    $casinoApiHandler = $this->configureMG($serviceID, $url, $capiusername, $capipassword, $capiplayername, $capiserverID);
            //    $transSearchInfo = $casinoApiHandler->TransactionSearchInfo( $login, $tracking1, $tracking2, $tracking3, $tracking4 );
            //    break;
            //case "PT":
            //    $casinoApiHandler = $this->configurePT($url, $capiusername, $capipassword);
            //    $transSearchInfo = $casinoApiHandler->TransactionSearchInfo( $login, $tracking1, $tracking2, $tracking3, $tracking4 );
            //    break;
            // Comment Out CCT 02/06/2018 END
            //ADDED CCT 12/27/2017 BEGIN
            case "HAB":
                $casinoApiHandler = $this->configureHAB($url, $capiusername, $capipassword); //BrandID, APIKey
                $transSearchInfo = $casinoApiHandler->TransactionSearchInfo($login, $tracking1, $tracking2, $tracking3, $tracking4);
                break;
            //ADDED CCT 12/27/2017 END     
            //ADDED CCT 01/23/2018 BEGIN
            case "EB":
                return array('IsSucceed'=>true, 'ErrorCode' => 0, 'ErrorMessage'=>null, 
                            'TransactionInfo' => array('EB'=>array('TransactionStatus'=>'TRANSACTIONSTATUS_APPROVED', 'TransactionId'=>'')));              
                break;
            //ADDED CCT 01/23/2018 END     
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
            // Comment Out CCT 02/06/2018 BEGIN    
            //case "MG":
            //    $casinoApiHandler = $this->configureMG($serviceID, $url, $capiusername, $capipassword, $capiplayername, $capiserverID);
            //    $deposit = $casinoApiHandler->Deposit($login, $amount, $tracking1, $tracking2, $tracking3, $tracking4);
            //    break;
            //case "PT":
            //    $casinoApiHandler = $this->configurePT($url, $capiusername, $capipassword);
            //    $deposit = $casinoApiHandler->Deposit($login, $amount, $tracking1, $tracking2, $tracking3, $tracking4);
            //    break;
            // Comment Out CCT 02/06/2018 END
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