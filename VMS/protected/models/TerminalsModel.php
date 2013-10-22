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

        $sql = "SELECT TerminalID, TerminalName FROM terminals
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

        return $result;
    }

}

?>
