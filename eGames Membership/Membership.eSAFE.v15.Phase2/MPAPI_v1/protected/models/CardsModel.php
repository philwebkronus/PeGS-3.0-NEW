<?php

/**
 * @author fdlsison
 * 
 * @date 6-24-2014
 */

class CardsModel {
    public static $_instance = null;
    public $_connection;


    public function __construct() {
        $this->_connection = Yii::app()->db2;
    }
    
    public static function model()
    {
        if(self::$_instance == null)
            self::$_instance = new CardsModel();
        return self::$_instance;
    }
    
    //@date 6-24-2014
    public function IsExist($ubCard) {
        $sql = 'SELECT *
                FROM cards
                WHERE CardNumber = :UBCard';
        $param = array(':UBCard' => $ubCard);
        $command = $this->_connection->createCommand($sql);
        $result = $command->queryRow(true, $param);
        
        if($result)
            return 1;
        else
            return 0;
    }
    
    //@purpose get member details using card number
    public function getMemberInfoUsingCardNumber($cardNumber) {
        $sql = 'SELECT b.CurrentPoints, b.BonusPoints, b.RedeemedPoints, b.LifetimePoints
                FROM cards a
                INNER JOIN membercards b ON a.CardID = b.CardID
                INNER JOIN membership.memberinfo c ON b.MID = c.MID
                WHERE a.CardNumber = :CardNumber AND b.Status IN(1, 5)';
        $param = array(':CardNumber' => $cardNumber);
        $command = $this->_connection->createCommand($sql);
        $result = $command->queryRow(true, $param);
        
        return $result;
    }
    
    //@date 07-07-2014
    //@purpose get card info
    public function getCardInfo($cardNumber) {
        $sql = 'SELECT *
                FROM cards
                WHERE CardNumber = :cardNumber';
        $param = array(':cardNumber' => $cardNumber);
        $command = $this->_connection->createCommand($sql);
        $result = $command->queryRow(true, $param);
        
        return $result;

    }

    public function updateCardStatus($tempcardid, $TempCardStatus, $AID) {

        $startTrans = $this->_connection->beginTransaction();

        try {
            $sql = 'UPDATE cards
                    SET Status = :Status, UpdatedByAID = :UpdatedByAID , DateUpdated = NOW(6)
                    WHERE CardID = :CardID';
            $param = array(':Status' => $TempCardStatus, ':UpdatedByAID' => $AID, 'CardID' => $tempcardid);
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
        } catch (Exception $e) {
            $startTrans->rollback();
            Utilities::log($e->getMessage());
            return 0;
        }
    }    
            
}

