<?php

/*
 * @author : owliber
 * @date : 2013-04-22
 */

class OldCards extends BaseEntity
{
    public function OldCards()
    {
        $this->TableName = "oldcards";
        $this->ConnString = "loyalty";
        $this->Identity = "OldCardID";
        $this->DatabaseType = DatabaseTypes::PDO;
    }        
        
    public function getOldCardInfo( $cardnumber )
    {
        $query = "SELECT * FROM oldcards WHERE CardNumber = '$cardnumber'";
        
        $result = parent::RunQuery($query);
        
        return $result;
    }
    
     function getemail($LoyatyCardNumber){
         $query = "select Email from oldcards where CardNumber = '$LoyatyCardNumber' ";
         return parent::RunQuery($query);
    }
    
    
    //Used to get get OLDCardsDetails, CardName and Points. //DO NOT DELETE!!!// -ish.
    // twice used//
        function getOldCardDetails ($LoyatyCardNumber){
       $query = " select a.OldCardID as OldCardID, a.CardTypeID as CardTypeID,a.LifetimePoints as LifetimePoints, (a.LifetimePoints - a.RedeemedPoints) as CurrentPoints, 
                    a.RedeemedPoints as RedeemedPoints,
                    b.CardTypeName as CardName from oldcards a 
                    inner join ref_cardtypes b 
                    on a.CardTypeID = b.CardTypeID
                    where a.CardNumber = '$LoyatyCardNumber'";
       return parent::RunQuery($query);
       
    }
   
}
?>
