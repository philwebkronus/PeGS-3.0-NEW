<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of PlayTechAPIWrapper
 *
 * @author elperez
 */

require_once( ROOT_DIR . 'sys/class/CasinoAPI/PlayTechAPI.class.php' );

class PlayTechAPIWrapper {
    
    private $_API;
    
    public function __construct($URI, $casinoName, $secretKey){
        $this->_API = new PlayTechAPI($URI, $casinoName, $secretKey);
    }
    
    public function GetAPIInstance(){
        return $this->_API;
    }
    
        public function NewPlayer($userName,
            $password,
            $email,
            $firstName,
            $lastName,
            $birthDate,
            $address,
            $city,
            $countryCode,
            $phone,
            $zip,
            $currency = 'PHP',
            $viplevel = 1,
            $skin = 'philweb')
    {
        $this->_API->SetVIPLevel($userName, $password, $viplevel);
       
        $response = $this->_API->NewPlayer(
                $userName,
                $password,
                $email,
                $firstName,
                $lastName,
                $birthDate,
                $address,
                $city,
                $countryCode,
                $phone,
                $zip,
                $currency,
                $viplevel,
                $skin);
        
        if(!$this->_API->GetError())
        {
            if(is_array($response))
            {
                if(!isset($response['error']))
                {
                    return array( 'IsSucceed' => true,
                                        'ErrorCode' => 0, 
                                        'ErrorMessage' => null,  
                                        'AccountInfo' => $response);
                } else {
                    return array('IsSucceed' => false, 'ErrorCode' => 1, 'ErrorMessage' => "(".$response['error'].")" );
                }
            } else {
                return array('IsSucceed' => false, 'ErrorCode' => 2, 'ErrorMessage' => 'Response malformed');
            }
        } else {
            return array('IsSucceed' => false, 'ErrorCode' => 3, 'ErrorMessage' => 'API Error: '.$this->_API->GetError());
        }
    }
    
    public function GetBalance($loginName){
        $response = $this->_API->GetBalance( $loginName );

        if ( !$this->_API->GetError() )
        {   
            if ( is_array( $response ) )
            {
                if ( !isset($response['error']) )
                {
                    return array( 'IsSucceed' => true, 'ErrorCode' => 0, 'ErrorMessage' => null, 
                                  'BalanceInfo' => array( 'Balance' => $response["balance"], 
                                                          'CurrentBet'=>$response['currentbet'],
                                                          'BonusBalance'=>$response['bonusbalance']) );
                }
                else
                {
                    return array( 'IsSucceed' => false, 'ErrorCode' => 1, 'ErrorMessage' => "(" . $response[ "transaction" ][ "@attributes" ][ "result" ] . ") " . $response[ "error" ] );
                }
            }
            else
            {
                return array( 'IsSucceed' => false, 'ErrorCode' => 2, 'ErrorMessage' => 'Response malformed' );
            }
        }
        else
        {
            return array( 'IsSucceed' => false, 'ErrorCode' => 3, 'ErrorMessage' => 'API Error: ' . $this->_API->GetError() );
        }
    }
    
    public function Deposit($loginName, $password, $amount, $externalTranId){
        
        $response = $this->_API->ExternalDeposit($loginName, $password, $amount, $externalTranId);
        $status  = 'false';
        if ( !$this->_API->GetError() )
        {   
            if ( is_array( $response ) )
            {
                if ( !isset($response['error']) )
                {
                        if(isset($response['status']))
                            $status = $response['status'];
                        
                        return array( 'IsSucceed' => true, 'ErrorCode' => 0, 'ErrorMessage' => null, 
                                      'TransactionInfo' => array(
                                      "PT"=>array("TransactionStatus"=>$status,
                                                  "TransactionId"=>$response['tranid'])));
                }
                else
                {
                    switch ($response[ "error" ]){
                        case 1 :
                            $errorMsg = "Incorrect password or username.";
                            return array( 'IsSucceed' => false, 'ErrorCode' => $response[ "error" ], 
                                          'ErrorMessage' => $errorMsg,
                                          'TransactionInfo'=> array(
                                              'PT'=>array('TransactionStatus'=>$response['status'],
                                                          'TransactionId'=>null)));
                            break;
                        case 16 :
                            $errorMsg = "Account has been frozen.";
                            return array( 'IsSucceed' => false, 'ErrorCode' => $response[ "error" ], 
                                          'ErrorMessage' => $errorMsg,
                                          'TransactionInfo'=> array(
                                              'PT'=>array('TransactionStatus'=>$response['status'],
                                                          'TransactionId'=>$response['tranid'])));
                            break;
                        default :
                            $errorMsg = $response[ "status" ];
                            return array( 'IsSucceed' => false, 'ErrorCode' => $response[ "error" ], 
                                          'ErrorMessage' => $errorMsg,
                                          'TransactionInfo'=> array(
                                              'PT'=>array('TransactionStatus'=>$response['status'],
                                                          'TransactionId'=>null)));
                            break;
                    }
                    
                }
            }
            else
            {
                return array( 'IsSucceed' => false, 'ErrorCode' => 2, 'ErrorMessage' => 'Response malformed' );
            }
        }
        else
        {
            return array( 'IsSucceed' => false, 'ErrorCode' => 3, 'ErrorMessage' => 'API Error: ' . $this->_API->GetError() );
        }
    }
    
    public function CheckTransaction($externalTranId){
        
        $response = $this->_API->CheckTransaction($externalTranId);
        
        if ( !$this->_API->GetError() )
        {   
            if ( is_array( $response ) )
            {
                return array( 'IsSucceed' => true, 'ErrorCode' => 0, 'ErrorMessage' => null, 
                              'TransactionInfo' => array("PT"=>$response));
            }
            else
            {
                return array( 'IsSucceed' => false, 'ErrorCode' => 2, 'ErrorMessage' => 'Response malformed' );
            }
        }
        else
        {
            return array( 'IsSucceed' => false, 'ErrorCode' => 3, 'ErrorMessage' => 'API Error: ' . $this->_API->GetError() );
        }
    }
    
    public function Withdraw($loginName, $password, $amount, $externalTranId){
        
        $response = $this->_API->ExternalWithdraw($loginName, $password, $amount, $externalTranId);

        if ( !$this->_API->GetError() )
        {   
            if ( is_array( $response ) )
            {
                if ( !isset($response['error']) )
                {
                    return array( 'IsSucceed' => true, 'ErrorCode' => 0, 'ErrorMessage' => null, 
                                  'TransactionInfo' => array("PT"=>$response));
                }
                else
                {
                    return array( 'IsSucceed' => false, 'ErrorCode' => $response[ "error" ], 'ErrorMessage' => "(" . $response[ "error" ] . ") " . $response[ "status" ] );
                }
            }
            else
            {
                return array( 'IsSucceed' => false, 'ErrorCode' => 2, 'ErrorMessage' => 'Response malformed' );
            }
        }
        else
        {
            return array( 'IsSucceed' => false, 'ErrorCode' => 3, 'ErrorMessage' => 'API Error: ' . $this->_API->GetError() );
        }
    }
    
    public function FreezePlayer($loginName, $frozen){
        
        $response = $this->_API->FreezePlayer($loginName, $frozen);
        
        if ( !$this->_API->GetError() )
        {   
            if ( is_array( $response ) )
            {
                if(isset($response['transaction']['@attributes']['result']) == 'OK')
                    return array('IsSucceed' =>true, 'ErrorCode' => 0, 'ErrorMessage' => null);
                
                else 
                    return array('IsSucceed' =>false,'ErrorCode' =>4, 
                                 'ErrorMessage' => $response['error']);
            }
            else
            {
                return array( 'IsSucceed' => false, 'ErrorCode' => 2, 'ErrorMessage' => 'Response malformed' );
            }
        }
        else
        {
            return array( 'IsSucceed' => false, 'ErrorCode' => 3, 'ErrorMessage' => 'API Error: ' . $this->_API->GetError() );
        }
    }
    
    public function KickPlayer($loginName){
        
        $response = $this->_API->KickPlayer($loginName);
        
        if ( !$this->_API->GetError() )
        {   
            if ( is_array( $response ) )
            {
                if(isset($response['transaction']['@attributes']['result']))
                    return array('IsSucceed' =>true, 'ErrorCode' => 0, 'ErrorMessage' => null);
                
                else 
                    return array('IsSucceed' =>false,'ErrorCode' =>4, 
                                 'ErrorMessage' => $response['error']);
            }
            else
            {
                return array( 'IsSucceed' => false, 'ErrorCode' => 2, 'ErrorMessage' => 'Response malformed' );
            }
        }
        else
        {
            return array( 'IsSucceed' => false, 'ErrorCode' => 3, 'ErrorMessage' => 'API Error: ' . $this->_API->GetError() );
        }
    }
    
    public function GetPlayerInfo($username,$password)
    {
        $response = $this->_API->GetPlayerInfo($username, $password);
        
        if(!$this->_API->GetError()){
            
            if(is_array($response)){
                
                if(isset($response['@attributes']))
                    return array('IsSucceed'=>true,'ErrorCode'=>0,'ErrorMessage'=>null,'PlayerInfo'=>$response);
            } else {
                
                return array('IsSucceed'=>false,'ErrorCode'=>$response['error'],'ErrorMessage'=>$response);
            }
            
        } else {
            
            return array( 'IsSucceed' => false, 'ErrorCode' => 3, 'ErrorMessage' => 'API Error: ' . $this->_API->GetError() );
        }
    }
    
    public function ChangePassword($username, $password)
    {
        $response = $this->_API->ChangePassword($username, $password);
        
        if(!$this->_API->GetError()){
            
            if(is_array($response)){
                
                if(isset($response['transaction']['@attributes']['result']))
                    return array('IsSucceed'=>true,'ErrorCode'=>0,'ErrorMessage'=>null,'PlayerInfo'=>$response);
                else
                    return array('IsSucceed'=>false,'ErrorCode'=>$response['error'],'ErrorMessage'=>"(".$response['error'].")");
            } else {
                return array('IsSucceed'=>false,'ErrorCode'=>$response['error'],'ErrorMessage'=>'Response Malformed.');
            }
        } else {
            return array( 'IsSucceed' => false, 'ErrorCode' => 3, 'ErrorMessage' => 'API Error: ' . $this->_API->GetError() );
        }
    }
}

?>
