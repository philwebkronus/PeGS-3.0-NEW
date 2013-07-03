<?php

/*
 * @author : owliber
 * @date : 2013-04-22
 */

class MemberCards extends BaseEntity
{
    public function MemberCards()
    {
        $this->TableName = "loyaltydb.membercards";
        $this->ConnString = 'loyalty';
        $this->Identity = "MemberCardID";
        $this->DatabaseType = DatabaseTypes::PDO;
    }
    
    public function getMemberCardInfo( $MID )
    {
        $query = "SELECT * 
                  FROM membercards 
                  WHERE MID = $MID ";
       
        $result = parent::RunQuery($query);
        
        return $result;
    }
        
    function getMemberCardInfoByCard($cardnumber)
    {

        $query = "SELECT m.*, c.CardTypeID 
                  FROM membercards m
                    INNER JOIN cards c ON m.CardID = c.CardID AND m.CardNumber = c.CardNumber
                  WHERE m.CardNumber='$cardnumber'";
        
        
        $result = parent::RunQuery($query);
        return $result;
    }
    
    /*
     * Description: Get Card Status
     * @author: gvjagolino
     * result: object array
     * DateCreated: 2013-07-01
     */
    function getStatusByCard($cardnumber)
    {

        $query = "SELECT Status FROM membercards WHERE CardNumber='$cardnumber'";
        
        $result = parent::RunQuery($query);
        return $result;
    }
    
    public function getMemberPoints( $cardnumber )
    {
        $row = $this->getMIDByCard( $cardnumber );
        
        $MID = $row[0]['MID'];
        
        $query = "SELECT
                    COALESCE(SUM(CurrentPoints), 0) AS `CurrentPoints`,
                    COALESCE(SUM(LifetimePoints), 0) AS `LifetimePoints`,
                    COALESCE(SUM(RedeemedPoints), 0) AS `RedeemedPoints`,
                    COALESCE(SUM(BonusPoints), 0) AS `BonusPoints`,
                    COALESCE(SUM(RedeemedBonusPoints), 0) AS `RedeemedBonusPoints`,
                    MID
                  FROM loyaltydb.membercards
                  WHERE MID = $MID
                  AND `Status` IN (1,5,7, 8)
                  GROUP BY MID;";
                
        $result = parent::RunQuery($query);
        return $result;
    }
    
    public function getCardNumberUsingMID( $MID )
    {
        $query = "SELECT mc.CardNumber
                            FROM membercards mc
                            INNER JOIN cards c ON c.CardID = mc.CardID
                            WHERE mc.MID = $MID AND mc.Status IN(1,5)";
        
        $result = parent::RunQuery($query);
        return $result[0]['CardNumber'];
    }
    
    public function getMIDByCard( $cardnumber )
    {
        $query = "SELECT mc.MID
                  FROM membercards mc
                    INNER JOIN cards c ON mc.CardID = c.CardID
                  WHERE c.CardNumber = '$cardnumber'";
        
        $result = parent::RunQuery($query);
        
        return $result;
    }
    
    public function getActiveMemberCardInfo( $MID )
    {
        $query = "SELECT m.*,
                    CASE c.CardTypeID
                        WHEN 1 THEN 'Gold'
                        WHEN 2 THEN 'Green'
                    END AS CardType
            FROM membercards m
                INNER JOIN cards c ON c.CardID = m.CardID AND m.CardNumber = c.CardNumber
            WHERE m.MID = $MID";
       
        $result = parent::RunQuery($query);
        
        return $result;
    }
    
    /*
     * Description: Get MemberCard Info using MID with a status limit only to active, active temporary and banned cards.
     * @author: aqdepliyan
     * DateCreated: 2013-06-17 05:38:40PM
     */
    public function getMemberCardInfoByMID( $MID )
    {
        $query = "SELECT m.Status, mc.MemberCardID, mc.CardNumber
                            FROM membership.members as m
                            INNER JOIN ".$this->TableName." as mc ON mc.MID = m.MID
                            WHERE mc.Status IN(1,5,9) AND m.Status IN(1,5) AND m.MID =".$MID;
       
        $result = parent::RunQuery($query);
        return $result;
    }
    
    /*
     * Description: Get MemberCard Info using CardNumber with a status limit only to active, active temporary and banned cards.
     * @author: aqdepliyan
     * DateCreated: 2013-06-17 06:02:35PM
     */
    public function getMemberCardInfoByCardNumber( $cardnumber )
    {
        $query = "SELECT MemberCardID, MID
                            FROM ".$this->TableName."
                            WHERE CardNumber ='".$cardnumber."'";
       
        $result = parent::RunQuery($query);
        return $result;
    }
    
    /*
     * Description: Get MemberCard Info with a status limit only to banned cards.
     * @author: aqdepliyan
     * DateCreated: 2013-06-19 06:02:35PM
     */
    public function getAllBannedMemberCardInfo( )
    {
        $query = "SELECT MemberCardID, MID, CardNumber
                            FROM ".$this->TableName."
                            WHERE Status = 9";
       
        $result = parent::RunQuery($query);
        return $result;
    }
    
    public function updateMemberCardStatusUsingCardNumber($status,$CardNumber){
        $query = "UPDATE ".$this->TableName." SET Status = ".$status." WHERE CardNumber = '".$CardNumber."'";
        parent::ExecuteQuery($query);
        if($this->HasError){
            App::SetErrorMessage($this->getError());
            return false;
        } else {
            $query = "UPDATE loyaltydb.cards SET Status = ".$status." WHERE CardNumber = '".$CardNumber."'";
            parent::ExecuteQuery($query);
            if($this->HasError){
                App::SetErrorMessage($this->getError());
                return false;
            }
        }
    }
    
    public function processMemberCard($arrMemberCards, $arrTempMemberCards)
    {
        $this->StartTransaction();
        try
        {
            $this->Insert($arrMemberCards);
            if(!App::HasError())
            {
                $this->UpdateByArray($arrTempMemberCards);
                if(!App::HasError())
                {
                    $this->CommitTransaction();
                }
                else
                {
                    $this->RollBackTransaction();
                }
            }
            else
            {
                $this->RollBackTransaction();
            }
        }
        catch(Exception $e)
        {
            $this->RollBackTransaction();
            App::SetErrorMessage($e->getMessage());
        }
    }
    
    public function updateMemberCardName( $MID, $name )
    {
        $this->StartTransaction();
        
        $query = "UPDATE loyaltydb.membercards SET MemberCardName = '$name' WHERE MID = $MID";
        
        $this->ExecuteQuery($query);
        
        if(!App::HasError())
            $this->CommitTransaction();
        else
            $this->RollBackTransaction ();
    }
    
    public function Redeem($MID, $CardNumber, $redeemTotalPoints)
    {
        $query = "Update $this->TableName set RedeemedPoints = RedeemedPoints + $redeemTotalPoints, 
                CurrentPoints = CurrentPoints - $redeemTotalPoints WHERE MID = $MID and CardNumber = '$CardNumber'
                and CurrentPoints >= $redeemTotalPoints and Status = 1;";
        $this->LastQuery = $query;
        $retval = parent::ExecuteQuery($query);
        if ($this->AffectedRows <= 0)
        {
            App::SetErrorMessage("Failed to redeem: Card may have insufficient points.");
        }
        return $retval;
    }
}
?>
