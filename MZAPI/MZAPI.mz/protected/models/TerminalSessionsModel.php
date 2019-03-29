<?php

class TerminalSessionsModel extends CFormModel {

    public $connection;

    public function __construct() {
        $this->connection = Yii::app()->db;
    }

    public function updateTerminalSession($NewServiceID, $TerminalID, $CardNumber, $NewUBServiceLogin, $NewUBServicePassword, $NewUBHashedPassword, $ActiveServiceStatus, $LastBalance) {


        $sql = "UPDATE terminalsessions SET 
                ServiceID = :ServiceID ,
                ActiveServiceID = :ServiceID ,
                UBServiceLogin = :UBServiceLogin,
                UBServicePassword = :UBServicePassword ,
                UBHashedServicePassword = :UBHashedServicePassword ,
                ActiveServiceStatus = :ActiveServiceStatus ,
                LastTransactionDate =  NOW(6) ,
                ActiveLastTransdateUpd =  NOW(6) ,
                LastBalance = :LastBalance
                WHERE TerminalID = :TerminalID AND LoyaltyCardNumber = :CardNumber";
        $command = $this->connection->createCommand($sql);
        $command->bindValue(":ServiceID", $NewServiceID);
        $command->bindValue(":UBServiceLogin", $NewUBServiceLogin);
        $command->bindValue(":UBServicePassword", $NewUBServicePassword);
        $command->bindValue(":UBHashedServicePassword", $NewUBHashedPassword);
        $command->bindValue(":ActiveServiceStatus", $ActiveServiceStatus);
        $command->bindValue(":LastBalance", $LastBalance);
        $command->bindValue(":TerminalID", $TerminalID);
        $command->bindValue(":CardNumber", $CardNumber);
        $result = $command->execute();
        try {
            if ($result > 0) {
                return true;
            } else {
                return false;
            }
        } catch (PDOException $e) {
            Utilities::log($e->getMessage());
            return false;
        }
    }

    public function checkActiveSession($terminalCode) {
        $sql = "SELECT COUNT(ts.TerminalID) AS Cnt FROM terminalsessions ts 
                    INNER JOIN terminals t ON ts.TerminalID = t.TerminalID
                    WHERE t.TerminalCode IN ('" . $terminalCode . "', '" . $terminalCode . "VIP')";
        $command = $this->connection->createCommand($sql);
        $result = $command->queryRow();

        return $result;
    }

    public function checkActiveWallet($terminalCode) {
        $sql = "SELECT serviceid FROM terminalsessions ts 
                    INNER JOIN terminals t ON ts.TerminalID = t.TerminalID
                    WHERE t.TerminalCode IN ('" . $terminalCode . "', '" . $terminalCode . "VIP')";
        $command = $this->connection->createCommand($sql);
        $result = $command->queryRow();

        return $result;
    }

    public function checkActiveServiceStatus($terminalCode) {
        $sql = "SELECT ActiveServiceStatus FROM terminalsessions ts 
                    INNER JOIN terminals t ON ts.TerminalID = t.TerminalID
                    WHERE t.TerminalCode IN ('" . $terminalCode . "', '" . $terminalCode . "VIP')";
        $command = $this->connection->createCommand($sql);
        $result = $command->queryRow();

        return $result;
    }

    public function getTerminalSessionDetails($terminalCode) {
        $sql = "SELECT * FROM terminalsessions ts 
                    INNER JOIN terminals t ON ts.TerminalID = t.TerminalID
                    WHERE t.TerminalCode IN ('" . $terminalCode . "', '" . $terminalCode . "VIP')";
        $command = $this->connection->createCommand($sql);
        $result = $command->queryRow();

        return $result;
    }

    public function updateActiveServiceStatus($TerminalID, $CardNumber, $ActiveServiceStatus) {


        $sql = "UPDATE terminalsessions SET 
                ActiveServiceStatus = :ActiveServiceStatus 
                WHERE TerminalID = :TerminalID AND LoyaltyCardNumber = :CardNumber";
        $command = $this->connection->createCommand($sql);
        $command->bindValue(":ActiveServiceStatus", $ActiveServiceStatus);
        $command->bindValue(":TerminalID", $TerminalID);
        $command->bindValue(":CardNumber", $CardNumber);
        try {
            $command->execute();
            return true;
        } catch (PDOException $e) {
            Utilities::log($e->getMessage());
            return false;
        }
    }

}

?>

