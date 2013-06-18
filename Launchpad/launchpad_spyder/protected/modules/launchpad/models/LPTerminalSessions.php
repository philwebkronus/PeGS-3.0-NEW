<?php

/**
 * Description of LPTerminalSessions
 * @package application.modules.launchpad.models
 * @author Bryan Salazar
 */
class LPTerminalSessions extends LPModel
{
    /**
     *
     * @var LPTerminalSessions 
     */
    private static $_instance = null;
    
    private function __construct() 
    {
        $this->_connection = LPDB::app();
    }
    
    /**
     * Get instance of LPTerminalSessions
     * @return LPTerminalSessions 
     */
    public static function model()
    {
        if(self::$_instance == null)
            self::$_instance = new LPTerminalSessions();
        return self::$_instance;
    }

    /**
     * Check if terminal code has active session vip or not vip
     * @param string $terminalCode
     * @return bool|int false if no row affected else number of row affected
     */
    public function isLogin($terminalCode)
    {
        $vip = LPConfig::app()->params['vip'];
        $query = "SELECT t.TerminalID, t.TerminalCode, ts.TransactionSummaryID, t.SiteID, 
                ts.ServiceID, ts.LoyaltyCardNumber, ts.UserMode, ts.UBServiceLogin,
                ts.UBServicePassword, ts.UBHashedServicePassword FROM terminals t 
                INNER JOIN terminalsessions ts ON ts.TerminalID = t.TerminalID
                WHERE t.TerminalCode IN (:terminalCode, :withvip)";
        $command=$this->_connection->createCommand($query);
        $row=$command->queryRow(true, array(':terminalCode'=>$terminalCode, ':withvip'=>$terminalCode.$vip));
        
        if(!$row) {
            $command=$this->_connection->createCommand($query);
            
            if(stripos($terminalCode, $vip) === false) { // case-insensitive
                $terminalCode = $terminalCode.$vip;
            } else {
                $terminalCode = Lib::removeVip($terminalCode);
            }
            
            $row=$command->queryRow(true, array(':terminalCode'=>$terminalCode, ':withvip'=>$terminalCode.$vip));
        } 
        Yii::app()->user->setState('terminalCode', $row['TerminalCode']);
        Yii::app()->user->setState('casinoMode', $row['UserMode']);
        Yii::app()->user->setState('UBUsername', $row['UBServiceLogin']);
        Yii::app()->user->setState('UBPlainPwd', $row['UBServicePassword']);
        Yii::app()->user->setState('UBHashedPwd', $row['UBHashedServicePassword']);
        return $row;
    }
    
    /**
     * Get current casino by terminal code
     * @param string $terminalCode
     * @return bool|array false if no row affected
     */
    public function getCurrentCasino($terminalCode) 
    {
        $query = 'SELECT ts.ServiceID FROM terminals t ' . 
            'INNER JOIN terminalsessions ts ON ts.TerminalID = t.TerminalID ' . 
            'WHERE t.TerminalCode = :terminalCode';
        $command=$this->_connection->createCommand($query);
        $row=$command->queryRow(true, array(':terminalCode'=>$terminalCode));
        if(!$row) {
            $this->log("Can't get current casino", 'launchpad.models.LPTerminalSessions');
            throw new CHttpException(404, "Can't get current casino");
        }
            
        return $row;
    }
    
    /**
     * Get ServiceID and Alias
     * @param type $terminalID
     * @return array array('Alias'=>'','ServiceID'=>'') 
     */
    public function getCurrentCasinoByTerminalID($terminalID)
    {
        $query = 'SELECT rs.Alias, rs.ServiceID FROM terminalsessions ts ' . 
            'INNER JOIN ref_services rs ON rs.ServiceID = ts.ServiceID ' . 
            'WHERE ts.TerminalID = :terminalID';
        $command=$this->_connection->createCommand($query);
        $row=$command->queryRow(true, array(':terminalID'=>$terminalID));
        if(!$row) {
            $this->log("Can't get current casino", 'launchpad.models.LPTerminalSessions');
            throw new CHttpException(404, "Can't get current casino");
        }
        return $row;
    }
    
    /**
     *
     * @param type $terminalID
     * @param type $serviceID
     * @return bool|int false if no row affected else number of row affected 
     */
    public function updateCurrentServiceID($terminalID,$serviceID) 
    {
        $query = 'UPDATE terminalsessions SET ServiceID = :serviceID WHERE TerminalID = :terminalID';
        $data = array(
            ':terminalID'=>$terminalID,
            ':serviceID'=>$serviceID
        );
        $command = $this->_connection->createCommand($query);
        $n = $command->execute($data);
        if(!$n) {
            $this->log(" failed to update servicetransferhistory", 'launchpad.models.LPTerminalSessions');
        }
        return $n;
    }
    
    public function getTerminalPassword($terminalID, $serviceID)
    {
        $query = "SELECT t.ServicePassword, t.HashedServicePassword FROM terminalservices t 
                  WHERE t.TerminalID = :terminal_id AND ServiceID = :service_id 
                  AND t.Status = 1";
        $data = array(
            ':terminal_id'=>$terminalID,
            ':service_id'=>$serviceID
        );
        
        $command=$this->_connection->createCommand($query);
        $row=$command->queryRow(true, $data);
        if(!$row) {
            $this->log("Can't get casino password", 'launchpad.models.LPTerminalSessions');
            //throw new CHttpException(404, "Can't get casino password");
        }
        return $row;
    }
    
    public function getTransactionSummaryID($terminalID) {
        $query = "SELECT TransactionSummaryID FROM terminalsessions WHERE TerminalID = :terminal_id AND DateEnded = 0";
        $data = array(':terminal_id'=>$terminalID);
        $command = $this->_connection->createCommand($query);
        $row = $command->queryRow(true, $data);
        if(!$row) {
            $message = "Can't get session ID";
            $this->log($message, 'launchpad.models.LPTerminalSessions');
            throw new CHttpException(404, $message);
        }
        return $row['TransactionSummaryID'];
    }
    
    public function getTerminalDetails($terminalID) {
        $query = "SELECT LoyaltyCardNumber, MID, UserMode FROM terminalsessions WHERE TerminalID = :terminal_id AND DateEnded = 0";
        $data = array(':terminal_id'=>$terminalID);
        $command = $this->_connection->createCommand($query);
        $row = $command->queryRow(true, $data);
        if(!$row) {
            $message = "Can't get session ID";
            $this->log($message, 'launchpad.models.LPTerminalSessions');
            throw new CHttpException(404, $message);
        }
        return $row;
    }
}