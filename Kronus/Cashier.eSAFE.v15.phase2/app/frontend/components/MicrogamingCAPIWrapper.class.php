<?php

Mirage::loadComponents('CasinoAPI/MicrogamingCAPI.class');

class MicrogamingCAPIWrapper
{
    private $_API;

    public function __construct( $URI, $authLogin, $authPassword, $layerName = 'capi', $serverID = '' )
    {
        $this->_API = new MicrogamingCAPI( $URI, $authLogin, $authPassword, $layerName, $serverID );
        
       // $this->_API->SetCaching( false );
    }

    public function GetAPIInstance()
    {
        return $this->_API;
    }

    public function AddUser(
            $userType,
            $loginName,
            $password,
            $email,
            $firstName,
            $lastName,
            $workTel,
            $homeTel,
            $fax,
            $addr1,
            $addr2,
            $city,
            $country,
            $provice,
            $zip,
            $IDnumber,
            $currency,
            $occupation,
            $sex,
            $DOB,
            $alias,
            $ticketID = '' )
    {
        
        $this->_API->SetTicketID( $ticketID );

        $response = $this->_API->AddUser(
                $userType,
                $loginName,
                $password,
                $email,
                $firstName,
                $lastName,
                $workTel,
                $homeTel,
                $fax,
                $addr1,
                $addr2,
                $city,
                $country,
                $provice,
                $zip,
                $IDnumber,
                $currency,
                $occupation,
                $sex,
                $DOB,
                $alias );

        if ( !$this->_API->GetError() )
        {
            if ( is_array( $response ) )
            {
                if ( $response["Result"]["@attributes"]["Success"] == 1 )
                {
                    return array
                    (
                        'IsSucceed' => true,
                        'ErrorCode' => 0,
                        'ErrorMessage' => null,
                        'AccountInfo' => array
                            (
                                'UserID' => $response[ "Result" ][ "Returnset" ][ "UserID" ][ "@attributes" ][ "Value" ]
                            )
                    );
                }
                else
                {
                    return array( 'IsSucceed' => false, 'ErrorCode' => 1, 'ErrorMessage' => "(" . $response[ "Result" ][ "Returnset" ][ "ErrorCode" ][ "@attributes" ][ "Value" ] . ") " . $response[ "Result" ][ "Returnset" ][ "Error" ][ "@attributes" ][ "Value" ] );
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

    public function ChangePassword( $loginName, $oldPassword, $newPassword )
    {
        $response = $this->_API->GetUserStatus( $loginName );

        if ( !$this->_API->GetError() )
        {
            if ( is_array( $response ) )
            {
                if ( $response["Result"]["@attributes"]["Success"] == 1 )
                {
                    return array
                    (
                        'IsSucceed' => true,
                        'ErrorCode' => 0,
                        'ErrorMessage' => null
                    );
                }
                else
                {
                    return array( 'IsSucceed' => false, 'ErrorCode' => 1, 'ErrorMessage' => "(" . $response[ "Result" ][ "Returnset" ][ "ErrorCode" ][ "@attributes" ][ "Value" ] . ") " . $response[ "Result" ][ "Returnset" ][ "Error" ][ "@attributes" ][ "Value" ] );
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

    public function Deposit( $loginName, $password, $amount, $transactionID, $eventID, $ticketID )
    {
        $this->_API->SetTicketID( $ticketID );

        // All balances in the system are stored as cent units - 1/100th of a credit unit.
        $amount = $amount * 100;
        $methodname = Mirage::app()->param['mgcapi_trans_method']; //depends on the config file
        $response = $this->_API->$methodname( $loginName, $password, $amount, $transactionID, $eventID ); //for demo player
        if ( !$this->_API->GetError() )
        {
            if ( is_array( $response ) )
            {
                if ( $response["Result"]["@attributes"]["Success"] == 1 )
                {
                    return array
                    (
                        'IsSucceed' => true,
                        'ErrorCode' => 0,
                        'ErrorMessage' => null,
                        'TransactionInfo' => array
                            (
                                'MG'=>array
                                  (
                                    'TransactionAmount' => $amount / 100,
                                    'TransactionId' =>  $transactionID,
                                    'TransactionStatus'=>'true',
                                    'Balance' => $response[ "Result" ][ "Returnset" ][ "Balance" ][ "@attributes" ][ "Value" ] / 100
                                  )
                            )
                    );
                }
                else
                {
                    return array( 'IsSucceed' => false, 'ErrorCode' => 1, 'ErrorMessage' => "(" . $response[ "Result" ][ "Returnset" ][ "ErrorCode" ][ "@attributes" ][ "Value" ] . ") " . $response[ "Result" ][ "Returnset" ][ "Error" ][ "@attributes" ][ "Value" ] );
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

    public function GetBalance( $loginName )
    {
        $response = $this->_API->GetBalance( $loginName );

        if ( !$this->_API->GetError() )
        {
            if ( is_array( $response ) )
            {
                if ( $response["Result"]["@attributes"]["Success"] == 1 )
                {
                    return array( 'IsSucceed' => true, 'ErrorCode' => 0, 'ErrorMessage' => null, 'BalanceInfo' => array( 'Balance' => $response[ "Result" ][ "Returnset" ][ "Balance" ][ "@attributes" ][ "Value" ] / 100 ) );
                }
                else
                {
                    return array( 'IsSucceed' => false, 'ErrorCode' => 1, 'ErrorMessage' => "(" . $response[ "Result" ][ "Returnset" ][ "ErrorCode" ][ "@attributes" ][ "Value" ] . ") " . $response[ "Result" ][ "Returnset" ][ "Error" ][ "@attributes" ][ "Value" ] );
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

    public function GetUserStatus( $loginName )
    {
        $response = $this->_API->GetUserStatus( $loginName );

        if ( !$this->_API->GetError() )
        {
            if ( is_array( $response ) )
            {
                if ( $response["Result"]["@attributes"]["Success"] == 1 )
                {
                    return array
                    (
                        'IsSucceed' => true,
                        'ErrorCode' => 0,
                        'ErrorMessage' => null,
                        'UserStatusInfo' => array
                            (
                                'LoginStatus' => $response[ "Result" ][ "Returnset" ][ "LoginStatus" ][ "@attributes" ][ "Value" ],
                                'LockoutStatus' => $response[ "Result" ][ "Returnset" ][ "LockoutStatus" ][ "@attributes" ][ "Value" ]
                            )
                    );
                }
                else
                {
                    return array( 'IsSucceed' => false, 'ErrorCode' => 1, 'ErrorMessage' => "(" . $response[ "Result" ][ "Returnset" ][ "ErrorCode" ][ "@attributes" ][ "Value" ] . ") " . $response[ "Result" ][ "Returnset" ][ "Error" ][ "@attributes" ][ "Value" ] );
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
    
     public function GetMethodStatus( $ticketID )
    {
        $response = $this->_API->GetMethodStatus( $ticketID );
        
        if ( !$this->_API->GetError() )
        {
            if ( is_array( $response ) )
            {
                if ( $response["Result"]["@attributes"]["Success"] == 1 )
                {
                    if ( $response["Result"]["@attributes"]["Name"] == "AddUser" )
                    {
                        return array
                        (
                            'IsSucceed' => true,
                            'ErrorCode' => 0,
                            'ErrorMessage' => null,
                            'MethodName' => $response["Result"]["@attributes"]["Name"],
                            'AccountInfo' => array
                                (
                                    'UserID' => $response[ "Result" ][ "Returnset" ][ "UserID" ][ "@attributes" ][ "Value" ]
                                )
                        );
                    }
                    elseif ( ( $response["Result"]["@attributes"]["Name"] == "ChangeBalanceEx" ) || ( $response["Result"]["@attributes"]["Name"] == "ChangeBalanceEvents" ) )
                    {
                        return array
                        (
                            'IsSucceed' => true,
                            'ErrorCode' => 0,
                            'ErrorMessage' => null,
                            'MethodName' => $response["Result"]["@attributes"]["Name"],
                            'TransactionInfo' => array
                                (                     
                                    'MG'=> array
                                        (
                                            'TransactionAmount' => null,
                                            'TransactionId' => $ticketID,
                                            'TransactionStatus'=>'true',
                                            'Balance' => abs( $response[ "Result" ][ "Returnset" ]
                                                            [ "Balance" ][ "@attributes" ][ "Value" ] / 100 )
                                        )
                                )
                        );
                    }
                    elseif ( $response["Result"]["@attributes"]["Name"] == "GetBalance" )
                    {
                        return array
                        (
                            'IsSucceed' => true,
                            'ErrorCode' => 0,
                            'ErrorMessage' => null,
                            'MethodName' => $response["Result"]["@attributes"]["Name"],
                            'BalanceInfo' => array( 'Balance' => abs( $response[ "Result" ][ "Returnset" ][ "Balance" ][ "@attributes" ][ "Value" ] / 100 ) )
                        );
                    }
                    else
                    {
                        return array
                        (
                            'IsSucceed' => true,
                            'ErrorCode' => 0,
                            'ErrorMessage' => null,
                            'MethodName' => $response["Result"]["@attributes"]["Name"],
                            'ResponseInfo' => $response
                        );
                    }
                }
                else
                {
                    return array
                    (
                        'IsSucceed' => false,
                        'ErrorCode' => 1,
                        'ErrorMessage' => "(" . $response[ "Result" ][ "Returnset" ][ "ErrorCode" ][ "@attributes" ][ "Value" ] . ") " . $response[ "Result" ][ "Returnset" ][ "Error" ][ "@attributes" ][ "Value" ],
                        'MethodName' => $response["Result"]["@attributes"]["Name"]
                    );
                }
            }
            else
            {
                return array( 'IsSucceed' => false, 'ErrorCode' => 1, 'ErrorMessage' => 'Response malformed' );
            }
        }
        else
        {
            return array( 'IsSucceed' => false, 'ErrorCode' => 2, 'ErrorMessage' => 'API Error: ' . $this->_API->GetError() );
        }
    }

    public function Withdraw( $loginName, $password, $amount, $transactionID, $eventID, $ticketID )
    {
        $this->_API->SetTicketID( $ticketID );

        // All balances in the system are stored as cent units - 1/100th of a credit unit.
        $amount = -($amount * 100);
        $methodname = Mirage::app()->param['mgcapi_trans_method']; //depends on the config file
        $response = $this->_API->$methodname( $loginName, $password, $amount, $transactionID, $eventID ); //for demo player
        if ( !$this->_API->GetError() )
        {
            if ( is_array( $response ) )
            {
                if ( $response["Result"]["@attributes"]["Success"] == 1 )
                {
                    return array
                    (
                        'IsSucceed' => true,
                        'ErrorCode' => 0,
                        'ErrorMessage' => null,
                        'TransactionInfo' => array
                            (
                                'MG'=>array
                                (
                                    'TransactionAmount' => abs($amount / 100),
                                    'TransactionId' =>  $transactionID,
                                    'TransactionStatus'=>'true',
                                    'Balance' => $response[ "Result" ][ "Returnset" ]
                                                          [ "Balance" ][ "@attributes" ]
                                                          [ "Value" ] / 100
                                )
                            )
                    );
                }
                else
                {
                    return array( 'IsSucceed' => false, 'ErrorCode' => 1, 'ErrorMessage' => "(" . $response[ "Result" ][ "Returnset" ][ "ErrorCode" ][ "@attributes" ][ "Value" ] . ") " . $response[ "Result" ][ "Returnset" ][ "Error" ][ "@attributes" ][ "Value" ] );
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
    
    public function UnlockUserAccount($loginName){
        $unlockedResult = $this->_API->UnlockUserAccount($loginName);
        if(!$this->_API->GetError() )
        {
            if ( is_array( $unlockedResult ) )
            {       
                if ( $unlockedResult["Result"]["@attributes"]["Success"] == 1 )
                {
                    return array
                    (
                        'IsSucceed' => true,
                        'ErrorCode' => 0,
                        'ErrorMessage' => null
                    );
}
                else
                {
                    return array( 'IsSucceed' => false, 'ErrorCode' => 1, 'ErrorMessage' => 'Error on unlocking account status' );
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
}
?>
