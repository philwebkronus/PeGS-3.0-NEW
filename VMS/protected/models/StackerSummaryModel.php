<?php


class StackerSummaryModel extends CFormModel {
   
    
    public function isStackerSummaryIdExists($stackerBatchID){
        
        $connection = Yii::app()->db4;
        
        $sql = "SELECT COUNT(StackerSummaryID) ctrStackerSummaryID FROM stackersummary WHERE StackerSummaryID = :stacker_batch_id";        
        $command = $connection->createCommand($sql);
        $command->bindValue(":stacker_batch_id", $stackerBatchID);
        $result = $command->queryRow();

        return $result['ctrStackerSummaryID'];
    }
    
}