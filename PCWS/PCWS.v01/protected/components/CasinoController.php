<?php

class CasinoController{
    
    public function EncryptCredentials($username, $password){
        Yii::import('application.components.CasinoAPI.RealtimeGamingWCFRemoteAPI');
        
        $url = Yii::app()->params->forcetapi;
        $certpath = Yii::app()->params->rtg_certkey_dir.'19/cert-key.pem';
        
        $rtgremote = new RealtimeGamingWCFRemoteAPI($url, $certpath, '');
        
        $result = $rtgremote->remoteAuth($username, $password);
        
        if(!empty($result)){
            if($result['encryptCredentialsResult']['Success'] == true && $result['encryptCredentialsResult']['ErrorText'] == ''){
                $result = $result['encryptCredentialsResult'];
            }
            else{
                $result = '';
            }
        }
        else{
            $result = '';
        }
        return $result;
    }
    
    public function GetBalance($username){
        Yii::import('application.components.CasinoAPI.RealtimeGamingCashierAPI2');
        
        $url = Yii::app()->params->abbottcashierapi;
        $certpath = Yii::app()->params->rtg_certkey_dir.'19/cert.pem';
        $keypath = Yii::app()->params->rtg_certkey_dir.'19/key.pem';
        $rtgcashier = new RealtimeGamingCashierAPI2($url, $certpath, $keypath, '');
        $result = $rtgcashier->GetPIDFromLogin($username);
        
        if(!empty($result)){
            if(!empty($result['GetPIDFromLoginResult']) && $result['GetPIDFromLoginResult'] !== null){
                
                $pid = $result['GetPIDFromLoginResult'];
                
                $result2 = $rtgcashier->GetAccountBalance(1, $pid);
                
                if(!empty($result2['GetAccountBalanceResult']) && $result2['GetAccountBalanceResult'] !== null){
                    $result2 = $result2['GetAccountBalanceResult'];
                }
                else{
                    $result2 = 'Cant connect to casino';
                }
            }
            else{
                $result2 = 'Cant connect to casino';
            }
        }
        else{
            $result2 = 'Cant connect to casino';
        }
        return $result2;
    }
    
   
    public function GetPID($username){
        Yii::import('application.components.CasinoAPI.RealtimeGamingCashierAPI2');
        
        $url = Yii::app()->params->abbottcashierapi;
        $certpath = Yii::app()->params->rtg_certkey_dir.'19/cert.pem';
        $keypath = Yii::app()->params->rtg_certkey_dir.'19/key.pem';
        
        $rtgcashier = new RealtimeGamingCashierAPI2($url, $certpath, $keypath, '');
        
        $result = $rtgcashier->GetPIDFromLogin($username);
        
        if(!empty($result)){
            if(!empty($result['GetPIDFromLoginResult']) && $result['GetPIDFromLoginResult'] !== null){                
                $result2 = $result['GetPIDFromLoginResult'];   
            }
            else{
                $result2 = '';
            }
        }
        else{
            $result2 = '';
        }
        return $result2;
    }
    
    
    public function GetToken($pid){
        Yii::import('application.components.CasinoAPI.RealtimeGamingCashierAPI2');
        
        $url = Yii::app()->params->abbottcashierapi;
        $certpath = Yii::app()->params->rtg_certkey_dir.'19/cert.pem';
        $keypath = Yii::app()->params->rtg_certkey_dir.'19/key.pem';
        
        $rtgcashier = new RealtimeGamingCashierAPI2($url, $certpath, $keypath, '');
        
        $result = $rtgcashier->CreateToken($pid);
        
        if(!empty($result)){
            if(!empty($result['CreateTokenResult']) && $result['CreateTokenResult'] !== null){                
                $result2 = $result['CreateTokenResult'];   
            }
            else{
                $result2 = '';
            }
        }
        else{
            $result2 = '';
        }
        return $result2;
    }
    
    
    public function Withdraw($username, $password, $casinoID, $amount, $tracking1, $tracking2, $tracking3, $tracking4){
        Yii::import('application.components.CasinoAPI.RealtimeGamingCashierAPI2');
        
        $url = Yii::app()->params->abbottcashierapi;
        $certpath = Yii::app()->params->rtg_certkey_dir.'19/cert.pem';
        $keypath = Yii::app()->params->rtg_certkey_dir.'19/key.pem';
        
        $rtgcashier = new RealtimeGamingCashierAPI2($url, $certpath, $keypath, '');
        
        $PID = $this->GetPID($username);
        
        if($PID != NULL){
            $hashedPassword = sha1($password);
        
            $login = $rtgcashier->Login($casinoID, $PID, $hashedPassword, 1, $_SERVER[ 'HTTP_HOST' ]);

            if(!empty($login)){
                $sessionID = $login['LoginResult'];
                $methodID = Yii::app()->params->withdrawmethodid;
                $response = $rtgcashier->WithdrawGeneric($casinoID, $PID, $methodID, $amount, $tracking1, $tracking2, $tracking3, $tracking4, $sessionID);

                if ( !$rtgcashier->GetError() )
                {
                    if ( is_array( $response ) )
                    {
                        return array( 'IsSucceed' => true, 'ErrorCode' => 0, 'ErrorMessage' => null, 'TransactionInfo' => $response );
                    }
                    else
                    {
                        return array( 'IsSucceed' => false, 'ErrorCode' => 60, 'ErrorMessage' => 'Response malformed' );
                    }
                }
                else
                {
                    return array( 'IsSucceed' => false, 'ErrorCode' => 61, 'ErrorMessage' => 'API Error: ' . $rtgcashier->GetError() );
                }
            }
            else{
                return 'Cant connect to RTG'; 
            }
        }
        else{
            return 'Cant connect to RTG'; 
        }
    }
    
    
    public function Deposit($username, $password, $casinoID, $amount, $tracking1, $tracking2, $tracking3, $tracking4){
        Yii::import('application.components.CasinoAPI.RealtimeGamingCashierAPI2');
        
        $url = Yii::app()->params->abbottcashierapi;
        $certpath = Yii::app()->params->rtg_certkey_dir.'19/cert.pem';
        $keypath = Yii::app()->params->rtg_certkey_dir.'19/key.pem';
        
        $rtgcashier = new RealtimeGamingCashierAPI2($url, $certpath, $keypath, '');
        
        $PID = $this->GetPID($username);
        
        if($PID != NULL){
            $hashedPassword = sha1($password);
        
            $login = $rtgcashier->Login($casinoID, $PID, $hashedPassword, 1, $_SERVER[ 'HTTP_HOST' ]);

            if(!empty($login)){
                $sessionID = $login['LoginResult'];
                $methodID = Yii::app()->params->depositmethodid;
                $response = $rtgcashier->DepositGeneric($casinoID, $PID, $methodID, $amount, $tracking1, $tracking2, $tracking3, $tracking4, $sessionID);

                if ( !$rtgcashier->GetError() )
                {
                    if ( is_array( $response ) )
                    {
                        return array( 'IsSucceed' => true, 'ErrorCode' => 0, 'ErrorMessage' => null, 'TransactionInfo' => $response );
                    }
                    else
                    {
                        return array( 'IsSucceed' => false, 'ErrorCode' => 50, 'ErrorMessage' => 'Response malformed' );
                    }
                }
                else
                {
                    return array( 'IsSucceed' => false, 'ErrorCode' => 51, 'ErrorMessage' => 'API Error: ' . $rtgcashier->GetError() );
                }
            }
            else{
               return 'Cant connect to RTG';
            }
        }
        else{
            return 'Cant connect to RTG';
        }
        
       
    }
    
    
    public function TransactionSerachInfo($username, $tracking1, $tracking2, $tracking3, $tracking4){
        Yii::import('application.components.CasinoAPI.RealtimeGamingCashierAPI2');
        
        $url = Yii::app()->params->abbottcashierapi;
        $certpath = Yii::app()->params->rtg_certkey_dir.'19/cert.pem';
        $keypath = Yii::app()->params->rtg_certkey_dir.'19/key.pem';
        
        $rtgcashier = new RealtimeGamingCashierAPI2($url, $certpath, $keypath, '');
        
        $PID = $this->GetPID($username);
        
        if($PID != NULL){
            $TrackingInfoTransactionSearchResult = $rtgcashier->TrackingInfoTransactionSearch($PID, $tracking1, $tracking2, $tracking3, $tracking4);

            if ( !$this->_API->GetError() )
            {
                if ( is_array( $TrackingInfoTransactionSearchResult ) )
                {
                    return array( 'IsSucceed' => true, 'ErrorCode' => 0, 'ErrorMessage' => null, 'TransactionInfo' => $TrackingInfoTransactionSearchResult );
                }
                else
                {
                    return array( 'IsSucceed' => false, 'ErrorCode' => 40, 'ErrorMessage' => 'Response malformed' );
                }
            }
            else
            {
                return array( 'IsSucceed' => false, 'ErrorCode' => 41, 'ErrorMessage' => 'API Error: ' . $this->_API->GetError() );
            }
        }
        else{
            return 'Cant connect to RTG';
        }
   
    }
    
    
    
    public function AddToCurrentBalance($username, $amount){
        Yii::import('application.components.CasinoAPI.RealtimeGamingCashierAPI2');
        
        $url = Yii::app()->params->abbottcashierapi;
        $certpath = Yii::app()->params->rtg_certkey_dir.'19/cert.pem';
        $keypath = Yii::app()->params->rtg_certkey_dir.'19/key.pem';
        $rtgcashier = new RealtimeGamingCashierAPI2($url, $certpath, $keypath, '');
        $result = $rtgcashier->GetPIDFromLogin($username);
        
        if(!empty($result)){
            if(!empty($result['GetPIDFromLoginResult']) && $result['GetPIDFromLoginResult'] !== null){
                
                $pid = $result['GetPIDFromLoginResult'];
                
                $result2 = $rtgcashier->AdjustComps(1, $pid, $amount,0);

                if(!empty($result2['AdjustCompsResult']) && $result2['AdjustCompsResult'] !== null){
                    $result2 = $result2['AdjustCompsResult'];
                }
                else{
                    $result2 = '';
                }
            }
            else{
                $result2 = '';
            }
        }
        else{
            $result2 = '';
        }
        
        return $result2;
    }
    
    
    public function DeductToCurrentBalance($username, $amount){
        Yii::import('application.components.CasinoAPI.RealtimeGamingCashierAPI2');

        $url = Yii::app()->params->abbottcashierapi;
        $certpath = Yii::app()->params->rtg_certkey_dir.'19/cert.pem';
        $keypath = Yii::app()->params->rtg_certkey_dir.'19/key.pem';
        $rtgcashier = new RealtimeGamingCashierAPI2($url, $certpath, $keypath, '');
        $result = $rtgcashier->GetPIDFromLogin($username);
        
        if(!empty($result)){
            if(!empty($result['GetPIDFromLoginResult']) && $result['GetPIDFromLoginResult'] !== null){
                
                $pid = $result['GetPIDFromLoginResult'];
                
                $result2 = $rtgcashier->AdjustComps(1, $pid, -$amount,1);

                if(!empty($result2['AdjustCompsResult']) && $result2['AdjustCompsResult'] !== null){
                    $result2 = $result2['AdjustCompsResult'];
                }
                else{
                    $result2 = '';
                }
            }
            else{
                $result2 = '';
            }
        }
        else{
            $result2 = '';
        }
        
        return $result2;
    }
    
    
    public function logout($username){
        Yii::import('application.components.CasinoAPI.RealtimeGamingPlayerAPI');
        Yii::import('application.components.CasinoAPI.RealtimeGamingCashierAPI2');

        $url = Yii::app()->params->abbottcashierapi;
        $certpath = Yii::app()->params->rtg_certkey_dir.'19/cert.pem';
        $keypath = Yii::app()->params->rtg_certkey_dir.'19/key.pem';
        $rtgplayer = new RealtimeGamingPlayerAPI($url, $certpath, $keypath, '');
        $rtgcashier = new RealtimeGamingCashierAPI2($url, $certpath, $keypath, '');
        
        $PID = $rtgcashier->GetPIDFromLogin($username);
        
        if($PID != NULL){
            $result = $rtgplayer->logoutPlayer($PID);
        
            if(!empty($result)){
                if(!empty($result['LogoutPlayerResult']) && $result['LogoutPlayerResult'] !== null){

                    $result2 = $result['LogoutPlayerResult'];
                }
                else{
                    $result2 = '';
                }
            }
            else{
                $result2 = '';
            }
        }
        else{
            return 'Cant connect to RTG';
        }
        
        return $result2;
    }
}
?>
