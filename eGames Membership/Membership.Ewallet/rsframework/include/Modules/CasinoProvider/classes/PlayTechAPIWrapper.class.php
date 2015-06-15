<?php
require_once('PlayTechAPI.class.php' );
require_once('PlayTechRevertBrokenGamesAPI.class.php' );


class PlayTechAPIWrapper
{
    private $_API;
    
    public function __construct($URI, $casino='', $secretKey='', $certFilePath='', $keyFilePath='', $isRevert = 0) {
        
        if($isRevert == 0){
            $this->_API = new PlayTechAPI($URI, $casino, $secretKey);
        } else {
            $this->_API = new PlayTechRevertBrokenGamesAPI($URI, $certFilePath, $keyFilePath);
        }
    }
    
    public function GetAPIInstance()
    {
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
            $viplevel = 2,
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
                $viplevel,
                $currency,
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
                    return array('IsSucceed' => false, 'ErrorCode' => 1, 'ErrorMessage' => $response['error']);
                }
            } else {
                return array('IsSucceed' => false, 'ErrorCode' => 2, 'ErrorMessage' => 'Response malformed');
            }
        } else {
            return array('IsSucceed' => false, 'ErrorCode' => 3, 'ErrorMessage' => 'API Error: '.$this->_API->GetError());
        }
    }
    
    public function KickPlayer($username)
    {
        $response = $this->_API->KickPlayer($username);
        if(!$this->_API->GetError())
        {
            if(is_array($response))
            {
                if(!isset($response['error']))
                {
                    return array('IsSucceed' => true, 'ErrorCode' => 0, 'ErrorMessage' => null, $response);
                } else {
                    return array('IsSucceed' => false, 'ErrorCode' => 1, 'ErrorMessage' => $response['status']);
                }
            } else {
                return array('IsSucceed' => false, 'ErrorCode' => 2, 'ErrorMessage' => 'Response malformed');
            }
        } else {
            return array('IsSucceed' => false, 'ErrorCode' => 3, 'ErrorMessage' => 'API Error: '.$this->_API->GetError());
        }
    }
    
    public function GetPlayerInfo($username, $password)
    {
        $response = $this->_API->GetPlayerInfo($username, $password);
        if(!$this->_API->GetError())
        {
            if(is_array($response))
            {
                    if($response != null)
                    {
                        return array('IsSucceed' => true, 'ErrorCode' => 0, 'ErrorMessage' => null, $response);
                    } else {
                        return array('IsSucceed' => false, 'ErrorCode' => 1, 'ErrorMessage' => $response['status']);
                    }
            } else {
                    return array('IsSucceed' => false, 'ErrorCode' => 2, 'ErrorMessage' => 'Response malformed');
            }
        } else {
            return array('IsSucceed' => false, 'ErrorCode' => 3, 'ErrorMessage' => 'API Error: '.$this->_API->GetError());
        }
    }
    
    public function GetBalance($username)
    {
        $response = $this->_API->GetBalance($username);

        if(!$this->_API->GetError())
        {
            if(is_array($response))
            {                
                if(!isset($response['error']))
                {
                    return array('IsSucceed' => true, 'ErrorCode' => 0, 'ErrorMessage' => null, 'BalanceInfo' => array('Balance' => $response['balance'], 'CurrentBet' => $response['currentbet']));
                } else {
                    return array('IsSucceed' => false, 'ErrorCode' => 1, 'ErrorMessage' => "(".$response["transaction"]["@attributes"]["result"].")".$response["error"]);
                }
            } else {
                return array('IsSucceed' => false, 'ErrorCode' => 2, 'ErrorMessage' => 'Response Malformed');
            }
        } else {
            return array('IsSucceed' => false, 'ErrorCode' => 3, 'ErrorMessage' => 'API Error: '.$this->_API->GetError());
        }
    }
    
    public function FreezePlayer($username, $frozen)
    {
        $response = $this->_API->FreezePlayer($username,$frozen);
        
        if(!$this->_API->GetError())
        {
            if(is_array($response))
            {
                if($response != null)
                {
                    return array('IsSucceed' => true, 'ErrorCode' => 0, 'ErrorMessage' => null, $response);
                } else {
                    return array('IsSucceed' => false, 'ErrorCode' => 1, 'ErrorMessage' => "(".$response['error'].")");
                } 
            } else {
                return array('IsSucceed' => false, 'ErrorCode' => 2, 'ErrorMessage' => 'Response Malformed');
            }
        } else {
            return array('IsSucceed' => false, 'ErrorCode' => 3, 'ErrorMessage' => 'API Error: '.$this->_API->GetError());
        }
    }
    
    public function ExternalWithdraw($username, $password, $amount, $externalTranId)
    {
        $response = $this->_API->ExternalWithdraw($username, $password, $amount, $externalTranId);
        
        if(!$this->_API->GetError())
        {
            if(is_array($response))
            {
                if(!isset($response["error"]))
                {
                    return array('IsSucceed' => true, 'ErrorCode' => 0, 'ErrorMessage' => null, 'WithdrawalInfo' => $response);
                } else {
                    return array('IsSucceed' => false, 'ErrorCode' => 1, 'ErrorMessage' => $response['status']);
                }
            } else {
                return array('IsSucceed' => false, 'ErrorCode' => 2, 'ErrorMessage' => 'Response Malformed');
            }
        } else {
            return array('IsSucceed' => false, 'ErrorCode' => 3, 'ErrorMessage' => 'API Error: '.$this->_API->GetError());
        }
    }
    
    public function ExternalDeposit($username, $password, $amount, $externalTranId)
    {
        $response = $this->_API->ExternalDeposit($username, $password, $amount, $externalTranId);
        
        if(!$this->_API->GetError())
        {
            if(is_array($response))
            {
                if(!isset($response["error"]))
                {
                   return array('IsSucceed' => true, 'ErrorCode' => 0, 'ErrorMessage' => null,
                                                        'TransactionInfo' =>array('PT'=>$response));
                } else {
                    return array('IsSucceed' => false, 'ErrorCode' => $response["error"], 'ErrorMessage' => $response['status']);
                }
            } else {
                return array('IsSucceed' => false, 'ErrorCode' => 2, 'ErrorMessage' => 'Response Malformed');
            }
        } else {
            return array('IsSucceed' => false, 'ErrorCode' => 3, 'ErrorMessage' => 'API Error: '.$this->_API->GetError());
        }
    }
    
    public function CheckTransaction($externalTranId)
    {
        $response = $this->_API->CheckTransaction($externalTranId);

        if(!$this->_API->GetError())
        {
            if(is_array($response))
            {
                if($response != null)
                {
                    return array('IsSucceed' => true, 'ErrorCode' => 0, 'ErrorMessage' => null, 'TransactionID' => array("ID"=>$response['id'],"Status"=>$response['status']));
                } else {
                    return array('IsSucceed' => false, 'ErrorCode' => 1, 'ErrorMessage' => $response['status']);
                }
            } else {
                return array('IsSucceed' => false, 'ErrorCode' => 2, 'ErrorMessage' => 'Response Malformed');
            }
        } else {
            return array('IsSucceed' => false, 'ErrorCode' => 3, 'ErrorMessage' => 'API Error: '.$this->_API->GetError());
        }
    }
    
    public function ChangePassword($username, $newpassword)
    {
        $response = $this->_API->ChangePassword($username, $newpassword);
        
        if(!$this->_API->GetError())
        {
            if(is_array($response))
            {
                if($response != null)
                {
                    return array('IsSucceed' => true, 'ErrorCode' => 0, 'ErrorMessage' => null);
                } else {
                    return array('IsSucceed' => false, 'ErrorCode' => 1, 'ErrorMessage' => $response['status']);
                }
            } else {
                return array('IsSucceed' => false, 'ErrorCode' => 2, 'ErrorMessage' => "Response Malformed");
            }
        } else {
            return array('IsSucceed' => false, 'ErrorCode' => 3, 'ErrorMessage' => "API Error: ".$this->_API->GetError());
        }
    }
    
    public function RevertBrokenGames($playerUsername, $playerMode, $revertMode)
    {
        $response = $this->_API->DoRevertBrokenGames($playerUsername, $playerMode, $revertMode);
        
        if(is_array($response)){
            if($response != NULL){
                return array('IsSucceed' => true, 'ErrorCode' => 0, 'ErrorMessage' => null, 'RevertBrokenGamesReponse' => $response);
            } else {
                    return array('IsSucceed' => false, 'ErrorCode' => 1, 'ErrorMessage' => $response['status']);
                }
        } else {
            return array('IsSucceed' => false, 'ErrorCode' => 2, 'ErrorMessage' => "Response Malformed");
        }
    }
    
    
}
?>
