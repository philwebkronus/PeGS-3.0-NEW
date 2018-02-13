<?php

/**
 * UB API calls to RTG and MG
 *
 * @author gvjagolino
 * @created on: Feb 02, 2012
 */

include 'CasinoCAPIHandlerUB.class.php';

class CasinoGamingCAPIUB {
    
    /**
     * Configuration parameters that must pass to RTG
     * @param int $serverID
     * @param string $url
     * @param int $isplayerAPI
     * @return array CasinoCAPIHandler 
     */
    public function configureRTG($serverID, $url, $isplayerAPI, $usermode)
    {
        //check if player api was called
        if($isplayerAPI == 1)
        {
            $configuration = array( 'URI' => $url,
                                'isCaching' => FALSE,
                                'isDebug' => TRUE,
                                'certFilePath' => RTGCerts_DIR . $serverID  . '/cert.pem',
                                'keyFilePath' => RTGCerts_DIR . $serverID  . '/key.pem',
                             );
        }
        else
        {
            //config for RTG, need to change depositMethodID and withdrawalMethodID when put on production
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
        
        if($usermode == 1){
            $_CasinoAPIHandler = new CasinoCAPIHandlerUB(CasinoCAPIHandlerUB::RTG, $configuration);
        }
        else{
            $_CasinoAPIHandler = new CasinoCAPIHandler(CasinoCAPIHandler::RTG, $configuration);
        }
        
        
        // check if connected
        if (!(bool)$_CasinoAPIHandler->IsAPIServerOK()) {
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
    //    if (!(bool)$_CasinoAPIHandler->IsAPIServerOK()) {
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
    //    public function configurePT($url,$capiusername,$capisecretkey)
    //    {
    //            $configuration = array('URI' => $url,
    //                                   'isCaching' => FALSE,
    //                                   'isDebug' => TRUE,
    //                                   'authLogin'=>$capiusername,
    //                                    'secretKey'=>$capisecretkey);
    //
    //            $_CasinoAPIHandler = new CasinoCAPIHandler(CasinoCAPIHandler::PT, $configuration);
    //
    //            // check if connected
    //            if (!(bool)$_CasinoAPIHandler->IsAPIServerOK()) {
    //                $message = 'Can\'t connect to PT';
    //                self::throwError($message);
    //            }
    //            return $_CasinoAPIHandler;
    //    }
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
    public function createTerminalAccount($vprovidername, $serviceID, $url, $login,
        $password, $aid, $currency, $email, $fname,
        $lname, $dayphone, $evephone, $addr1, $addr2,
        $city, $country, $province,
        $zip, $userID, $birthdate, $fax, $occupation,
        $sex, $alias, $casinoID, $ip, $mac, $downloadID,
        $clientID, $putInAffPID, $calledFromCasino,
        $hashedPassword,$agentID,$currentPosition,
        $thirdPartyPID, $capiusername, $capipassword,
        $capiplayername, $capiserverID,$isVIP='',$usermode='')
    {
        //check if this will be created to MG
        switch (true){
                // Comment Out CCT 02/06/2018 BEGIN
                //case strstr($vprovidername, "MG"):
                //        $casinoApiHandler = $this->configureMG($serviceID, $url, $capiusername,
                //        $capipassword, $capiplayername, $capiserverID);
                //    if($usermode == 1){
                //        $createTerminalResult = array("IsSucceed"=>true); 
                //    }
                //    else{
                //        $createTerminalResult = $casinoApiHandler->CreateTerminalAccount($login, $password,
                //        $aid, $currency, $email, $fname, $lname, $dayphone, $evephone,
                //        $addr1, $addr2, $city, $country, $province, $zip, $userID,
                //        $birthdate, $fax, $occupation, $sex, $alias, $casinoID, $ip,
                //        $mac, $downloadID, $clientID, $putInAffPID, $calledFromCasino,
                //        $hashedPassword, $agentID, $currentPosition, $thirdPartyPID);
                //    }
                //        break;
                // Comment Out CCT 02/06/2018 END
                case strstr($vprovidername, "RTG"):
                    if($usermode == 1){
                        $createTerminalResult = array("IsSucceed"=>true); 
                    }
                    else{
                        $isplayerAPI = 1;
                        $casinoApiHandler = $this->configureRTG($serviceID, $url, $isplayerAPI,$usermode);
                        $createTerminalResult = $casinoApiHandler->CreateTerminalAccount($login, $password,
                        $aid, $currency, $email, $fname, $lname, $dayphone, $evephone,
                        $addr1, $addr2, $city, $country, $province, $zip, $userID,
                        $birthdate, $fax, $occupation, $sex, $alias, $casinoID, $ip,
                        $mac, $downloadID, $clientID, $putInAffPID, $calledFromCasino,
                        $hashedPassword, $agentID, $currentPosition, $thirdPartyPID);
                    }
                        
                        break;
                // Comment Out CCT 02/06/2018 BEGIN
                //case strstr($vprovidername, "PT"):
                //        $casinoApiHandler = $this->configurePT($url, $capiusername, $capipassword);
//                        $createTerminalResult = $casinoApiHandler->CreateTerminalAccount($login, $password,
//                        $aid, $currency, $email, $fname, $lname, $dayphone, $evephone,
//                        $addr1, $addr2, $city, $country, $province, $zip, $userID,
//                        $birthdate, $fax, $occupation, $sex, $alias, $casinoID, $ip,
//                        $mac, $downloadID, $clientID, $putInAffPID, $calledFromCasino,
//                        $hashedPassword, $agentID, $currentPosition, $thirdPartyPID,$isVIP);
                         //always pass true in order to mapped PT casino in a specific terminal
                //         $createTerminalResult = array("IsSucceed"=>true); 
                //        break;
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
    public function changeTerminalPassword($vprovidername, $serviceID, $url,
        $casinoID, $login, $oldpassword, $newpassword,
        $capiusername, $capipassword, $capiplayername,
        $capiserverID,$usermode)
    {

        switch(true){
            case (strstr($vprovidername, "RTG")):
                $isplayerAPI = 1;
                $casinoApiHandler = $this->configureRTG($serviceID, $url, $isplayerAPI,$usermode);
                $changePwdResult = $casinoApiHandler->ChangeTerminalPassword($casinoID, $login, $oldpassword, $newpassword);
                break;
            // Comment Out CCT 02/06/2018 BEGIN            
            //case (strstr($vprovidername, "MG")):
            //    $casinoApiHandler = $this->configureMG($serviceID, $url, $capiusername,
            //    $capipassword, $capiplayername, $capiserverID);
            //    $changePwdResult = $casinoApiHandler->ChangeTerminalPassword($casinoID, $login, $oldpassword, $newpassword);
            //    break;
            //case (strstr($vprovidername, "PT")):
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
            default :
                echo 'Invalid Provider Name';
        }
        
        return $changePwdResult;
    }

    public function getBalance($providername, $serviceID, $url, $login, 
                               $capiusername, $capipassword, $capiplayername, 
                               $capiserverID)
    {        
        switch (true)
        {
            case (strstr($providername, "RTG")):
                $isplayerAPI = 0;
                $casinoApiHandler = $this->configureRTG($serviceID, $url, $isplayerAPI);
                $balanceinfo = $casinoApiHandler->GetBalance($login);
                break;
            // Comment Out CCT 02/06/2018 BEGIN            
            //case (strstr($providername, "MG")):
            //    $casinoApiHandler = $this->configureMG($serviceID, $url, $capiusername, $capipassword, $capiplayername, $capiserverID);
            //    $balanceinfo = $casinoApiHandler->GetBalance($login);
            //    break;
            //case (strstr($providername, "PT")):
            //    $casinoApiHandler = $this->configurePT($url, $capiusername, $capipassword);
            //    $balanceinfo = $casinoApiHandler->GetBalance($login);
            //    break;
            // Comment Out CCT 02/06/2018 END
        }
        
        if(!isset($balanceinfo['BalanceInfo']['Balance'])) {
            $message = 'Error: Cannot get balance';
            //self::throwError($message);
        }
        if(isset($balanceinfo['BalanceInfo']['Balance']))
        {
            $terminal_balance = $balanceinfo['BalanceInfo']['Balance'];
            $redeemable_amount = 0;
            if(isset($balanceinfo['BalanceInfo']['Redeemable'])) {
                $redeemable_amount = $balanceinfo['BalanceInfo']['Redeemable'];
            } else {
                $redeemable_amount = $terminal_balance;
            }
            return $redeemable_amount;
        }
        else
        {
            return $message;
        }
        
        
    }
    
    public function Withdraw($providername, $serviceID, $url, $login, 
                               $capiusername, $capipassword, $capiplayername, 
                               $capiserverID,$amount, $tracking1, $tracking2, $tracking3, $tracking4, $methodname)
    {
        switch (true)
        {
            case (strstr($providername, "RTG")):
                $isplayerAPI = 0;
                $casinoApiHandler = $this->configureRTG($serviceID, $url, $isplayerAPI);
                $withdraw = $casinoApiHandler->Withdraw( $login, $amount, $tracking1, $tracking2, $tracking3, $tracking4 );
                break;
            // Comment Out CCT 02/06/2018 BEGIN            
            //case (strstr($providername, "MG")):
            //    $casinoApiHandler = $this->configureMG($serviceID, $url, $capiusername, $capipassword, $capiplayername, $capiserverID);
            //    $withdraw = $casinoApiHandler->Withdraw( $login, $amount, $tracking1, $tracking2, $tracking3, $tracking4, $methodname );
            //    break;
            //case (strstr($providername, "PT")):
            //    $casinoApiHandler = $this->configurePT($url, $capiusername, $capipassword);
            //    $withdraw = $casinoApiHandler->Withdraw($login,$amount,$tracking1,$tracking2);
            //    break;
            // Comment Out CCT 02/06/2018 END
        }
        return $withdraw;
    }

    public function getCasinoAccountInfo($login, $serviceID, $url,$password){
        $isplayerAPI = 0;
        $casinoApiHandler = $this->configureRTG($serviceID, $url, $isplayerAPI);
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
    
    public function TransSearchInfo($providername, $serviceID, $url, $login, 
                               $capiusername, $capipassword, $capiplayername, 
                               $capiserverID, $tracking1, $tracking2, $tracking3, $tracking4)
    {
        switch (true)
        {
            case (strstr($providername, "RTG")):
                $isplayerAPI = 0;
                $casinoApiHandler = $this->configureRTG($serviceID, $url, $isplayerAPI);
                $transSearchInfo = $casinoApiHandler->TransactionSearchInfo( $login, $tracking1, $tracking2, $tracking3, $tracking4 );
                break;
            // Comment Out CCT 02/06/2018 BEGIN            
            //case (strstr($providername, "MG")):
            //    $casinoApiHandler = $this->configureMG($serviceID, $url, $capiusername, $capipassword, $capiplayername, $capiserverID);
            //    $transSearchInfo = $casinoApiHandler->TransactionSearchInfo( $login, $tracking1, $tracking2, $tracking3, $tracking4 );
            //    break;
            //case (strstr($providername, "PT")):
            //    $casinoApiHandler = $this->configurePT($url, $capiusername, $capipassword);
            //    $transSearchInfo = $casinoApiHandler->TransactionSearchInfo( $login, $tracking1, $tracking2, $tracking3, $tracking4 );
            //    break;
            // Comment Out CCT 02/06/2018 END
        }
        return $transSearchInfo;
    }
        
    public function Deposit($providername, $serviceID, $url, $login, $capiusername, $capipassword, $capiplayername, 
                               $capiserverID, $amount, $tracking1, $tracking2, $tracking3, $tracking4)
    {
        switch (true)
        {
            case (strstr($providername, "RTG")):
                $isplayerAPI = 0;
                $casinoApiHandler = $this->configureRTG($serviceID, $url, $isplayerAPI);
                $deposit = $casinoApiHandler->Deposit($login, $amount, $tracking1, $tracking2, $tracking3, $tracking4);
                break;
            // Comment Out CCT 02/06/2018 BEGIN            
            //case (strstr($providername, "MG")):
            //    $casinoApiHandler = $this->configureMG($serviceID, $url, $capiusername, $capipassword, $capiplayername, $capiserverID);
            //    $deposit = $casinoApiHandler->Deposit($login, $amount, $tracking1, $tracking2, $tracking3, $tracking4);
            //    break;
            //case (strstr($providername, "PT")):
            //    $casinoApiHandler = $this->configurePT($url, $capiusername, $capipassword);
            //    $deposit = $casinoApiHandler->Deposit($login, $amount, $tracking1, $tracking2, $tracking3, $tracking4);
            //    break;
            // Comment Out CCT 02/06/2018 END
        }
        return $deposit;
    }
    
    public function ChangePlayerClassification($casinoName, $url, $pid, $playerClassID, $userID, $serverID)
        {
            if(strpos($casinoName, 'RTG2') !== false){
                $isplayerAPI = 0;
                $casinoApiHandler = $this->configureRTG2($serverID, $url, $isplayerAPI);
            }
            
            if(!$casinoApiHandler){
                $response = false;
            }
            else{
                $response = $casinoApiHandler->ChangePlayerClassification($pid, $playerClassID, $userID);
            }
            
            return $response;
        }
}
    
?>

