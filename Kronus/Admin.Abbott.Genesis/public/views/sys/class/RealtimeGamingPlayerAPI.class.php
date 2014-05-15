<?php
#Name: RealtimeGamingPlayerAPI.class.php
#Author: J.O. Pormento
#Version: 1.0.0
#Copyright 2010 PhilWeb Corporation

require_once('nusoap/nusoap.php');

class RealtimeGamingPlayerAPI
{
    private $_soapClient;
    
    public function __construct($configuration)
    {
        $this->_soapClient = new nusoap_client($configuration['URI'], 'wsdl');
        $this->_soapClient->authtype = 'certificate';
        $this->_soapClient->certRequest['sslcertfile'] = $configuration['certFilePath'];
        $this->_soapClient->certRequest['sslkeyfile'] = $configuration['keyFilePath'];
        $this->_soapClient->certRequest['passphrase'] = '';
        $this->_soapClient->certRequest['verifypeer'] = 0;
        $this->_soapClient->certRequest['verifyhost'] = 0;
    }
    
    public function GetError()
    {
    	return $this->_soapClient->getError();
    }     
	
    public function createNewPlayerFull($login, $password, $aid, $country, $casinoID, $fname, $lname, $email, $dayphone, $evephone, $addr1, $addr2, $city, $state, $zip, $ip, $mac, $userID, $downloadID, $birthdate, $clientID, $putInAffPID, $calledFromCasino, $hashedPassword, $agentID, $currentPosition, $thirdPartyPID)
    {
    	$result = $this->_soapClient->call('createNewPlayerFull', array('login' => $login,
                                                                        'pw' => $password,
                                                                        'aid' => $aid,
                                                                        'country' => $country,
                                                                        'casinoID' => $casinoID,
                                                                        'fname' => $fname,
                                                                        'lname' => $lname,
                                                                        'email' => $email,
                                                                        'dayphone' => $dayphone,
                                                                        'evephone' => $evephone,
                                                                        'addr1' => $addr1,
                                                                        'addr2' => $addr2,
                                                                        'city' => $city,
                                                                        'state' => $state,
                                                                        'zip' => $zip,
                                                                        'ip' => $ip,
                                                                        'mac' => $mac,
                                                                        'userID' => $userID,
                                                                        'downloadID' => $downloadID,
                                                                        'birthdate' => $birthdate,
                                                                        'clientID' => $clientID,
                                                                        'putInAffPID' => $putInAffPID,
                                                                        'calledFromCasino' => $calledFromCasino,
                                                                        'hashedPassword' => $hashedPassword,
                                                                        'agentID' => $agentID,
                                                                        'currentPosition' => $currentPosition,
                                                                        'thirdPartyPID' => $thirdPartyPID,));
        return $result;
    }
	
	public function createNewPlayer($login, $pw, $aid, $country, $casinoID, $thirdPartyPID)
	{
		$result = $this->_soapClient->call('createNewPlayer', array('login' => $login,
                                                                            'pw' => $pw,
                                                                            'aid' => $aid,
                                                                            'country' => $country,
                                                                            'casinoID' => $casinoID,
                                                                            'thirdPartyPID' => $thirdPartyPID));	  
		return $result;
	}
	
	public function ChangePlayerDetails($pid, $login, $fname, $lname, $email, $dayphone, $evephone, $addr1, $addr2, $city, $state, $zip, $country, $birthdate)
	{
		$result = $this->_soapClient->call('ChangePlayerDetails', array('pid' => $pid,
                                                                                'login' => $login,
                                                                                'fname' => $fname,
                                                                                'lname' => $lname,
                                                                                'email' => $email,
                                                                                'dayphone' => $dayphone,
                                                                                'evephone' => $evephone,
                                                                                'addr1' => $addr1,
                                                                                'addr2' => $addr2,
                                                                                'city' => $city,
                                                                                'state' => $state,
                                                                                'zip' => $zip,
                                                                                'country' => $country,
                                                                                'birthdate' => $birthdate));

		return $result;
	}
        
	public function changePlayerPassword($casinoID, $login, $oldpassword, $newpassword)
        {
                $result = $this->_soapClient->call('changePlayerPW', array('casinoID'=>$casinoID,
                                                                           'login'=>$login,
                                                                           'oldpw'=>$oldpassword,
                                                                           'newpw'=>$newpassword));
                return $result;
        }
	
}

?>