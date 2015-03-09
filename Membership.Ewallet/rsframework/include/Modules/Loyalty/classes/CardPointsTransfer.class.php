<?php

/* * ***************** 
 * Author: Renz Tiratira
 * Date Created: 2013-04-22
 * ***************** */

class CardPointsTransfer extends BaseEntity
{
    function CardPointsTransfer()
    {
        $this->TableName = "loyaltydb.cardpointstransfer";
        $this->ConnString = "loyalty";
        $this->Identity = "CardPointsTransferID";
        $this->DatabaseType = DatabaseTypes::PDO;
    }
    
    /*function UpdateCardPoints($membercardid, $lifetimepoints, $currentpoints, $redeemedpoints)
    {
        $query = "update membercards set LifetimePoints=".$lifetimepoints.", Currentpoints=".$currentpoints.", RedeemedPoints=".$redeemedpoints." where MemberCardID=".$membercardid;
        
        $result = parent::RunQuery($query);
        return $result;
    }*/
    
    
    function getTransferrredUBCard($fromoldcardid)
    {
        $query = "SELECT MID, ToMemberCardID, LifeTimePoints, CurrentPoints, 
            RedeemedPoints, BonusPoints, DateTransferred 
            FROM cardpointstransfer
            WHERE FromOldCardID = '$fromoldcardid' ";
        
        return parent::RunQuery($query);
    }
    
    
    function getOldUBCard($mid)
    {
        $query = "SELECT FromOldCardID, MID, ToMemberCardID, LifeTimePoints, CurrentPoints, 
            RedeemedPoints, BonusPoints, DateTransferred 
            FROM cardpointstransfer
            WHERE MID = '$mid' ";
        
        return parent::RunQuery($query);
    }
}
?>
