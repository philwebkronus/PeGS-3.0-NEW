<?php

class TerminalSessionsModel extends CFormModel {

    public $connection;

    public function __construct() {
        $this->connection = Yii::app()->db;
    }


    public function checkSessionIDwithcard($cardnumber, $serviceid) {
        $sql = "SELECT TerminalID FROM terminalsessions WHERE LoyaltycardNumber = :cardnumber AND ServiceID = :serviceid";
        $command = $this->connection->createCommand($sql);
        $command->bindValue(":cardnumber", $cardnumber);
        $command->bindValue(":serviceid", $serviceid);
        $result = $command->queryRow();

        return $result;
    }

    public function isCardHasActiveSession($cardNumber) {
        $sql = "SELECT COUNT(TerminalID) AS Cnt FROM terminalsessions WHERE LoyaltyCardNumber = :cardNumber AND DateEnded = 0";
        $command = $this->connection->createCommand($sql);
        $command->bindValue(':cardNumber', $cardNumber);
        $result = $command->queryRow();

        return $result;
    }

    public function getTransSummaryID($mid, $serviceid) {
        $sql = "SELECT TransactionSummaryID FROM terminalsessions WHERE MID = :mid AND ServiceID = :serviceid";
        $command = $this->connection->createCommand($sql);
        $command->bindValue(':mid', $mid);
        $command->bindValue(":serviceid", $serviceid);
        $result = $command->queryRow();

        return $result;
    }

    public function updateTerminalState($terminalid, $serviceid, $cardnumber) {
        $sql = "UPDATE terminalsessions SET Status = 1 WHERE TerminalID = :terminalID AND ServiceID = :serviceID AND LoyaltyCardNumber = :cardNumber";
        $command = $this->connection->createCommand($sql);
        $command->bindValue(':terminalID', $terminalid);
        $command->bindValue(":serviceID", $serviceid);
        $command->bindValue(":cardNumber", $cardnumber);
        try {
            $command->execute();
            return 1;
        } catch (PDOException $e) {
            Utilities::log($e->getMessage());
            return 0;
        }
    }

    public function checkSessionValidity($terminalid, $serviceid, $cardnumber) {
        $sql = "SELECT COUNT(TerminalID) AS Cnt FROM terminalsessions WHERE TerminalID = :terminalID AND ServiceID = :serviceID AND LoyaltyCardNumber = :cardNumber";
        $command = $this->connection->createCommand($sql);
        $command->bindValue(':terminalID', $terminalid);
        $command->bindValue(":serviceID", $serviceid);
        $command->bindValue(":cardNumber", $cardnumber);
        $result = $command->queryRow();

        return $result;
    }

    public function getCasinoDetailsByUBServiceLogin($ServiceUsername) {
        $sql = "SELECT TerminalID, ServiceID, LoyaltyCardNumber,MID,UserMode,TransactionSummaryID FROM terminalsessions WHERE UBServiceLogin=:serviceUsername";
        $command = $this->connection->createCommand($sql);
        $command->bindValue(':serviceUsername', $ServiceUsername);
        $result = $command->queryRow();

        return $result;
    }

    public function isTerminalHasActiveUBSession($terminalID) {
        $sql = "SELECT COUNT(TerminalID) AS Cnt FROM terminalsessions WHERE TerminalID=:terminal_id";
        $command = $this->connection->createCommand($sql);
        $command->bindValue(':terminal_id', $terminalID);
        $result = $command->queryRow();

        return $result;
    }
    
    public function checkActiveSession($terminalCode) {
        $sql = "SELECT COUNT(ts.TerminalID) AS Cnt FROM terminalsessions ts 
                    INNER JOIN terminals t ON ts.TerminalID = t.TerminalID
                    WHERE t.TerminalCode IN ('".$terminalCode."', '".$terminalCode."VIP')";
        $command = $this->connection->createCommand($sql);
        $result = $command->queryRow();

        return $result;
    }

    public function isCardHasActiveUBSession($cardNumber) {
        $sql = "SELECT COUNT(tss.TerminalID) AS Cnt FROM terminalsessions tss INNER JOIN ref_services rs ON tss.ServiceID=rs.ServiceID WHERE tss.LoyaltyCardNumber=:cardNumber AND rs.UserMode=1";
        $command = $this->connection->createCommand($sql);
        $command->bindValue(':cardNumber', $cardNumber);
        $result = $command->queryRow();

        return $result;
    }

    /**
     * Check if both Regular and VIP terminal has an active session. 
     * This is the same as <b>isSessionActive</b> but checks both reg 
     * and VIP terminals
     * @param array $terminalIDs
     * @param string $cardNumber
     * @author Mark Kenneth Esguerra
     * @date May 15, 2015
     */
    public function isArrTerminalHasSession($terminalIDs, $cardNumber) {
        $sql = "SELECT TransactionSummaryID, TerminalID  
                FROM terminalsessions 
                WHERE TerminalID IN ($terminalIDs) 
                AND LoyaltyCardNumber = :cardNumber AND DateEnded = 0";
        $command = $this->connection->createCommand($sql);
        $command->bindValues(array(':cardNumber' => $cardNumber));
        $result = $command->queryRow();

        return $result;
    }

    /**
     * Delete terminal session using card number and terminal number. 
     * @param int $terminalID
     * @param string $cardnumber
     * @return array Transaction result
     * @author Mark Kenneth Esguerra
     * @date May 15, 2015
     */
    public function deleteSessionByCardAndTerminal($terminalID, $cardnumber) {
        $pdo = $this->connection->beginTransaction();

        try {

            $sql = "DELETE FROM terminalsessions 
                    WHERE TerminalID = :terminal AND LoyaltyCardNumber = :cardnumber";
            $command = $this->connection->createCommand($sql);
            $command->bindValue(":terminal", $terminalID);
            $command->bindValue(":cardnumber", $cardnumber);
            $result = $command->execute();
            if ($result > 0) {
                try {
                    $pdo->commit();
                    return array('TransCode' => 0,
                        'TransMsg' => 'Terminal session has successfully removed.');
                } catch (CDbException $e) {
                    $pdo->rollback();
                    return array('TransCode' => 1,
                        'TransMsg' => 'An error occured while deleting the session.');
                }
            } else {
                return array('TransCode' => 1,
                    'TransMsg' => 'Failed to delete terminal session.');
            }
        } catch (CDbException $e) {
            return array('TransCode' => 1,
                'TransMsg' => 'An error occured while deleting the session.');
        }
    }

    public function checkeSafeSessionValidity($mid, $terminalID) {
        $sql = "SELECT IFNULL(TransactionSummaryID,'') FROM terminalsessions WHERE TerminalID = :terminal_id AND MID = :mid";
        $command = $this->connection->createCommand($sql);
        $command->bindValues(array(':terminal_id' => $terminalID, ':mid' => $mid));
        $result = $command->queryRow();

        return $result;
    }

    public function getTerminalName($terminalID) {
        $sql = "SELECT TerminalName FROM terminals WHERE TerminalID = :terminal_id";
        $command = $this->connection->createCommand($sql);
        $command->bindValues(array(':terminal_id' => $terminalID));
        $result = $command->queryRow();

//        foreach ($result as $row) {
            $TerminalName = $result['TerminalName'];
//        }
         
        return $TerminalName;
    }

}

?>
