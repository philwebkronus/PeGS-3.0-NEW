<?php
#Name: MicrogamingCAPI.class.php
#Version: 1.0.0
#Copyright 2012 PhilWeb Corporation

//require_once('../sys/class/Array2XML.php');
//$_MicrogamingCashierAPI = new MicrogamingCAPI( $URI, $authLogin, $authPassword, $layerName = 'capi', $serverID = '' );

class MicrogamingCAPI
{
    /**
     * Holds the web service end point
     * @var string
     */
    private $_URI = 'http://78.24.209.144/CasinoAPIV2/CasinoAPI.aspx';

    /**
     * Set caching of connection
     * @var boolean
     */
    private $_caching = FALSE;

    /**
     * User agent
     * @var string
     */
    private $_userAgent = 'PEGS Station Manager';
    
    /**
     * 
     * @var string
     */
    private $_authLogin = '';

    /**
     *
     * @var string
     */
    private $_authPassword = '';

    /**
     *
     * @var string
     */
    private $_layerName = 'capi';

    /**
     *
     * @var string
     */
    private $_serverID = '';

    /**
     * Maximum number of seconds to wait while trying to connect
     * @var integer
     */
    private $_connectionTimeout = 10;

    /**
     * Maximum number of seconds before a call timeouts
     * @var integer
     */
    private $_timeout = 500;

    /**
     * Error message
     * @var string
     */
    private $_error;

    /**
     * Holds array response
     * @var array
     */
    private $_APIresponse;

    /**
     * Holds the ticket ID for each request
     * @var string
     */
    private $_ticketID;

     /**
     * Class constructor
     *
     * @param string $URI
     * @param string $authLogin
     * @param string $authPassword
     * @param string $layerName
     * @param string $serverID
     * @return void
     */
    public function __construct( $URI, $authLogin, $authPassword, $layerName = 'capi', $serverID = '' )
    {
        if ( $layerName != '' )
            $this->_layerName = $layerName;

        $this->_URI = $URI;
        $this->_authLogin = $authLogin;
        $this->_authPassword = $authPassword;
        $this->_layerName = $layerName;
        $this->_serverID = $serverID;
    }     

    /**
     * Class destructor
     */
    public function __destruct()
    {
        // do nothing
    }

    public function SetCaching( $caching )
    {
        $this->_caching = $caching;
    }

    public function SetUserAgent( $userAgent )
    {
        $this->_userAgent = $userAgent;
    }

    public function SetConnectionTimeout( $timeout )
    {
        $this->_connectionTimeout = $timeout;
    }

    public function SetTimeout( $timeout )
    {
        $this->_timeout = $timeout;
    }

    /** API Web Methods **/

    public function SetTicketID( $ticketID )
    {
        $this->_ticketID = $ticketID;
    }
    
    public function AccountExists( $loginName )
    {
        $xmlData = array( 'Method' => array( 
            '@attributes' => array(
                'Name' => 'AccountExists',
                'LayerName' => $this->_layerName,
                'ServerID' => $this->_serverID ),
            'Auth' => array( '@attributes' => array(
                'Login' => $this->_authLogin,
                'Password' => $this->_authPassword,
                'TicketID' =>  $this->_ticketID,
                'ServerID' => $this->_serverID ) ),
            'Params' => array(
                'LoginName' => array( '@attributes' => array( 'Value' => $loginName ) )
                )
            )
        );
        $msg = $xmlData;
        unset($msg["Method"]['Auth']['@attributes']['Password']);
        $message = $string = preg_replace('/\s+/', ' ', print_r($msg,true));
        logger($message, "Request", "MG");
        return $this->ProcessXMLRequest( $xmlData );
    }

    public function AddPlayerToPlayerGroup( $loginName, $groupName )
    {
        $xmlData = array( 'Method' => array( 
            '@attributes' => array(
                'Name' => 'AddPlayerToPlayerGroup',
                'LayerName' => $this->_layerName,
                'ServerID' => $this->_serverID ),
            'Auth' => array( '@attributes' => array(
                'Login' => $this->_authLogin,
                'Password' => $this->_authPassword,
                'TicketID' =>  $this->_ticketID,
                'ServerID' => $this->_serverID ) ),
            'Params' => array(
                'LoginName' => array( '@attributes' => array( 'Value' => $loginName ) ),
                'PlayerGroupName' => array( '@attributes' => array( 'Value' => $groupName ) )
                )
            )
        );
        
        $msg = $xmlData;
        unset($msg["Method"]['Auth']['@attributes']['Password']);
        $message = $string = preg_replace('/\s+/', ' ', print_r($msg,true));
        logger($message, "Request", "MG");
        return $this->ProcessXMLRequest( $xmlData );
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
            $alias )
    {
        $xmlData = array( 'Method' => array(
            '@attributes' => array(
                'Name' => 'AddUser',
                'LayerName' => $this->_layerName,
                'ServerID' => $this->_serverID ),
            'Auth' => array( '@attributes' => array(
                'Login' => $this->_authLogin,
                'Password' => $this->_authPassword,
                'TicketID' =>  $this->_ticketID,
                'ServerID' => $this->_serverID ) ),
            'Params' => array(
                'UserType' => array( '@attributes' => array( 'Value' => $userType ) ),
                'LoginName' => array( '@attributes' => array( 'Value' => $loginName ) ),
                'Password' => array( '@attributes' => array( 'Value' => $password ) ),
                'Email' => array( '@attributes' => array( 'Value' => $email ) ),
                'FN' => array( '@attributes' => array( 'Value' => $firstName ) ),
                'LN' => array( '@attributes' => array( 'Value' => $lastName ) ),
                'WrkTel' => array( '@attributes' => array( 'Value' => $workTel ) ),
                'HomeTel' => array( '@attributes' => array( 'Value' => $homeTel ) ),
                'Fax' => array( '@attributes' => array( 'Value' => $fax ) ),
                'Addr1' => array( '@attributes' => array( 'Value' => $addr1 ) ),
                'Addr2' => array( '@attributes' => array( 'Value' => $addr2 ) ),
                'City' => array( '@attributes' => array( 'Value' => $city ) ),
                'Country' => array( '@attributes' => array( 'Value' => $country ) ),
                'Province' => array( '@attributes' => array( 'Value' => $provice ) ),
                'Zip' => array( '@attributes' => array( 'Value' => $zip ) ),
                'IDNumber' => array( '@attributes' => array( 'Value' => $IDnumber ) ),
                'Currency' => array( '@attributes' => array( 'Value' => $currency ) ),
                'Occupation' => array( '@attributes' => array( 'Value' => $occupation ) ),
                'Sex' => array( '@attributes' => array( 'Value' => $sex ) ),
                'DOB' => array( '@attributes' => array( 'Value' => $DOB ) ),
                'Alias' => array( '@attributes' => array( 'Value' => $alias ) )
                )
            )
        );

        $msg = $xmlData;
        unset($msg["Method"]['Auth']['@attributes']['Password']);
        $message = $string = preg_replace('/\s+/', ' ', print_r($msg,true));
        logger($message, "Request", "MG");
        return $this->ProcessXMLRequest( $xmlData );
    }

    public function ChangeBalance( $loginName, $password, $amount, $transactionID, $eventID )
    {
        $xmlData = array( 'Method' => array(
            '@attributes' => array(
                'Name' => 'ChangeBalance',
                'LayerName' => $this->_layerName,
                'ServerID' => $this->_serverID ),
            'Auth' => array( '@attributes' => array(
                'Login' => $this->_authLogin,
                'Password' => $this->_authPassword,
                'TicketID' =>  $this->_ticketID,
                'ServerID' => $this->_serverID ) ),
            'Params' => array(
                'LoginName' => array( '@attributes' => array( 'Value' => $loginName ) ),
                'Amount' => array( '@attributes' => array( 'Value' => $amount ) )
                )
            )
        );

        $msg = $xmlData;
        unset($msg["Method"]['Auth']['@attributes']['Password']);
        $message = $string = preg_replace('/\s+/', ' ', print_r($msg,true));
        logger($message, "Request", "MG");
        return $this->ProcessXMLRequest( $xmlData );
    }
    
    public function ChangeBalanceEvents( $loginName, $password, $amount, $transactionID, $eventID )
    {
        $xmlData = array( 'Method' => array(
            '@attributes' => array(
                'Name' => 'ChangeBalanceEvents',
                'LayerName' => $this->_layerName,
                'ServerID' => $this->_serverID ),
            'Auth' => array( '@attributes' => array(
                'Login' => $this->_authLogin,
                'Password' => $this->_authPassword,
                'TicketID' =>  $this->_ticketID,
                'ServerID' => $this->_serverID ) ),
            'Params' => array(
                'LoginName' => array( '@attributes' => array( 'Value' => $loginName ) ),
                'Password' => array( '@attributes' => array( 'Value' => $password ) ),
                'Amount' => array( '@attributes' => array( 'Value' => $amount ) ),
                'TransactionID' => array( '@attributes' => array( 'Value' => $transactionID ) ),
                'EventID' => array( '@attributes' => array( 'Value' => $eventID ) )
                )
            )
        );

        $msg = $xmlData;
        unset($msg["Method"]['Auth']['@attributes']['Password']);
        $message = $string = preg_replace('/\s+/', ' ', print_r($msg,true));
        logger($message, "Request", "MG");
        return $this->ProcessXMLRequest( $xmlData );
    }
    
    public function ChangeBalanceEx( $loginName, $password, $amount, $transactionID, $eventID )
    {
        $xmlData = array( 'Method' => array(
            '@attributes' => array(
                'Name' => 'ChangeBalanceEx',
                'LayerName' => $this->_layerName,
                'ServerID' => $this->_serverID ),
            'Auth' => array( '@attributes' => array(
                'Login' => $this->_authLogin,
                'Password' => $this->_authPassword,
                'TicketID' =>  $this->_ticketID,
                'ServerID' => $this->_serverID ) ),
            'Params' => array(
                'LoginName' => array( '@attributes' => array( 'Value' => $loginName ) ),
                'Password' => array( '@attributes' => array( 'Value' => $password ) ),
                'Amount' => array( '@attributes' => array( 'Value' => $amount ) ),
                'TransactionID' => array( '@attributes' => array( 'Value' => $transactionID ) ),
                'EventID' => array( '@attributes' => array( 'Value' => $eventID ) )
                )
            )
        );
        $msg = $xmlData;
        unset($msg["Method"]['Auth']['@attributes']['Password']);
        $message = $string = preg_replace('/\s+/', ' ', print_r($msg,true));
        logger($message, "Request", "MG");
        return $this->ProcessXMLRequest( $xmlData );
    }

    public function ChangePassword( $loginName, $oldPassword, $newPassword )
    {
        $xmlData = array( 'Method' => array(
            '@attributes' => array(
                'Name' => 'ChangePassword',
                'LayerName' => $this->_layerName,
                'ServerID' => $this->_serverID ),
            'Auth' => array( '@attributes' => array(
                'Login' => $this->_authLogin,
                'Password' => $this->_authPassword,
                'TicketID' =>  $this->_ticketID,
                'ServerID' => $this->_serverID ) ),
            'Params' => array(
                'LoginName' => array( '@attributes' => array( 'Value' => $loginName ) ),
                'OldPassword' => array( '@attributes' => array( 'Value' => $loginName ) ),
                'NewPassword' => array( '@attributes' => array( 'Value' => $loginName ) )
                )
            )
        );

        $msg = $xmlData;
        unset($msg["Method"]['Auth']['@attributes']['Password']);
        $message = $string = preg_replace('/\s+/', ' ', print_r($msg,true));
        logger($message, "Request", "MG");
        return $this->ProcessXMLRequest( $xmlData );
    }

    public function GetBalance( $loginName )
    {
        $xmlData = array( 'Method' => array(
            '@attributes' => array(
                'Name' => 'GetBalance',
                'LayerName' => $this->_layerName,
                'ServerID' => $this->_serverID ),
            'Auth' => array( '@attributes' => array(
                'Login' => $this->_authLogin,
                'Password' => $this->_authPassword,
                'TicketID' =>  $this->_ticketID,
                'ServerID' => $this->_serverID ) ),
            'Params' => array(
                'LoginName' => array( '@attributes' => array( 'Value' => $loginName ) )
                )
            )
        );

        $msg = $xmlData;
        unset($msg["Method"]['Auth']['@attributes']['Password']);
        $message = $string = preg_replace('/\s+/', ' ', print_r($msg,true));
        logger($message, "Request", "MG");
        return $this->ProcessXMLRequest( $xmlData );
    }

    public function GetLoginName( $alias, $networkID )
    {
        $xmlData = array( 'Method' => array(
            '@attributes' => array(
                'Name' => 'GetLoginName',
                'LayerName' => $this->_layerName,
                'ServerID' => $this->_serverID ),
            'Auth' => array( '@attributes' => array(
                'Login' => $this->_authLogin,
                'Password' => $this->_authPassword,
                'TicketID' =>  $this->_ticketID,
                'ServerID' => $this->_serverID ) ),
            'Params' => array(
                'Alias' => array( '@attributes' => array( 'Value' => $alias ) ),
                'NetworkID' => array( '@attributes' => array( 'Value' => $networkID ) )
                )
            )
        );

        $msg = $xmlData;
        unset($msg["Method"]['Auth']['@attributes']['Password']);
        $message = $string = preg_replace('/\s+/', ' ', print_r($msg,true));
        logger($message, "Request", "MG");
        return $this->ProcessXMLRequest( $xmlData );
    }

    public function GetMethodStatus( $ticketID )
    {
        $xmlData = array( 'Method' => array(
            '@attributes' => array(
                'Name' => 'GetMethodStatus',
                'LayerName' => $this->_layerName,
                'ServerID' => $this->_serverID ),
            'Auth' => array( '@attributes' => array(
                'Login' => $this->_authLogin,
                'Password' => $this->_authPassword,
                'TicketID' =>  '', //this must be blank to avoid error
                'ServerID' => $this->_serverID ) ),
            'Params' => array(
                'TicketID' => array( '@attributes' => array( 'Value' => $ticketID ) ) )
            )
        );

        $msg = $xmlData;
        unset($msg["Method"]['Auth']['@attributes']['Password']);
        $message = $string = preg_replace('/\s+/', ' ', print_r($msg,true));
        logger($message, "Request", "MG");
        return $this->ProcessXMLRequest( $xmlData );
    }
    
    public function GetUserStatus( $loginName )
    {                
        $xmlData = array( 'Method' => array( 
            '@attributes' => array(
                'Name' => 'GetUserStatus',
                'LayerName' => $this->_layerName,
                'ServerID' => $this->_serverID ),
            'Auth' => array( '@attributes' => array(
                'Login' => $this->_authLogin,
                'Password' => $this->_authPassword,
                'TicketID' => $this->_ticketID,
                'ServerID' => $this->_serverID ) ),
            'Params' => array(
                'LoginName' => array( '@attributes' => array( 'Value' => $loginName ) )
                )
            )
        );

        $msg = $xmlData;
        unset($msg["Method"]['Auth']['@attributes']['Password']);
        $message = $string = preg_replace('/\s+/', ' ', print_r($msg,true));
        logger($message, "Request", "MG");
        return $this->ProcessXMLRequest( $xmlData );
    }

    public function LockUserAccount( $loginName )
    {
        $xmlData = array( 'Method' => array(
            '@attributes' => array(
                'Name' => 'LockUserAccount',
                'LayerName' => $this->_layerName,
                'ServerID' => $this->_serverID ),
            'Auth' => array( '@attributes' => array(
                'Login' => $this->_authLogin,
                'Password' => $this->_authPassword,
                'TicketID' =>  $this->_ticketID,
                'ServerID' => $this->_serverID ) ),
            'Params' => array(
                'LoginName' => array( '@attributes' => array( 'Value' => $loginName ) )
                )
            )
        );

        $msg = $xmlData;
        unset($msg["Method"]['Auth']['@attributes']['Password']);
        $message = $string = preg_replace('/\s+/', ' ', print_r($msg,true));
        logger($message, "Request", "MG");
        return $this->ProcessXMLRequest( $xmlData );
    }

    public function RemovePlayerFromPlayerGroup( $loginName, $groupName )
    {
        $xmlData = array( 'Method' => array(
            '@attributes' => array(
                'Name' => 'RemovePlayerFromPlayerGroup',
                'LayerName' => $this->_layerName,
                'ServerID' => $this->_serverID ),
            'Auth' => array( '@attributes' => array(
                'Login' => $this->_authLogin,
                'Password' => $this->_authPassword,
                'TicketID' =>  $this->_ticketID,
                'ServerID' => $this->_serverID ) ),
            'Params' => array(
                'LoginName' => array( '@attributes' => array( 'Value' => $loginName ) ),
                'PlayerGroupName' => array( '@attributes' => array( 'Value' => $groupName ) )
                )
            )
        );

        $msg = $xmlData;
        unset($msg["Method"]['Auth']['@attributes']['Password']);
        $message = $string = preg_replace('/\s+/', ' ', print_r($msg,true));
        logger($message, "Request", "MG");
        return $this->ProcessXMLRequest( $xmlData );
    }

    public function UnlockUserAccount( $loginName )
    {
        $xmlData = array( 'Method' => array( 
            '@attributes' => array(
                'Name' => 'UnlockUserAccount',
                'LayerName' => $this->_layerName,
                'ServerID' => $this->_serverID ),
            'Auth' => array( '@attributes' => array(
                'Login' => $this->_authLogin,
                'Password' => $this->_authPassword,
                'TicketID' =>  $this->_ticketID,
                'ServerID' => $this->_serverID ) ),
            'Params' => array(
                'LoginName' => array( '@attributes' => array( 'Value' => $loginName ) )
                )
            )
        );

        $msg = $xmlData;
        unset($msg["Method"]['Auth']['@attributes']['Password']);
        $message = $string = preg_replace('/\s+/', ' ', print_r($msg,true));
        logger($message, "Request", "MG");
        return $this->ProcessXMLRequest( $xmlData );
    }    

    public function ValidateUser( $loginName, $password )
    {
        $xmlData = array( 'Method' => array( 
            '@attributes' => array(
                'Name' => 'ValidateUser',
                'LayerName' => $this->_layerName,
                'ServerID' => $this->_serverID ),
            'Auth' => array( '@attributes' => array(
                'Login' => $this->_authLogin,
                'Password' => $this->_authPassword,
                'TicketID' =>  $this->_ticketID,
                'ServerID' => $this->_serverID ) ),
            'Params' => array(
                'LoginName' => array( '@attributes' => array( 'Value' => $loginName ) ),
                'Password' => array( '@attributes' => array( 'Value' => $password ) )
                )
            )
        );

        $msg = $xmlData;
        unset($msg["Method"]['Auth']['@attributes']['Password']);
        $message = $string = preg_replace('/\s+/', ' ', print_r($msg,true));
        logger($message, "Request", "MG");
        return $this->ProcessXMLRequest( $xmlData );
    }    

    /**
     *
     * @return string
     */
    public function GetError()
    {
        return $this->_error;
    }

    /**
     *
     * @return xml
     */
    public function GetXMLRequest()
    {
        return $this->_xmlRequest;
    }

    /**
     *
     * @return xml
     */
    public function GetXMLResponse()
    {
        return $this->_xmlResponse;
    }

    public function ProcessXMLRequest( $xmlData )
    {
        $object = null;
        
        $xml = Array2XML::createXML( 'PKT', $xmlData );

        $this->_xmlRequest = $xml->saveXML();

        $response = $this->PostXMLData( $this->_xmlRequest, $this->_URI );

        if ( $response[0] == 200 )
        {
            $this->_xmlResponse = $response[1];

            if ( $this->_xmlResponse != null )
            {
                // XML to array
                $object = $this->XML2Array( $this->_xmlResponse );
            }
        }
        else
        {
            $this->_error = "HTTP ". $response[0];
        }
        $message = $string = preg_replace('/\s+/', ' ', print_r($object,true));
        logger($message, "Response", "MG");
        return $object;
    }
    
    private function XML2Array( $xmlString )
    {
        $xml = simplexml_load_string( $xmlString );

        $json = json_encode( $xml );

        return json_decode( $json, TRUE );
    }
    /**
     * Post XML data to remote server
     *
     * @param xml $xmlData
     * @param string $URI
     * @return data
     */
    private function PostXMLData( $xmlData, $URI )
    {
        $curl = curl_init( $URI );

        curl_setopt( $curl, CURLOPT_FRESH_CONNECT, $this->_caching );
        curl_setopt( $curl, CURLOPT_CONNECTTIMEOUT, $this->_connectionTimeout );
        curl_setopt( $curl, CURLOPT_TIMEOUT, $this->_timeout );
        curl_setopt( $curl, CURLOPT_USERAGENT, $this->_userAgent );
        curl_setopt( $curl, CURLOPT_SSL_VERIFYHOST, FALSE );
        curl_setopt( $curl, CURLOPT_SSL_VERIFYPEER, FALSE );
        curl_setopt( $curl, CURLOPT_POST, TRUE );
        curl_setopt( $curl, CURLOPT_HTTPHEADER, array( 'Content-Type: text/xml; charset=utf-8' ) );
        curl_setopt( $curl, CURLOPT_POSTFIELDS, $xmlData );
        curl_setopt( $curl, CURLOPT_RETURNTRANSFER, TRUE );
        curl_setopt( $curl, CURLOPT_SSLVERSION, 3 );

        $response = curl_exec( $curl );

        $http_status = curl_getinfo( $curl, CURLINFO_HTTP_CODE );

        curl_close( $curl );

        return array( $http_status, $response );
    }
}
?>