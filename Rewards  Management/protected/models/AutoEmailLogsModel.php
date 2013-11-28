<?php


class AutoEmailLogsModel extends CFormModel
{
    public function InsertAutoEmailLogs($AEmailID, $SentToAID, $SentToCCAID, $SentToBCCAID, $Message, $SentByAID){
        $connection = Yii::app()->db;
        
        if($SentToAID == "")
            $SentToAID = null;
        if($SentToBCCAID == "")
            $SentToBCCAID = null;
        if($SentToCCAID == "")
            $SentToCCAID = null;

        $insertautoemaillogs = "INSERT INTO  autoemaillogs(AEmailID, SentToAID, SentToCCAID, SentToBCCAID, Message, DateSent, SentByAID)
                                                VALUES(:aemailid, :senttoaid, :senttoccaid, :senttobccaid, :message, now_usec(), :sentbyaid)";
        $command = $connection->createCommand($insertautoemaillogs);
        $command->bindParam(":aemailid", $AEmailID,PDO::PARAM_INT);
        $command->bindParam(":senttoaid", $SentToAID,PDO::PARAM_INT);
        $command->bindParam(":senttoccaid", $SentToCCAID,PDO::PARAM_INT);
        $command->bindParam(":senttobccaid", $SentToBCCAID,PDO::PARAM_INT);
        $command->bindParam(":message", $Message);
        $command->bindParam(":sentbyaid", $SentByAID,PDO::PARAM_INT);

        try {
            $command->execute();
            return array('TransMsg'=>'Auto Email successfully logged.','TransCode'=>0);
        } catch (CDbException $e) {
            return array('TransMsg'=>'Error: '. $e->getMessage(),'TransCode'=>2);
        }
    }
}
?>
