<?php

require_once( ROOT_DIR . 'MicrogamingAPI.class.php' );

class MicrogamingAPIWrapper
{
    private $_API;
    
    public function __construct( $URI, $loginName , $pinCode, $sessionGUID, $caching = FALSE )
    {
        $this->_API = new MicrogamingAPI( $URI, $loginName, $pinCode, $sessionGUID, $_SERVER[ 'HTTP_HOST' ], $caching = FALSE );
    }

    public function Deposit( $accountNumber, $amount, $currency )
    {
        $response = $this->_API->Deposit( $accountNumber, $amount, $currency );

        if ( !$this->_API->GetError() )
        {
            if ( is_array( $response ) )
            {
                return array( 'IsSucceed' => true, 'ErrorCode' => 0, 'ErrorMessage' => null, 'TransactionInfo' => $response );
            }
            else
            {
                return array( 'IsSucceed' => false, 'ErrorCode' => 70, 'ErrorMessage' => 'Response malformed' );
            }
        }
        else
        {
            return array( 'IsSucceed' => false, 'ErrorCode' => 71, 'ErrorMessage' => 'API Error: ' . $this->_API->GetError() );
        }
    }

    public function GetBalance( $accountNumber )
    {
        $response = $this->_API->GetAccountBalance( $accountNumber );

        if ( !$this->_API->GetError() )
        {
            if ( is_array( $response ) )
            {                
                if ( $response[ 'GetAccountBalanceResult' ][ 'BalanceResult' ][ 'IsSucceed' ] == "true" )
                {
                    return array( 'IsSucceed' => true, 'ErrorCode' => 0, 'ErrorMessage' => null, 'BalanceInfo' => array( 'TransactionAmount' => $response[ 'GetAccountBalanceResult' ][ 'BalanceResult' ][ 'TransactionAmount' ],
                        'TransactionCreditAmount' => $response[ 'GetAccountBalanceResult' ][ 'BalanceResult' ][ 'TransactionCreditAmount' ],
                        'TransactionId' => $response[ 'GetAccountBalanceResult' ][ 'BalanceResult' ][ 'TransactionId' ],
                        'CreditBalance' => $response[ 'GetAccountBalanceResult' ][ 'BalanceResult' ][ 'CreditBalance' ],
                        'Balance' => $response[ 'GetAccountBalanceResult' ][ 'BalanceResult' ][ 'Balance' ]) );
                }
                else
                {
                    return array( 'IsSucceed' => false, 'ErrorCode' => 80, 'ErrorMessage' => $response[ 'GetAccountBalanceResult' ][ 'BalanceResult' ][ 'ErrorMessage' ]  );
                }
            }
            else
            {
                return array( 'IsSucceed' => false, 'ErrorCode' => 81, 'ErrorMessage' => 'Response malformed' );
            }
        }
        else
        {
            return array( 'IsSucceed' => false, 'ErrorCode' => 82, 'ErrorMessage' => 'API Error: ' . $this->_API->GetError() );
        }
    }

    public function Withdraw( $accountNumber, $amount )
    {
        $response = $this->_API->Withdrawal( $accountNumber, $amount );

        if ( !$this->_API->GetError() )
        {
            if ( is_array( $response ) )
            {
                return array( 'IsSucceed' => true, 'ErrorCode' => 0, 'ErrorMessage' => null, 'TransactionInfo' => $response );
            }
            else
            {
                return array( 'IsSucceed' => false, 'ErrorCode' => 90, 'ErrorMessage' => 'Response malformed' );
            }
        }
        else
        {
            return array( 'IsSucceed' => false, 'ErrorCode' => 91, 'ErrorMessage' => 'API Error: ' . $this->_API->GetError() );
        }
    }
    
    public function GetMyBalance()
    {
        return $this->_API->GetMyBalance();
    }
}

?>