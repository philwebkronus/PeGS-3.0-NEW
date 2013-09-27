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
    
    
    
}
?>
