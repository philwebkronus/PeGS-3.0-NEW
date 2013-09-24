<?php

/**
 * VerificationLogsForm class.
 */
class VerificationLogsModel extends CFormModel
{
    
    public function logToVerificationLogs($rewardid, $partnerid, $rewarditemid,
            $serialcode, $securitycode, $source, $date, $aid){
        
        $connection = Yii::app()->db;
         
        $sql="INSERT INTO verificationlogs (RewardID, PartnerID, RewardItemID, SerialCode,
            SecurityCode, Source, DateCreated, CreatedByAID) VALUES (:rewardid, :partnerid, :rewarditemid,
            :serialcode, :securitycode, :source, :date, :aid);";
        $command = $connection->createCommand($sql);
        $command->bindValue(':rewardid', $rewardid);
        $command->bindValue(':partnerid', $partnerid);
        $command->bindValue(':rewarditemid', $rewarditemid);
        $command->bindValue(':serialcode', $serialcode);
        $command->bindValue(':securitycode', $securitycode);
        $command->bindValue(':source', $source);
        $command->bindValue(':date', $date);
        $command->bindValue(':aid', $aid);
        $result = $command->execute();
       
        return $result;
    }


    
    
}
