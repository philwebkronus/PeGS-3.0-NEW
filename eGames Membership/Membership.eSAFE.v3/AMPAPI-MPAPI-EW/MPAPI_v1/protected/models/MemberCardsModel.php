<?php

/**
 * @author fdlsison
 * 
 * @date 6-19-2014
 */

class MemberCardsModel {
    public static $_instance = null;
    public $_connection;

    public function __construct() {
        $this->_connection = Yii::app()->db2;
    }
    
    public static function model()
    {
        if(self::$_instance == null)
            self::$_instance = new MemberCardsModel();
        return self::$_instance;
    }
    
    //@author Ralph Sison
    //@date 6-18-2014
    public function getMemberDetailsByCard($cardNumber) {
        $sql = 'SELECT a.MID, a.CurrentPoints, b.Status, b.CardTypeID
                FROM membercards a
                INNER JOIN cards b ON a.CardID = b.CardID
                WHERE b.CardNumber = :CardNumber';
        $param = array(':CardNumber' => $cardNumber);
        $command = $this->_connection->createCommand($sql);
        $result = $command->queryRow(true, $param);
        
        return $result;
    }
    
    public function getCardStatus($cardNumber) {
        $sql = 'SELECT Status
                FROM membercards
                WHERE CardNumber = :CardNumber AND Status IN (1,5)
                GROUP BY MID';
        $param = array(':CardNumber' => $cardNumber);
        $command = $this->_connection->createCommand($sql);
        $result = $command->queryRow(true, $param);
        
        return $result;            
    }
     
    public function getMIDUsingCard($cardNumber) {
        $sql = 'SELECT mc.MID, c.Status
                FROM membercards mc
                    INNER JOIN cards c ON mc.CardID = c.CardID
                WHERE c.CardNumber = :CardNumber';
        $param = array(':CardNumber' => $cardNumber);
        $command = $this->_connection->createCommand($sql);
        $result = $command->queryRow(true, $param);
        
        return $result;
    }
    
    //@date 6-18-2014
    public function getCardNumberUsingMID($MID) {
        $sql = 'SELECT a.CardNumber
                FROM membercards a
                INNER JOIN cards b ON a.CardID = b.CardID
                WHERE a.MID = :MID AND a.Status IN(1,5)';
        $param = array(':MID' => $MID);
        $command = $this->_connection->createCommand($sql);
        $result = $command->queryRow(true, $param);
        
        return $result['CardNumber'];
    }
    
    //@date 6-24-2014
    //@purpose get active member card
    public function getActiveMemberCardInfo($MID) {
        $sql = "SELECT a.*, b.IsVIP,
                 CASE c.CardTypeID
                    WHEN 1 THEN 'Gold'
                    WHEN 2 THEN 'Green'
                    WHEN 3 THEN 'Temporary'
                 END AS CardType, c.CardTypeID
                FROM membercards a
                 INNER JOIN membership.members b ON a.MID = b.MID
                 INNER JOIN cards c ON a.CardID = c.CardID AND a.CardNumber = c.CardNumber
                WHERE a.MID = :MID AND a.Status IN(1,5)";
        $param = array(':MID' => $MID);
        $command = $this->_connection->createCommand($sql);
        $result = $command->queryRow(true, $param);
        
        return $result;
    }
    
    //@date 07-01-2014
    //@purpose updating card points
    public function updateCardPoints($MID, $redeemTotalPoints) {
        $startTrans = $this->_connection->beginTransaction();
        
        try {
            $sql = 'UPDATE membercards
                    SET RedeemedPoints = RedeemedPoints + :RedeemTotalPoints
                    WHERE MID = :MID AND Status IN (1,5)';
            $param = array(':RedeemTotalPoints' => $redeemTotalPoints, ':MID' => $MID);
            $command = $this->_connection->createCommand($sql);
            $command->bindValues($param);
            $command->execute();
            
            try {
                $startTrans->commit();
                return 1;
            } catch (PDOException $e) {
                $startTrans->rollback();
                Utilities::log($e->getMessage());
                return 0;
            }
        } catch(Exception $e) {
            $startTrans->rollback();
            Utilities::log($e->getMessage());
            return 0;
        }
    }           
}