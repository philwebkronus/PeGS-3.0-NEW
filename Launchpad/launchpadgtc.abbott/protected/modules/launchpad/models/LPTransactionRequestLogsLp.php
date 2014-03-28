<?php

/**
 * Description of LPTransactionRequestLogsLp
 * @package application.modules.launchpad.models
 * @author Bryan Salazar
 */
class LPTransactionRequestLogsLp extends LPModel
{
    
    /**
     *
     * @var LPTransactionRequestLogsLp 
     */
    private static $_instance;
    
    private $_referenceID;
    
    private function __construct() 
    {
        $this->_connection = LPDB::app();
        
    }
    
    /**
     * Get instance of LPTransactionRequestLogsLp
     * @return LPTransactionRequestLogsLp 
     */
    public static function model()
    {
        if(self::$_instance == null)
            self::$_instance = new LPTransactionRequestLogsLp();
        return self::$_instance;
    }
    
    /**
     * Insert into transactionrequestlogslp table
     * @param string|int $balance the balance will always convert to decimal
     * @param int $lastServTransHisID
     * @param string $transType ('D', 'R', 'W')
     * @param int $serviceID 
     */
    public function insert($balance,$lastServTransHisID,$transType,$serviceID, 
            $loyaltyCardNo, $mid, $UserMode, $lpTransactionID)
    {
        try{
            $query = 'INSERT INTO transactionrequestlogslp(TransactionReferenceID,
                Amount,StartDate,EndDate,TransactionType,TerminalID,Status,SiteID, 
                ServiceTransactionID,ServiceStatus,ServiceID, ServiceTransferHistoryID, 
                LoyaltyCardNumber, MID,UserMode,TransactionSummaryID, PaymentType) 
                VALUES (:TransactionReferenceID, :Amount, now_usec(), :EndDate, 
                :TransactionType,:TerminalID,:Status,:SiteID, :ServiceTransactionID, 
                :ServiceStatus,:ServiceID, :ServiceTransferHistoryID, :LoyaltyCardNo,
                :mid, :UserMode, :TransactionSummaryID,:PaymentType)';
        
            $balance = Lib::moneyToDecimal($balance);
            $transSummaryID = LPTerminalSessions::model()->getTransactionSummaryID(Yii::app()->user->getState('terminalID'));
            $this->_referenceID = $udate = Lib::udate('YmdHisu');

            $command = $this->_connection->createCommand($query);
            
            $data = array(
                ':TransactionReferenceID' => $udate,
                ':Amount'=> $balance,
                //':StartDate'=> new CDbExpression('NOW_USEC()'),
                ':EndDate'=> '',
                ':TransactionType'=>$transType,
                ':TerminalID'=> Yii::app()->user->getState('terminalID'),
                ':Status'=>0,
                ':SiteID'=>Yii::app()->user->getState('siteID'),
                ':ServiceTransactionID'=>$lpTransactionID,
                ':ServiceStatus'=>'',
                ':ServiceID'=> $serviceID,
                ':ServiceTransferHistoryID'=> $lastServTransHisID,
                ':LoyaltyCardNo'=>$loyaltyCardNo,
                ':mid'=>$mid,
                ':UserMode'=>$UserMode,
                ':TransactionSummaryID'=>$transSummaryID,
                ':PaymentType'=>1,
            );
            
            $command->bindValues($data);
            
            $n = $command->execute();

            if(!$n) {
                $this->log(' failed to insert to transactionrequestlogslp', 'launchpad.models.LPTransactionRequestLogsLp');
                throw new CHttpException(404, 'There was a pending transaction for this user / terminal.');
            }
            return $n;
        
        }  catch (Exception $e) {
            $this->log(' failed to insert to transactionrequestlogslp', 'launchpad.models.LPTransactionRequestLogsLp');
            throw new CHttpException(404, 'There was a pending transaction for this user / terminal.');
        }
        
    }
    
    /**
     * Update transactionrequestlogslp table
     * @param int $status
     * @param int $transServID
     * @param string $apiResult
     * @param string $endDate
     * @param string $transRefID transaction if from webservice
     * @return bool|int false if no row affected else number of row affected 
     */
    public function update($status,$transServID,$apiResult,$endDate,$transRefID,$transRequestLogLPID)
    {
        $query = 'UPDATE `transactionrequestlogslp` SET ' .
            '`Status` = :Status, ' .
            '`ServiceTransactionID` = :ServiceTransactionID, ' .
            '`EndDate` = now_usec(), ' .
            '`ServiceStatus` = :ServiceStatus ' .
            'WHERE TransactionRequestLogLPID = :TransactionRequestLogLPID';
        
        $command = $this->_connection->createCommand($query);
        
        $data = array(
            ':Status'=>$status,
            ':ServiceTransactionID'=>$transServID,
            ':ServiceStatus'=>$apiResult,
            ':TransactionRequestLogLPID'=>$transRequestLogLPID,
        );
        
        $command->bindValues($data);
        
        $n = $command->execute();
        
        if(!$n) {
            $this->log(' failed to update to transactionrequestlogslp', 'launchpad.models.LPTransactionRequestLogsLp');
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
     * Get reference id
     * @return string 
     */
    public function getReferenceID()
    {
        return $this->_referenceID;
    }
}
