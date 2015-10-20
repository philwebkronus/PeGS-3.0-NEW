<?php
/**
 * EGMSession Model
 * @author Mark Kenneth Esguerra
 * @date June 19, 2015
 */
class EGMSessionModel extends Controller {
    
    private $connection;
    
    public function __construct() {
        $this->connection = Yii::app()->db;
    }
    /**
     * Check EGM Session using MID and Terminal
     * @param type $MID ID of the Member
     * @param type $terminal ID of the terminal
     * @return int Count
     * @author Mark Kenneth Esguerra
     * @date June 19, 2015
     */
    public function checkEGMSession($MID, $terminal) {

        $sql = "SELECT COUNT(EGMSessionID) as Count 
                FROM egmsessions 
                WHERE MID = :mid AND TerminalID = :terminal";
        $command = $this->connection->createCommand($sql);
        $command->bindValue(":mid", $MID);
        $command->bindValue(":terminal", $terminal);
        $result = $command->queryRow();

        return (int)$result['Count'];
    } 
    /**
     * Get EGM session ID by MID and Terminal
     * @param type $MID ID of the Member
     * @param type $terminal ID of the terminal
     * @return int 0 - null 
     * @author Mark Kenneth Esguerra
     * @date June 19, 2015
     */
    public function getEGMSessionID ($MID, $terminal) {

        $sql = "SELECT EGMSessionID  
                FROM egmsessions 
                WHERE MID = :mid AND TerminalID = :terminal";
        $command = $this->connection->createCommand($sql);
        $command->bindValue(":mid", $MID);
        $command->bindValue(":terminal", $terminal);
        $result = $command->queryRow();

        if (!empty($result)) 
            return $result['EGMSessionID']; 
        else
            return 0;
    } 
    /**
     * Remove EGM Session
     * @param type $EGMSessionID EGM Session ID
     * @return array Transaction result
     * @author Mark Kenneth Esguerra
     * @date June 19, 2015
     */
    public function removeEGMSession($EGMSessionID) {
        $pdo = $this->connection->beginTransaction();
        
        try {
            $sql = "DELETE FROM egmsessions 
                    WHERE EGMSessionID = :egmsessionID";
            $command = $this->connection->createCommand($sql);
            $command->bindValue(":egmsessionID", $EGMSessionID);
            $result = $command->execute();
            if ($result > 0) {
                try {
                    $pdo->commit();
                    return array('TransCode' => 0, 
                                 'TransMsg' => 'Transaction Successful');
                }
                catch (CDbException $e) {
                    $pdo->rollback();
                    return array('TransCode' => 1, 
                                 'TransMsg' => 'Transaction Failed');
                }
            }
            else {
                $pdo->rollback();
                return array('TransCode' => 1, 
                             'TransMsg' => 'Transaction Failed');
            }
        }
        catch (CDbException $e) {
            $pdo->rollback();
            return array('TransCode' => 1, 
                         'TransMsg' => 'Transaction Failed');
        }
    }
    
    /**
     * Check if card has an existing egm session
     * @param type $mid
     * @author Ralph Sison
     * @date 06-24-2015
     */
    public function hasEGMSession($mid) {
        $sql = "SELECT COUNT(EGMSessionID) as Count 
                FROM egmsessions 
                WHERE MID = :mid";
        $command = $this->connection->createCommand($sql);
        $command->bindValue(":mid", $mid);
        $result = $command->queryRow();

        return $result;
    }
}
?>
