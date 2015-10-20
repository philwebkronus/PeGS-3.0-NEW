<?php

/*
 * @author : owliber
 * @date : 2013-05-31
 * 
 */

class Players extends BaseEntity
{
    public function Players()
    {
        $this->ConnString = "membership";
        $this->TableName = "members";
    }
    
    public function getBannedPlayers()
    {
        $query = "SELECT
                    m.UserName,
                    m1.FirstName,
                    m1.LastName,
                    m2.CardNumber,
                    m2.SiteID
                  FROM membership.members m
                    INNER JOIN membership.memberinfo m1
                      ON m.MID = m1.MID                      
                    LEFT JOIN loyaltydb.membercards m2 ON m.MID = m2.MID
                  WHERE m.Status = 5
                  ORDER BY m1.LastName;";
        
        return parent::RunQuery($query);
    }
    
    public function getBannedPlayersByFilter($filter)
    {
        
        $query = "SELECT
                m.UserName,
                m1.FirstName,
                m1.LastName,
                m2.CardNumber,
                m2.SiteID
              FROM membership.members m
                INNER JOIN membership.memberinfo m1
                  ON m.MID = m1.MID
                LEFT JOIN loyaltydb.membercards m2 ON m.MID = m2.MID
              WHERE m.Status = 5
                AND (m.UserName LIKE '%$filter%' OR m1.FirstName LIKE '%$filter%'
                OR m1.LastName LIKE '%$filter%' OR m2.CardNumber LIKE '%$filter%')
              GROUP BY m.MID
                ORDER BY m1.LastName
               ";
        
        return parent::RunQuery($query);
    }
}
?>
