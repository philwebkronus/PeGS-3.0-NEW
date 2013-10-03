<?php

class MemberPointsTransferLog extends BaseEntity {
    
    public function MemberPointsTransferLog() {
        $this->TableName = "loyaltydb.memberpointstranferlog";
        $this->ConnString = 'loyalty';
        $this->Identity = "MemberPointsTransferLogID";
        $this->DatabaseType = DatabaseTypes::PDO;
    }
    
    
    public function getFromCardID($membercardid){
        $query = "SELECT LifeTimePoints, CurrentPoints, RedeemedPoints, DateTransferred, FromMemberCardID 
            FROM memberpointstranferlog WHERE ToMemberCardID = '$membercardid'";
        return parent::RunQuery($query);
    }
    
    
    public function getToCardID($membercardid){
        $query = "SELECT LifeTimePoints, CurrentPoints, RedeemedPoints, DateTransferred, ToMemberCardID 
            FROM memberpointstranferlog WHERE FromMemberCardID = '$membercardid'";
        return parent::RunQuery($query);
    }
    
    public function logPointsTransfer($fromMemberCardID, $toMemberCardID, $lifetimePoints, $currentPoints, $redeemedPoints, $dateTransferred, $transferredByAID)
    {
        $this->StartTransaction();
        try
        {
        $query = "INSERT INTO memberpointstranferlog (FromMemberCardID, ToMemberCardID,
                              LifetimePoints, CurrentPoints, RedeemedPoints, DateTransferred, TransferredByAID)
                         VALUES('$fromMemberCardID', '$toMemberCardID', '$lifetimePoints', '$currentPoints', '$redeemedPoints', '$dateTransferred', '$transferredByAID')";
        $this->ExecuteQuery($query);
        if(!App::HasError()) {
                $this->CommitTransaction();
        } else {
                $this->RollBackTransaction(); 
        }
        }
        catch(Exception $e)
        {
            $this->RollBackTransaction();
            App::SetErrorMessage($e->getMessage());
        }
    }
    
}
?>
