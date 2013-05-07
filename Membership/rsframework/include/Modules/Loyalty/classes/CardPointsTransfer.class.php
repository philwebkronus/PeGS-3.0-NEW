<?php

/* * ***************** 
 * Author: Renz Tiratira
 * Date Created: 2013-04-22
 * ***************** */

class CardPointsTransfer extends BaseEntity
{
    function CardPointsTransfer()
    {
        $this->TableName = "cardpointstransfer";
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
}
?>
