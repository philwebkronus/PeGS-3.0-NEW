<?php

require_once( ROOT_DIR . 'sys/class/CasinoAPI/MicrogamingCAPI.class.php' );

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
                    $changePwdResult = $this->_API->ChangePassword($loginName, $oldPassword, $newPassword);
                    if(!$this->_API->GetError() )
                    {
                        if ( is_array( $changePwdResult ) )
                        {       
                            if ( $changePwdResult["Result"]["@attributes"]["Success"] == 1 )
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
                                //ErrorCode to be pass must be 6, same with RTG ErrorCode
                                return array( 'IsSucceed' => false, 'ErrorCode' => 6, 'ErrorMessage' => 'Error on updating password' );
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

        $response = $this->_API->ChangeBalanceEvents( $loginName, $password, $amount, $transactionID, $eventID );

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
                                'TransactionAmount' => $amount,
                                'TransactionId' =>  $eventID,
                                'Balance' => $response[ "Result" ][ "Returnset" ][ "Balance" ][ "@attributes" ][ "Value" ] / 100
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
                // TODO
                return $response;
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

    public function Withdraw( $loginName, $password, $amount, $transactionID, $eventID, $ticketID, $methodname )
    {
        $this->_API->SetTicketID( $ticketID );

        // All balances in the system are stored as cent units - 1/100th of a credit unit.
        $amount = -($amount * 100);
        
        
        $response = $this->_API->$methodname( $loginName, $password, $amount, $transactionID, $eventID );
        
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
                                'TransactionAmount' => $amount,
                                'TransactionId' =>  $transactionID,
                                'Balance' => $response[ "Result" ][ "Returnset" ][ "Balance" ][ "@attributes" ][ "Value" ] / 100
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
    
    public function ResetPassword($loginName, $password){
        $response = $this->_API->GetUserStatus( $loginName );

        if ( !$this->_API->GetError() )
        {
            if ( is_array( $response ) )
            {
                if ( $response["Result"]["@attributes"]["Success"] == 1 )
                {
                    $resetPwdResult = $this->_API->ResetPassword($loginName, $password);
                    if(!$this->_API->GetError() )
                    {
                        if ( is_array( $resetPwdResult ) )
                        {       
                            if ( $resetPwdResult["Result"]["@attributes"]["Success"] == 1 )
                            {
                                return array
                                (
                                    'IsSucceed' => true,
                                    'ErrorCode' => 0,
                                    'ErrorMessage' => 'Success on reset password'
                                );
                            }
                            else
                            {
                                return array( 'IsSucceed' => false, 'ErrorCode' => 1, 'ErrorMessage' => 'Error on updating password' );
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
    
    public function AccountExists($loginName){
        
        $response = $this->_API->AccountExists($loginName);

        if ( !$this->_API->GetError() )
        {
            if ( is_array( $response ) )
            {
                if ( $response["Result"]["@attributes"]["Success"] == 1 )
                {
                    //TODO : For testing of result
                    return array
                    (
                        'IsSucceed' => true,
                        'ErrorCode' => 0,
                        'ErrorMessage' => null,
                        'AccountInfo' => array
                            (
                                'UserExists' => $response[ "Result" ][ "Returnset" ][ "UserExists" ][ "@attributes" ][ "Value" ],
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
    
    /**
     * Unlocks MG Account
     * @param type $loginName
     * @return array 
     */
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
