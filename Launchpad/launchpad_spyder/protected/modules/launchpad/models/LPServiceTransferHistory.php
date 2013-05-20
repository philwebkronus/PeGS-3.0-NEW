<?php

/**
 * Description of LPServiceTransferHistory
 * @package application.modules.launchpad.models
 * @author Bryan Salazar, elperez
 */
class LPServiceTransferHistory  extends LPModel
{
    
    /**
     *
     * @var LPServiceTransferHistory 
     */
    private static $_instance = null;    
    
    private function __construct() 
    {
        $this->_connection = LPDB::app();
    }
    
    /**
     * Get instance of LPServiceTransferHistory
     * @return LPServiceTransferHistory 
     */
    public static function model()
    {
        if(self::$_instance == null)
            self::$_instance = new LPServiceTransferHistory();
        return self::$_instance;
    }
    
    /**
     * Insert into servicetransferhistory table
     * @param int|string $amount
     * @param int $pickServiceID
     * @return bool|int false if no row affected else number of row affected
     */
    public function insert($amount,$pickServiceID)
    {
        $balance = Lib::moneyToDecimal($amount);
        $query = "INSERT INTO servicetransferhistory (`TerminalID`, `Amount`, " .
            "`FromServiceID`,`ToServiceID`,`DateCreated`) VALUES (:TerminalID, " .
            ":Amount,:FromServiceID,:ToServiceID,now_usec())";        
        $command = $this->_connection->createCommand($query);
        $data = array(
            ':TerminalID'=>Yii::app()->user->getState('terminalID'),
            ':Amount'=>$balance,
            ':FromServiceID'=>Yii::app()->user->getState('currServiceID'),
            ':ToServiceID'=>$pickServiceID,
        );    
        $n = $command->execute($data);
        if(!$n) {
            $this->log($command->getText().$command->getBound(), 'launchpad.models.LPServiceTransferHistory');
        }
        return $n;
    }
    
    /**
     * Update servicetransferhistory table
     * @param int $status
     * @param int $lastServID
     * @return bool|int false if no row affected else number of row affected
     */
    public function update($status,$lastServID)
    {
        $query = 'UPDATE servicetransferhistory SET Status = :Status ' . 
            'WHERE ServiceTransferHistoryID = :ServiceTransferHistoryID';
        $data = array(
            ':Status'=>$status,
            ':ServiceTransferHistoryID'=>$lastServID
        );
        $command = $this->_connection->createCommand($query);
        $n = $command->execute($data);
        if(!$n) {
            $this->log($command->getText().$command->getBound()." failed to update servicetransferhistory", 'launchpad.models.LPServiceTransferHistory');
        }
        return $n;
    }
    
    /**
     * Get last insert id
     * @return int 
     */
    public function getLastInsertID()
    {
        return $this->_connection->lastInsertID;
    }
    
    
    /**
     * Insert records in servicetransactionref
     * @param int $service_id
     * @param int $origin_id
     * @return bool|int false if no row affected else number of row affected
     */
    public function insertServiceTransRef($service_id, $origin_id)
    {
        $beginTrans = $this->_connection->beginTransaction();
        try{
            $query = "INSERT INTO servicetransactionref (ServiceID, TransactionOrigin, DateCreated) VALUES (:service_id, :origin_id, now_usec())";
            $command = $this->_connection->createCommand($query);
            $data = array(
                ':service_id'=>$service_id,
                ':origin_id'=>$origin_id
            );
            $n = $command->execute($data);
            if(!$n) {
                $this->log($command->getText().$command->getBound()." failed to insert servicetransactionref", 'launchpad.models.LPServiceTransferHistory');
            }
            $transaction_id = $this->getLastInsertID();
            $beginTrans->commit();
            return $transaction_id;
        } catch(Exception $e) {
            $beginTrans->rollback();
            return false;
        }
    }
}
