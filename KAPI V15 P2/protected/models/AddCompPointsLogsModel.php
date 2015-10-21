<?php
/**
 * Description of AddCompPointsLogsModel
 *
 * @author mcatangan
 * @Date 10/2/2015
 */
class AddCompPointsLogsModel extends CFormModel {
    public static $_instance = null;
    public $_connection;
    


    public function __construct() {
        $this->_connection = Yii::app()->db;
    }
    
    
    public static function model()
    {
        if(self::$_instance == null)
            self::$_instance = new LoyaltyRequestLogsModel();
        return self::$_instance;
    }
     public function addCompPointsLogs($mid, $cardNumber, $terminalID, $siteID, $serviceID, $amount, $transType) {
        $sql = "INSERT INTO comppointslogs(MID, LoyaltyCardNumber, TerminalID, SiteID, ServiceID, Amount, TransactionDate, TransactionType)
                VALUES(:mid, :cardNumber, :terminalID, :siteID, :serviceID, :amount, NOW(6), :transType)";
        $command = $this->_connection->createCommand($sql);
        $param = array(':mid'=>$mid, ':cardNumber'=>$cardNumber,':terminalID'=>$terminalID,':siteID'=>$siteID,':serviceID'=>$serviceID,  ':amount'=>$amount, ':transType'=>$transType);
          $smt->execute($param);
                $transaction_id = $this->_connection->getLastInsertID();
                return $transaction_id;
 }
 
}
