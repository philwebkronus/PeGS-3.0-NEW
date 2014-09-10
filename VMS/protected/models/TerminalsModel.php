<?php

/**
 * Description of TerminalsModel
 *
 * @author jshernandez
 */
class TerminalsModel extends CFormModel {

    /**
     * @author JunJun S. Hernandez
     * @datecreated 10/21/13
     * @param int $siteid
     * @return object
     */
    public function getTerminalNamesUsingSiteID($siteid) {

        $connection = Yii::app()->db2;

        $sql = "SELECT TerminalID, TerminalName, TerminalCode FROM terminals
                WHERE SiteID = :siteid";
        $command = $connection->createCommand($sql);
        $command->bindValue(":siteid", $siteid);
        $result = $command->queryAll();

        return $result;
    }

    /**
     * @author JunJun S. Hernandez
     * @datecreated 10/21/13
     * @param int $terminalid
     * @return object
     */
    public function getTerminalNamesUsingTerminalID($terminalid) {

        $connection = Yii::app()->db2;

        $sql = "SELECT TerminalID, TerminalName FROM terminals
                WHERE TerminalID = :terminalid";
        $command = $connection->createCommand($sql);
        $command->bindValue(":terminalid", $terminalid);
        $result = $command->queryAll();

        return $result;
    }

    /**
     * @author JunJun S. Hernandez
     * @datecreated 10/21/13
     * @param str $terminalid
     * @return object
     */
    public function getSiteIDfromterminals($terminalid) {

        $connection = Yii::app()->db2;

        $sql = "SELECT SiteID FROM terminals
                WHERE TerminalID = :terminalid";
        $command = $connection->createCommand($sql);
        $command->bindValue(":terminalid", $terminalid);
        $result = $command->queryAll();

        if(count($result) > 0){
            return $result[0]['SiteID'];
        } else {
            return 0;
        }
    }
    
    /**
     * @author JunJun S. Hernandez
     * @datecreated 10/21/13
     * @param str $terminalid
     * @return object
     */
    public function getTerminalIDfromterminals($terminalCode) {

        $connection = Yii::app()->db2;

        $sql = "SELECT TerminalID FROM terminals
                WHERE TerminalCode = :terminal_code AND TerminalType = 1";
        $command = $connection->createCommand($sql);
        $command->bindValue(":terminal_code", $terminalCode);
        $result = $command->queryAll();
        if(count($result) > 0){
            return $result[0]['TerminalID'];
        } else {
            return 0;
        }
    }
    
    
    public function getTerminalSiteID($terminal_code) {
        $connection = Yii::app()->db2;
        $sql = 'SELECT TerminalID, SiteID FROM terminals WHERE TerminalCode = :terminal_code';
        
        $command = $connection->createCommand($sql);
        $command->bindValue(":terminal_code", $terminal_code);
        $result = $command->queryRow();

        if(empty($result))
            return false;
        return $result;
    }
    
    public function getTerminalIDByCodeEGMType($terminalCode) {
        $connection = Yii::app()->db2;
        $terminalCodeVip = $terminalCode."VIP";
        $sql = 'SELECT TerminalID, Status FROM terminals WHERE TerminalCode IN (:terminal_code, :terminal_code_vip) AND  TerminalType = 1';
        $param = array(":terminal_code"=>$terminalCode,":terminal_code_vip"=>$terminalCodeVip);
        $command = $connection->createCommand($sql);
        return $command->queryAll(true, $param);
    }

}

?>
