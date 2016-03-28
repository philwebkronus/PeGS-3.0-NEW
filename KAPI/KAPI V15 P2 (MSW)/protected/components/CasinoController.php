<?php
/*  @author: ralph sison
 *  @dateadded: 12-28-2015
 */
class CasinoController
{    
    public function GetBalance($serviceid,$username){
        Yii::import('application.components.CasinoAPI.RealtimeGamingCashierAPI2');
        
        $url = Yii::app()->params->service_api[$serviceid-1];
        $certpath = Yii::app()->params->rtg_cert_dir.$serviceid.'/cert.pem';
        $keypath = Yii::app()->params->rtg_cert_dir.$serviceid.'/key.pem';
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
    
    public function Deposit($serviceid, $username, $password, $amount, $tracking1, $tracking2, $tracking3, $tracking4, $locatorname = ''){
        Yii::import('application.components.CasinoAPI.RealtimeGamingCashierAPI2');
        
        $url = Yii::app()->params->service_api[$serviceid-1];
        $certpath = Yii::app()->params->rtg_cert_dir.$serviceid.'/cert.pem';
        $keypath = Yii::app()->params->rtg_cert_dir.$serviceid.'/key.pem';
        
        $rtgcashier = new RealtimeGamingCashierAPI2($url, $certpath, $keypath, '');
        
        $pid = $this->GetPID($serviceid,$username);
        
        if(!empty($locatorname)){
            $skinID = $this->_GetSkinID($serviceid,$locatorname);
        } else { $skinID = 1; }
        
        if($pid != NULL){
            $hashedPassword = sha1($password);
            
            $casinoID = 1;
            $forMoney = 1;
            
            $login = $rtgcashier->Login($casinoID, $pid, $hashedPassword, $forMoney, $_SERVER[ 'HTTP_HOST' ], $skinID);

            if(!empty($login)){
                $sessionID = $login['LoginResult'];
                $methodID = Yii::app()->params->deposit_method_id;
                $response = $rtgcashier->DepositGeneric($casinoID, $pid, $methodID, $amount, $tracking1, $tracking2, $tracking3, $tracking4, $sessionID, $skinID);
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
    
    public function Withdraw($serviceid,$username, $password, $amount, $tracking1, $tracking2, $tracking3, $tracking4,$locatorname = ''){
        Yii::import('application.components.CasinoAPI.RealtimeGamingCashierAPI2');
        
        $url = Yii::app()->params->service_api[$serviceid-1];
        $certpath = Yii::app()->params->rtg_cert_dir.$serviceid.'/cert.pem';
        $keypath = Yii::app()->params->rtg_cert_dir.$serviceid.'/key.pem';
        
        $rtgcashier = new RealtimeGamingCashierAPI2($url, $certpath, $keypath, '');
        
        $PID = $this->GetPID($serviceid,$username);
        
        if(!empty($locatorname)){
            $skinID = $this->_GetSkinID($serviceid,$locatorname);
        } else { $skinID = 1; }
        
        if($PID != NULL){
            $hashedPassword = sha1($password);
            
            $casinoID = 1;
        
            $login = $rtgcashier->Login($casinoID, $PID, $hashedPassword, 1, $_SERVER[ 'HTTP_HOST' ],$skinID);
            
            if(!empty($login)){
                $sessionID = $login['LoginResult'];
                $methodID = Yii::app()->params->withdrawal_method_id;
                $response = $rtgcashier->WithdrawGeneric($casinoID, $PID, $methodID, $amount, $tracking1, $tracking2, $tracking3, $tracking4, $sessionID,$skinID);

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
    
    private function _GetSkinID( $serviceid, $locatorname )
    {
        Yii::import('application.components.CasinoAPI.RealtimeGamingCashierAPI2');
        
        $url = Yii::app()->params->service_api[$serviceid-1];
        $certpath = Yii::app()->params->rtg_cert_dir.$serviceid.'/cert.pem';
        $keypath = Yii::app()->params->rtg_cert_dir.$serviceid.'/key.pem';
        
        $rtgcashier = new RealtimeGamingCashierAPI2($url, $certpath, $keypath, '');
        
        $result = $rtgcashier->GetSkinID($locatorname);
        
        if(!empty($result)){
            if(!empty($result['GetSkinIDResult']) && $result['GetSkinIDResult'] !== null){                
                $result2 = $result['GetSkinIDResult'];   
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
    
    public function GetPID($serviceid, $username){
        Yii::import('application.components.CasinoAPI.RealtimeGamingCashierAPI2');
        
        $url = Yii::app()->params->service_api[$serviceid-1];
        $certpath = Yii::app()->params->rtg_cert_dir.$serviceid.'/cert.pem';
        $keypath = Yii::app()->params->rtg_cert_dir.$serviceid.'/key.pem';
        
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
    
    public function TransactionSearchInfo($serviceid,$username, $tracking1, $tracking2, $tracking3, $tracking4){
        Yii::import('application.components.CasinoAPI.RealtimeGamingCashierAPI2');
        
        $url = Yii::app()->params->service_api[$serviceid-1];
        $certpath = Yii::app()->params->rtg_cert_dir.$serviceid.'/cert.pem';
        $keypath = Yii::app()->params->rtg_cert_dir.$serviceid.'/key.pem';
        
        $rtgcashier = new RealtimeGamingCashierAPI2($url, $certpath, $keypath, '');
        
        $PID = $this->GetPID($serviceid,$username);
        
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
}
?>
