<?php
/**
 * Manipulates partner's session
 * @author Mark Kenneth Esguerra
 * @date October 1, 2013
 * @copyright (c) 2013, Philweb Corporation
 */
class PartnerSessionModel extends CFormModel
{
    public $partnerpid;
    /**
     * Checks if the after has session
     * @param int $partnerPID Partner's user ID
     * @return array PartnerPID
     * @author Mark Kenneth Esguerra
     * @date October 1, 2013
     */
    public function checkSession($partnerPID)
    {
        $connection = Yii::app()->db;
        
        $query = "SELECT PartnerPID FROM partnersessions
                  WHERE PartnerPID = :partnerPID";
        $command = $connection->createCommand($query);
        $command->bindParam(":partnerPID", $partnerPID);
        $result = $command->queryAll();
        
        return $result;
    }
    /**
     * Updates the session of the partner
     * @param int $partnerPID Partner user ID
     * @param string $sessionID New session ID
     * @author Mark Kenneth Esguerra
     * @date October 1, 2013
     */
    public function updateSession($partnerPID, $sessionID)
    {
        $connection = Yii::app()->db;
        
        $pdo = $connection->beginTransaction();
        
        $query = "UPDATE partnersessions SET SessionID = :sessionID, DateCreated = NOW()
                  WHERE PartnerPID = :partnerPID";
        $command = $connection->createCommand($query);
        $command->bindParam(":sessionID", $sessionID);
        $command->bindParam(":partnerPID", $partnerPID);
        $result = $command->execute();
        if ($result > 0)
        {
            try
            {
                $pdo->commit();
                return array('TransCode' => 1, 'TransMsg' =>'Session successfully updated');
            }
            catch(CDbException $e)
            {
                $pdo->rollback();
                return array('TransCode' => 0, 'TransMsg' =>'Error: '.$e->getMessage());
            }
        }
        else
        {
            $pdo->rollback();
            return array('TransCode' => 0, 'TransMsg' =>'Session details unchanged');
        }
    }
    /**
     * Add Session
     * @param int $partnerPID Partner's user ID
     * @param string $sessionID Session ID
     * @return array Transaction results
     * @author Mark Kenneth Esguerra
     * @date October 1, 2013
     */
    public function addSession($partnerPID, $sessionID)
    {
        $connection = Yii::app()->db;
        
        $pdo = $connection->beginTransaction();
        
        $query = "INSERT INTO partnersessions (PartnerPID, SessionID, DateCreated)
                  VALUES (:partnerPID, :sessionID, NOW())";
        
        $command = $connection->createCommand($query);
        $command->bindParam(":partnerPID", $partnerPID);
        $command->bindParam("sessionID", $sessionID);
        $result = $command->execute();
        if ($result > 0)
        {
            try
            {
                $pdo->commit();
                return array('TransCode' => 1, 'TransMsg' => 'Session successfully added');
            }
            catch(CDbException $e)
            {
                $pdo->rollback();
                return array('TransCode' => 0, 'TransMsg' => 'Error: '.$e->getMessage());
            }
        }
        else
        {
            $pdo->rollback();
            return array('TransCode' => 0, 'TransMsg' => 'Adding of session failed');
        }
    }
    /**
     * Check if session exist
     * @param int $partnerPID Partner user ID
     * @param string $sessionid Session ID of the user
     * @return int AID
     * @author Mark Kenneth Esguerra
     * @date October 1, 2013
     */
    public function checkIfSessionExist($partnerPID, $sessionid)
    {
        $connection = Yii::app()->db;
        
        $query = "SELECT COUNT(PartnerPID) as Count FROM partnersessions
                  WHERE SessionID = :sessionID AND PartnerPID = :partnerPID
                 ";
        $command = $connection->createCommand($query);
        $command->bindParam(":sessionID", $sessionid);
        $command->bindParam(":partnerPID", $partnerPID);
        $result = $command->queryAll();
        
        foreach ($result as $row) {
            $this->partnerpid = $row['Count'];
        }
        return $this->partnerpid;
    }
    /**
     * Delete session once the user logged out
     * @param int $partnerpid PartnerPID of the partner
     * @return array Transaction results
     * @author Mark Kenneth Esguerra
     * @date October 2, 2013
     */
    public function deleteSession($partnerpid)
    {
        $connection = Yii::app()->db;
        
        $pdo = $connection->beginTransaction();
        
        $query = "DELETE FROM partnersessions WHERE PartnerPID = :partnerpid";
        $command = $connection->createCommand($query);
        $command->bindParam(":partnerpid", $partnerpid);
        $result = $command->execute();
        
        if ($result > 0)
        {
            try
            {
                $pdo->commit();
                return array('TransCode' => 1);
            }
            catch(CDbException $e)
            {
                $pdo->rollback();
                return array('TransCode' => 0, 'TransMsg' => 'Error: '.$e.getMessage());
            }
        }
        else
        {
            $pdo->rollback();
            return array('TransCode' => 0, 'TransMsg' => 'An error occured while deleting the session');
        }
    }
}
?>
