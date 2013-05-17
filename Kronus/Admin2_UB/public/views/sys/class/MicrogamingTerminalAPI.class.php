<?php

/**
 * Created By: Edson L. Perez
 * Created On: September 12, 2011
 * Details: For creation of terminal accounts to MG
 * 
 */

ini_set('display_errors',true);
ini_set('log_errors',true);

require_once('nusoap/nusoap.php');

class MicrogamingTerminalAPI 
{
    private $_soapClient;
    private $_xmlSoapHeader;
    private $_currency;
    
    public function __construct($configuration)
    {
        $this->_soapClient = new nusoap_client($configuration['URI'], 'wsdl');
        $this->_xmlSoapHeader = '
                <AgentSession xmlns="https://entservices.totalegame.net">
                    <SessionGUID>' . $configuration[ 'sessionGUID' ] . '</SessionGUID>
                    <IPAddress>' . $configuration[ 'IPAddress' ] . '</IPAddress>
                    <ErrorCode>0</ErrorCode>
                    <IsLengthenSession>1</IsLengthenSession>
                </AgentSession>';
        $this->_currency = $configuration['currency'];
        $this->_soapClient->setHeaders($this->_xmlSoapHeader);
    }
    
    public function GetError()
    {
    	return $this->_soapClient->getError();
    }
    
    public function AddStationAccount($terminal, $password)
    {
        $response = $this->_soapClient->call('AddStationAccount', array('isGeneratePassword' => true, 
                                                                        //'password' => $password,
                                                                        'nickName' => $terminal,
                                                                        'currency' => $this->_currency, 
                                                                        'bettingProfileId' => -1));
        return $response;
    }
    
}

?>
