<?php

/**
 * Description of LPMemberServices
 * @package application.modules.launchpad.models
 * @author aqdepliyan
 */
class LPMemberServices extends LPModel
{
    /**
     *
     * @var LPMemberServices 
     */
    private static $_instance;
    
    private function __construct() 
    {
        $connstring = LPConfig::app()->params["db2"]["connectionString"];
        $username = LPConfig::app()->params["db2"]["username"];
        $password = LPConfig::app()->params["db2"]["password"];
        $this->_connection = new LPDB ($connstring, $username, $password);
    }
    
    /**
     * Get instance of LPMemberServices
     * @return LPMemberServices 
     */
    public static function model()
    {
        if(self::$_instance == null)
            self::$_instance = new LPMemberServices();
        return self::$_instance;
    }
    
    
    /**
     * @Description: Get UB Credentials (for User-Based casino)
     * @param int $mid
     * @param int $serviceid
     * @return array
     * @throws CHttpException
     */
    public function GetUBCredentials($serviceID, $UBusername, $MID = '')
    {
        
        if($MID != ''){
            $query = 'SELECT ms.ServiceUsername, ms.HashedServicePassword, ms.ServicePassword 
                                FROM membership.memberservices ms
                                WHERE ms.ServiceID = :serviceid 
                                AND ms.MID = :mid;';
            $command = $this->_connection->createCommand($query);
            $row =  $command->queryRow(true,array(':serviceid'=>$serviceID, ':mid'=>$MID));
        } else {
            $query = 'SELECT ms.ServiceUsername, ms.HashedServicePassword, ms.ServicePassword 
                                FROM membership.memberservices ms
                                WHERE ms.ServiceID = :serviceid 
                                AND ms.MID = (SELECT MID FROM membership.memberservices 
                                                        WHERE ServiceUsername = :ubusername LIMIT 1);';
            $command = $this->_connection->createCommand($query);
            $row =  $command->queryRow(true,array(':serviceid'=>$serviceID, ':ubusername'=>$UBusername));
        }

        if(count($row) == 0) {
            $this->log(''," Can't get User Credentials", 'launchpad.models.LPServiceTerminals');
            throw new CHttpException(404, 'Can\'t get User Credentials');
        }
        return $row;
    }
}

?>