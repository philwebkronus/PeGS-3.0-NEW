<?php

class LpErrorLogsModel extends CFormModel {

    public $connection;

    public function __construct() {
        $this->connection = Yii::app()->db;
    }

    public function insertLPlogs($Node, $TerminalID, $MID, $CardNumber, $ErrorMsg, $Request, $Response) {
        $startTrans = $this->connection->beginTransaction();
        try {
            $sql = "INSERT INTO lperrorlogs (
                        Node,
                        TerminalID, 
                        MID, 
                        CardNumber, 
                        ErrorMessage,
                        TransactionDateTime,
                        Request,
                        Response
                    ) 
                    VALUES (
                        :Node, 
                        :TerminalID,
                        :MID, 
                        :CardNumber, 
                        :ErrorMessage, 
                        NOW(6),
                        :Request, 
                        :Response
                    )";

            $param = array(
                ':Node' => $Node,
                ':TerminalID' => $TerminalID,
                ':MID' => $MID,
                ':CardNumber' => $CardNumber,
                ':ErrorMessage' => $ErrorMsg,
                ':Request' => $Request,
                ':Response' => $Response,
            );

            $command = $this->connection->createCommand($sql);
            $command->bindValues($param);
            $command->execute();

            $LpErrorLogsID = $this->connection->getLastInsertID();
            try {
                $startTrans->commit();
                return $LpErrorLogsID;
            } catch (PDOException $e) {
                $startTrans->rollback();
                Utilities::log($e->getMessage());
                return false;
            }
        } catch (Exception $e) {
            $startTrans->rollback();
            Utilities::log($e->getMessage());
            return false;
        }
    }

}

?>
