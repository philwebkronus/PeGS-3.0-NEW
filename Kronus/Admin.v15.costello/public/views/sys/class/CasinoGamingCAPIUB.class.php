<?php
/**
 * UB API calls to RTG and MG
 * @author gvjagolino
 * @created on: Feb 02, 2012
 */
include 'CasinoCAPIHandlerUB.class.php';

class CasinoGamingCAPIUB 
{
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
        
        if($usermode == 1)
        {
            $_CasinoAPIHandler = new CasinoCAPIHandlerUB(CasinoCAPIHandlerUB::RTG, $configuration);
        }
        else
        {
            $_CasinoAPIHandler = new CasinoCAPIHandler(CasinoCAPIHandler::RTG, $configuration);
        }
        
        // check if connected
        if (!(bool)$_CasinoAPIHandler->IsAPIServerOK()) 
        {
            $message = 'Can\'t connect to RTG';
            self::throwError($message);
        }
        
        return $_CasinoAPIHandler;
    }
    
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
    public function createTerminalAccount($vprovidername, $serviceID, $url, $login, $password, $aid, $currency, 
        $email, $fname, $lname, $dayphone, $evephone, $addr1, $addr2, $city, $country, $province, $zip, 
        $userID, $birthdate, $fax, $occupation, $sex, $alias, $casinoID, $ip, $mac, $downloadID, $clientID, 
        $putInAffPID, $calledFromCasino, $hashedPassword,$agentID,$currentPosition, $thirdPartyPID, 
        $capiusername, $capipassword, $capiplayername, $capiserverID,$isVIP='',$usermode='')
    {
        //check if this will be created 
        switch (true)
        {
            case strstr($vprovidername, "RTG"):
                if($usermode == 1)
                {
                    $createTerminalResult = array("IsSucceed"=>true); 
                }
                else
                {
                    $isplayerAPI = 1;
                    $casinoApiHandler = $this->configureRTG($serviceID, $url, $isplayerAPI,$usermode);
                    $createTerminalResult = $casinoApiHandler->CreateTerminalAccount($login, $password,
                        $aid, $currency, $email, $fname, $lname, $dayphone, $evephone, $addr1, $addr2, 
                        $city, $country, $province, $zip, $userID, $birthdate, $fax, $occupation, 
                        $sex, $alias, $casinoID, $ip, $mac, $downloadID, $clientID, $putInAffPID, 
                        $calledFromCasino, $hashedPassword, $agentID, $currentPosition, $thirdPartyPID);
                }
                break;

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
        $capiusername, $capipassword, $capiplayername, $capiserverID,$usermode)
    {
        switch(true)
        {
            case (strstr($vprovidername, "RTG")):
                $isplayerAPI = 1;
                $casinoApiHandler = $this->configureRTG($serviceID, $url, $isplayerAPI,$usermode);
                $changePwdResult = $casinoApiHandler->ChangeTerminalPassword($casinoID, $login, $oldpassword, $newpassword);
                break;

            default :
                echo 'Invalid Provider Name';
        }
        
        return $changePwdResult;
    }

    public function getBalance($providername, $serviceID, $url, $login, $capiusername, $capipassword, $capiplayername, $capiserverID)
    {        
        switch (true)
        {
            case (strstr($providername, "RTG")):
                $isplayerAPI = 0;
                $casinoApiHandler = $this->configureRTG($serviceID, $url, $isplayerAPI);
                $balanceinfo = $casinoApiHandler->GetBalance($login);
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
            $capiserverID,$amount, $tracking1, $tracking2, $tracking3, $tracking4, $methodname)
    {
        switch (true)
        {
            case (strstr($providername, "RTG")):
                $isplayerAPI = 0;
                $casinoApiHandler = $this->configureRTG($serviceID, $url, $isplayerAPI);
                $withdraw = $casinoApiHandler->Withdraw( $login, $amount, $tracking1, $tracking2, $tracking3, $tracking4 );
                break;
        }
        return $withdraw;
    }

    public function getCasinoAccountInfo($login, $serviceID, $url,$password)
    {
        $isplayerAPI = 0;
        $casinoApiHandler = $this->configureRTG($serviceID, $url, $isplayerAPI);
        $accountInfo = $casinoApiHandler->GetAccountInfo($login,$password);
        return $accountInfo;
    }

    public function TransSearchInfo($providername, $serviceID, $url, $login, $capiusername, $capipassword, $capiplayername, 
                $capiserverID, $tracking1, $tracking2, $tracking3, $tracking4)
    {
        switch (true)
        {
            case (strstr($providername, "RTG")):
                $isplayerAPI = 0;
                $casinoApiHandler = $this->configureRTG($serviceID, $url, $isplayerAPI);
                $transSearchInfo = $casinoApiHandler->TransactionSearchInfo( $login, $tracking1, $tracking2, $tracking3, $tracking4 );
                break;
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
        }
        return $deposit;
    }
    
    public function ChangePlayerClassification($casinoName, $url, $pid, $playerClassID, $userID, $serverID)
    {
        if(strpos($casinoName, 'RTG2') !== false)
        {
            $isplayerAPI = 0;
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
