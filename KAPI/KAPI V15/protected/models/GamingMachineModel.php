<?php

/**
 * Model for egmmachineinfo 
 * date created 10/12/12
 * @author elperez
 */
class GamingMachineModel{
    public static $_instance = null;
    public $_connection;


    public function __construct() {
        $this->_connection = Yii::app()->db;
    }
    
    public static function model()
    {
        if(self::$_instance == null)
            self::$_instance = new GamingMachineModel();
        return self::$_instance;
    }
    
    public function getMachineDetails($terminalID){
        $sql = "SELECT POSAccountNo, CreatedByAID FROM egmmachineinfo 
                WHERE TerminalID = :terminal_id OR TerminalIDVIP = :terminal_id";
        $params = array(":terminal_id"=>$terminalID);
        $command = $this->_connection->createCommand($sql);
        $result = $command->queryRow(true, $params);
        return $result;
    }
    
    /**
     * Gets the Dummy Loyalty Barcode if 
     * @param type $terminalID 
     */
    public function getDummyLoyalty($terminalID){
        $sql = "SELECT LoyaltyBarcode FROM egmmachineinfo WHERE TerminalID = :terminal_id
                OR TerminalIDVIP = :terminal_id";
        $params = array(":terminal_id"=>$terminalID);
        $command = $this->_connection->createCommand($sql);
        $result = $command->queryRow(true, $params);
        return $result['LoyaltyBarcode'];
    }
    
    public function getPOSAccountNo($token)
    {
        $sql = "SELECT POSAccountNo FROM egmmachineinfo WHERE Token = :token AND Status = 1";
        $params = array(':token'=>$token);
        $command = $this->_connection->createCommand($sql);
        $row = $command->queryRow(true, $params);
        return $row['POSAccountNo'];
    }
    
    public function getMachineInfo($machineID, $terminalCode){
        $stmt = "SELECT mac.EGMMachineInfoId_PK, mac.TerminalID, mac.TerminalIDVIP, 
                 mac.CreatedByAID, t.SiteID, mac.Token, mac.Status as egmstatus, 
                 t.Status = 1 as tstatus FROM egmmachineinfo mac
                 INNER JOIN terminals t ON mac.TerminalID = t.TerminalID
                 WHERE mac.Machine_Id = :machineId AND t.TerminalCode = :terminalCode";
        $params = array(':machineId'=>$machineID,':terminalCode'=>$terminalCode);
        $command = $this->_connection->createCommand($stmt);
        return $command->queryRow(true, $params);
    }
    
    /**
     * Initially updates machine token if not set
     * @param str $token
     * @param str $machine_Id
     * @return bool 
     */
    public function updateToken($token, $machine_Id){
        try {
            $stmt = "UPDATE egmmachineinfo SET Token = :token 
                 WHERE Machine_Id = :machine_id AND Status = 1";
            $params = array(':machine_id'=>$machine_Id,':token'=>$token);
            $command = $this->_connection->createCommand($stmt);
            $command->bindValues($params);
            $command->execute();
            return true;
        } catch (Exception $e) {
            Utilities::log($e->getMessage());
            return false;
        }
    }
    
    public function getEGMMachineInfoID($token)
    {
        $sql = "SELECT EGMMachineInfoId_PK FROM egmmachineinfo WHERE TokenID = :tokenid";
        $params = array(':tokenid'=>$token);
        $command = $this->_connection->createCommand($sql);
        $row = $command->queryRow(true, $params);
        return $row['EGMMachineInfoId_PK'];
    }
    
    //Get Machine token Details
    public function getMacInfoByToken($token){
        $sql = "SELECT Status, TerminalID, TerminalIDVIP, POSAccountNo FROM egmmachineinfo
                WHERE Token = :token";
        $params = array(':token'=>$token);
        $command = $this->_connection->createCommand($sql);
        return $command->queryRow(true, $params);
    }
    
    //Verify if token was set
    public function verifyToken($token)
    {
        $sql = "SELECT COUNT(EGMMachineInfoId_PK) as ctrtoken FROM egmmachineinfo WHERE Token = :tokenid;";
        $params = array(':tokenid'=>$token);
        $command = $this->_connection->createCommand($sql);
        $row = $command->queryRow(true, $params);
        return $row['ctrtoken'];
    }
}

?>
