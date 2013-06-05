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

        $query = "SELECT * 
                  FROM membercards 
                  WHERE CardNumber='$cardnumber'";
        
        
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
        $query = "SELECT * 
            FROM membercards 
            WHERE MID = $MID and Status IN (1,5)";
       
        $result = parent::RunQuery($query);
        
        return $result;
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
    
}
?>
