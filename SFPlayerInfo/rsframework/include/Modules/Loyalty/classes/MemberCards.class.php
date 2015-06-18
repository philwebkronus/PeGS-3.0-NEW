<?php

/*
 * @author : owliber
 * @date : 2013-04-22
 * @updated by Joene Floresca
 * @date : 2014-10-01
 */

class MemberCards extends BaseEntity
{

    public function MemberCards()
    {
        $this->TableName = "membercards";
        $this->ConnString = 'loyalty';
        $this->Identity = "MemberCardID";
        $this->DatabaseType = DatabaseTypes::PDO;
    }

    //@authod fdlsison
    //@date 04/30/2015
    public function getPOCDetails($MID)
    {
        $query = "SELECT mc.CardID, mc.CardNumber, mc.CurrentPoints, mc.LifetimePoints, mc.RedeemedPoints, mi.FirstName, mi.MiddleName, mi.LastName, mi.Birthdate, mi.Address1, mi.DateVerified, mi.Email,
                   mi.IdentificationID, mi.IdentificationNumber, mi.MobileNumber, mc.DateCreated, mi.MID
            FROM $this->TableName mc
                   INNER JOIN membership.memberinfo mi ON mc. MID = mi.MID
            WHERE mi.SFID = '$MID' AND mc.Status = 1;";

        $result = parent::RunQuery($query);
        return $result;
    }

    public function getPOCDetails2($MID)
    {
        $query = "SELECT mc.CardID, mc.CardNumber, mc.CurrentPoints, mc.LifetimePoints, mc.RedeemedPoints, mi.FirstName, mi.MiddleName, mi.LastName, mi.Birthdate, mi.Address1, mi.DateVerified, mi.Email,
                   mi.IdentificationID, mi.IdentificationNumber, mi.MobileNumber, mc.DateCreated
            FROM $this->TableName mc
                   INNER JOIN membership_temp.memberinfo mi ON mc. MID = mi.MID
            WHERE mi.SFID = '$MID' AND mc.Status = 1;";

        $result = parent::RunQuery($query);
        return $result;
    }

}

?>
