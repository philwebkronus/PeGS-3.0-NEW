<?php

/*
 * @author : owliber
 * @date : 2013-04-22
 * @updated by Joene Floresca
 * @date : 2014-10-01
 */

class MemberCards extends BaseEntity {

    public function MemberCards() {
        $this->TableName = "membercards";
        $this->ConnString = 'loyalty';
        $this->Identity = "MemberCardID";
        $this->DatabaseType = DatabaseTypes::PDO;
    }

    public function getMemberCardInfo($MID) {
        $query = "SELECT * 
                  FROM membercards 
                  WHERE MID = $MID ";

        $result = parent::RunQuery($query);

        return $result;
    }

    function getMemberCardInfoByCard($cardnumber) {

        $query = "SELECT m.*, c.CardTypeID , mb.IsVIP
                  FROM membercards m
                    INNER JOIN membership.members mb ON mb.MID = m.MID
                    INNER JOIN cards c ON m.CardID = c.CardID AND m.CardNumber = c.CardNumber
                  WHERE m.CardNumber='$cardnumber'";


        $result = parent::RunQuery($query);
        return $result;
    }

    /*
     * Description: Get the number of both Active and Banned Account Status
     * @author: Junjun S. Hernandez
     * DateCreated: June 27, 2013 01:07:35PM
     */

    public function getAllMemberCards() {

        $query = "SELECT MemberCardID, MID, CardNumber FROM membercards ORDER BY CardNumber ASC";

        return parent::RunQuery($query);
    }

    /*
     * Description: Get Card Status
     * @author: gvjagolino
     * result: object array
     * DateCreated: 2013-07-01
     */

    function getStatusByCard($cardnumber) {

        $query = "SELECT Status FROM membercards WHERE CardNumber='$cardnumber'";

        $result = parent::RunQuery($query);
        return $result;
    }
    
    public function getMemberPoints($cardnumber) {
        $row = $this->getMIDByCard($cardnumber);

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
                  AND `Status` IN (1,5)
                  GROUP BY MID;";

        $result = parent::RunQuery($query);
        return $result;
    }

    public function getCardNumberUsingMID($MID) {
        $query = "SELECT mc.CardNumber
                            FROM membercards mc
                            INNER JOIN cards c ON c.CardID = mc.CardID
                            WHERE mc.MID = $MID AND mc.Status IN(1,5)";

        $result = parent::RunQuery($query);
        return $result[0]['CardNumber'];
    }

    public function getOldUBCardNumberUsingMID($MID) {
        $query = "SELECT mc.CardNumber
                            FROM membercards mc
                            INNER JOIN cards c ON c.CardID = mc.CardID
                            WHERE mc.MID = $MID ORDER BY mc.MemberCardID DESC";

        $result = parent::RunQuery($query);
        if (empty($result)) {
            return 0;
        } else {
            return $result[0]['CardNumber'];
        }
    }

    public function getMIDByCard($cardnumber) {
        $query = "SELECT mc.MID, mc.Status
                  FROM membercards mc
                  WHERE mc.CardNumber = '$cardnumber'";

        $result = parent::RunQuery($query);

        return $result;
    }
    
    /**
     * @Author: Joene Floresca
     * @Desc: Get Status of Card Number from MemberCards
     * @param type $cardnumber
     * @return type
     */
    public function getMIDByCardMemberCards($cardnumber) {
        $query = "SELECT mc.MID, mc.Status
                  FROM membercards mc
                  WHERE mc.CardNumber = '$cardnumber';";

        $result = parent::RunQuery($query);

        return $result;
    }

    public function getPointsByCard($cardnumber) {
        $query = "SELECT mc.LifeTimePoints, mc.CurrentPoints, mc.RedeemedPoints, mc.BonusPoints
                  FROM membercards mc
                    INNER JOIN cards c ON mc.CardID = c.CardID
                  WHERE c.CardNumber = '$cardnumber'";

        $result = parent::RunQuery($query);

        return $result[0];
    }

    public function getMemberCardInfoRedemption($MID) {
        $query = "SELECT m.*,
                    CASE c.CardTypeID
                        WHEN 1 THEN 'Gold'
                        WHEN 2 THEN 'Green'
                    END AS CardType, c.CardTypeID
            FROM membercards m
                INNER JOIN cards c ON c.CardID = m.CardID AND m.CardNumber = c.CardNumber
            WHERE m.MID = $MID";

        $result = parent::RunQuery($query);

        return $result;
    }

    public function getActiveMemberCardInfo($MID) {
        $query = "SELECT m.*, mb.IsVIP,
                    CASE c.CardTypeID
                        WHEN 1 THEN 'Gold'
                        WHEN 2 THEN 'Green'
                    END AS CardType, c.CardTypeID
            FROM membercards m
                INNER JOIN membership.members mb ON mb.MID = m.MID
                INNER JOIN cards c ON c.CardID = m.CardID AND m.CardNumber = c.CardNumber
            WHERE m.MID = $MID AND m.Status IN(1,5)";

        $result = parent::RunQuery($query);

        return $result;
    }

    /**
     * @Description: Get MemberCard Info using MID with a status limit only to active, active temporary and banned cards.
     * @author: aqdepliyan
     * @DateCreated: 2013-06-17 05:38:40PM
     */
    public function getMemberCardInfoByMID($MID) {
        $query = "SELECT m.Status, mc.MemberCardID, mc.CardNumber
                            FROM membership.members as m
                            INNER JOIN " . $this->TableName . " as mc ON mc.MID = m.MID
                            WHERE mc.Status IN(1,5,9) AND m.Status IN(1,5) AND m.MID =" . $MID;

        $result = parent::RunQuery($query);
        return $result;
    }

    public function getMemberCardInfoByMIDAllStat($MID) {
        $query = "SELECT m.Status, mc.MemberCardID, mc.CardNumber, mc.Status AS MCStatus
                            FROM membership.members as m
                            INNER JOIN " . $this->TableName . " as mc ON mc.MID = m.MID
                            WHERE mc.Status IN(1,2,5) AND m.Status IN(1,6) AND m.MID =" . $MID;

        $result = parent::RunQuery($query);
        return $result;
    }

    /**
     * @Description: Get MemberCard Info using CardNumber.
     * @author: aqdepliyan
     * @DateCreated: 2013-06-17 06:02:35PM
     */
    public function getMemberCardInfoByCardNumber($cardnumber) {
        $query = "SELECT MemberCardID, MID, Status
                            FROM " . $this->TableName . "
                            WHERE CardNumber ='" . $cardnumber . "'";

        $result = parent::RunQuery($query);
        return $result;
    }

    /**
     * @Description: Get MemberCard Info with a status limit only to banned cards.
     * @author: aqdepliyan
     * @DateCreated: 2013-06-19 06:02:35PM
     */
    public function getAllBannedMemberCardInfo() {
        $query = "SELECT MemberCardID, MID, CardNumber
                            FROM " . $this->TableName . "
                            WHERE Status = 9";

        $result = parent::RunQuery($query);
        return $result;
    }

    public function updateMemberCardStatusUsingCardNumber($status, $CardNumber) {
        $query = "UPDATE " . $this->TableName . " SET Status = " . $status . " WHERE CardNumber = '" . $CardNumber . "'";
        parent::ExecuteQuery($query);
        if ($this->HasError) {
            App::SetErrorMessage($this->getError());
            return false;
        } else {
            $query = "UPDATE loyaltydb.cards SET Status = " . $status . " WHERE CardNumber = '" . $CardNumber . "'";
            parent::ExecuteQuery($query);
            if ($this->HasError) {
                App::SetErrorMessage($this->getError());
                return false;
            }
        }
    }

    public function processMemberCard($arrMemberCards, $arrTempMemberCards) {
        $this->StartTransaction();
        try {
            $this->Insert($arrMemberCards);
            if (!App::HasError()) {
                $this->UpdateByArray($arrTempMemberCards);
                if (!App::HasError()) {
                    $this->CommitTransaction();
                } else {
                    $this->RollBackTransaction();
                }
            } else {
                $this->RollBackTransaction();
            }
        } catch (Exception $e) {
            $this->RollBackTransaction();
            App::SetErrorMessage($e->getMessage());
        }
    }

    public function transferMemberCard($mid, $cardid, $siteid, $lifetimepoints, $currentpoints, $redeemedpoints, $newcardnumber, $oldubcardnumber, $status1, $status2, $aid, $dateupdated, $cardtypeid1) {

        $currentpoints = (int)$currentpoints;
        $lifetimepoints = (int)$lifetimepoints;

        $this->StartTransaction();
        try {
//            $query = "UPDATE membercards SET LifetimePoints = '$lifetimepoints',
//                CurrentPoints = '$currentpoints', RedeemedPoints = '$redeemedpoints', DateUpdated = '$dateupdated',
//                Status = '$status1', UpdatedByAID = '$aid'
//                WHERE CardNumber = '$newcardnumber'";
//
//            $this->ExecuteQuery($query);

//            if (!App::HasError()) {
                $query2 = "UPDATE membercards SET DateUpdated = '$dateupdated',
                    Status = '$status2', UpdatedByAID = '$aid', 
                    CurrentPoints = $currentpoints, LifetimePoints = $lifetimepoints  
                    WHERE CardNumber = '$oldubcardnumber'";

                $this->ExecuteQuery($query2);
                
                if (!App::HasError()) {
                    $query3 = "INSERT INTO membercards SET MID = '$mid', CardID = '$cardid', CardNumber = '$newcardnumber',
                                                           SiteID = '$siteid', LifetimePoints = '$lifetimepoints',
                                                           CurrentPoints = 0, RedeemedPoints = '$redeemedpoints',
                                                           DateCreated = '$dateupdated' , Status = '$status1'";

                    $this->ExecuteQuery($query3);

                    if (!App::HasError()) {
                        $query4 = "UPDATE cards SET CardTypeID = '$cardtypeid1' WHERE CardNumber = '$newcardnumber'";

                        $this->ExecuteQuery($query4);

                        if (!App::HasError()) {
                            $this->CommitTransaction();
                        } else {
                            $this->RollBackTransaction();
                        }
                    } else {
                        $this->RollBackTransaction();
                    }
                } else {
                    $this->RollBackTransaction();
                }
//            } else {
//                $this->RollBackTransaction();
//            }
        } catch (Exception $e) {
            $this->RollBackTransaction();
            App::SetErrorMessage($e->getMessage());
        }
    }

    public function updateMemberCardName($MID, $name) {
        $this->StartTransaction();

        $query = "UPDATE loyaltydb.membercards SET MemberCardName = '$name' WHERE MID = $MID";

        $this->ExecuteQuery($query);

        if (!App::HasError())
            $this->CommitTransaction();
        else
            $this->RollBackTransaction();
    }

    public function Redeem($MID, $CardNumber, $redeemTotalPoints) {
        $query = "Update $this->TableName set RedeemedPoints = RedeemedPoints + $redeemTotalPoints, 
                CurrentPoints = CurrentPoints - $redeemTotalPoints WHERE MID = $MID and CardNumber = '$CardNumber'
                and CurrentPoints >= $redeemTotalPoints and Status IN (1,5);";
        $this->LastQuery = $query;
        $retval = parent::ExecuteQuery($query);
        if ($this->AffectedRows <= 0) {
            App::SetErrorMessage("Failed to redeem: Card may have insufficient points.");
        }
        return $retval;
    }

    //For updating player points
    public function updatePlayerPoints($MID, $redeemTotalPoints) {
        $query = "UPDATE $this->TableName set RedeemedPoints = RedeemedPoints + $redeemTotalPoints, 
                CurrentPoints = CurrentPoints - $redeemTotalPoints WHERE MID = $MID AND  Status IN (1,5)";
        return parent::ExecuteQuery($query);
    }

    //For updating card points
//    public function UpdateCardPoints($MID, $redeemTotalPoints) {
//        $query = "UPDATE loyaltydb.membercards set RedeemedPoints = RedeemedPoints + $redeemTotalPoints, 
//                CurrentPoints = CurrentPoints - $redeemTotalPoints WHERE MID = $MID AND  Status IN (1,5)";
//        parent::ExecuteQuery($query);
//        return $this->AffectedRows;
//    }
    public function UpdateCardPoints($MID, $redeemTotalPoints) {
        $query = "UPDATE loyaltydb.membercards set RedeemedPoints = RedeemedPoints + $redeemTotalPoints
                  WHERE MID = $MID AND  Status IN (1,5)";
        parent::ExecuteQuery($query);
        return $this->AffectedRows;
    }

    /*
     * Description: MemberCardID based on given cardid
     * @author: Gerardo Jagolino Jr.
     * result: object array
     * DateCreated: 2013-07-17
     */

    public function getMemCardID($cardid) {
        $query = "SELECT MemberCardID FROM membercards WHERE CardID = $cardid";
        $result = parent::RunQuery($query);
        return $result;
    }

    /*
     * Description: MID based on given cardnumber
     * @author: Gerardo Jagolino Jr.
     * result: object array
     * DateCreated: 2013-07-17
     */

    public function getMID($cardnumber) {
        $query = "SELECT MID FROM membercards WHERE CardNumber = '$cardnumber'";
        $result = parent::RunQuery($query);
        return $result;
    }

    /**
     * @Description: For Fetching Card Points
     * @author: aqdepliyan
     * @DateCreated: 2013-09-17 04:04PM
     * @param string $cardnumber
     * @return int
     */
    public function getCurrentPointsByCardNumber($cardnumber) {
        $query = "SELECT CurrentPoints
                            FROM " . $this->TableName . "
                            WHERE CardNumber ='" . $cardnumber . "'";

        $result = parent::RunQuery($query);
        return $result;
    }

    /**
     * @Description: For Fetching Card Points
     * @author: aqdepliyan
     * @DateCreated: 2013-09-17 04:04PM
     * @param int $MID
     * @return int
     */
    public function getCurrentPointsByMID($MID) {
        $query = "SELECT CurrentPoints
                            FROM " . $this->TableName . "
                            WHERE MID=" . $MID . " AND Status = 1";
        $result = parent::RunQuery($query);
        return $result;
    }

    /*
     * Description: card number based on given membercardID
     * @author: Gerardo Jagolino Jr.
     * result: object array
     * DateCreated: 2013-07-17
     */

    public function getCardNumber($memcardid) {
        $query = "SELECT CardNumber FROM membercards WHERE MID = $memcardid AND Status != 8";
        $result = parent::RunQuery($query);
        return $result;
    }

    public function checkHasCardNumber($memcardid) {
        $query = "SELECT COUNT(CardNumber) as count FROM membercards WHERE MID = $memcardid AND Status != 8";
        $result = parent::RunQuery($query);
        return $result[0]['count'];
    }

    /*
     * Description: get Current Points and corresponding Status by CardNumber
     * @author: JunJun S. Hernandez
     * DateCreated: 2013-08-22
     */

    public function getCurrentPointsAndStatus($CardNumber) {
        $query = "SELECT CurrentPoints, Status FROM membercards WHERE CardNumber = '$CardNumber'";
        $result = parent::RunQuery($query);
        return $result;
    }

    /*
     * Description: card number based on given membercardID
     * @author: Gerardo Jagolino Jr.
     * result: object array
     * DateCreated: 2013-07-17
     */

    public function getCardDetails($CardNumber) {
        $query = "SELECT MemberCardID, MID, CardID, SiteID, 
            LifetimePoints, CurrentPoints, RedeemedPoints, DateCreated, 
            CreatedByAID, Status
        FROM membercards WHERE CardNumber = '$CardNumber'";
        $result = parent::RunQuery($query);
        return $result;
    }

    /*
     * Description: get Card Number using MID
     * @author: JunJun S. Hernandez
     * result: object array
     * DateCreated: 2013-08-12
     */

    public function getCardNumberByMID($MID) {
        $query = "SELECT CardNumber FROM membercards WHERE MID = '$MID'";
        return parent::RunQuery($query);
    }

    public function getMemCardIDByCardNumber($cardnumber) {
        $query = "SELECT MemberCardID FROM membercards WHERE CardNumber = '$cardnumber'";
        $result = parent::RunQuery($query);
        return $result;
    }

    public function getStatusByMID($MID) {

        $query = "SELECT Status FROM membercards WHERE MID = '$MID'";

        $result = parent::RunQuery($query);
        return $result;
    }

    /*
     * Description: card number based on given membercardID
     * @author: Gerardo Jagolino Jr.
     * result: object array
     * DateCreated: 2013-07-17
     */

    public function getTempcardDetails($cardnumber) {
        $query = "SELECT MemberCardID, MID, CardID, SiteID,
            LifeTimePoints, CurrentPoints, RedeemedPoints, BonusPoints, DateCreated, 
            CreatedByAID, Status
        FROM membercards WHERE CardNumber = '$cardnumber'";
        $result = parent::RunQuery($query);
        return $result;
    }

    /*
     * Description: card number based on given membercardID
     * @author: Gerardo Jagolino Jr.
     * result: object array
     * DateCreated: 2013-07-17
     */

    public function getMigratedCard($mid) {
        $query = "SELECT MemberCardID, MID, CardID, SiteID, CardNumber,
            LifeTimePoints, CurrentPoints, RedeemedPoints, BonusPoints, DateCreated, 
            CreatedByAID, Status
        FROM membercards WHERE MID = '$mid' AND Status = 1";
        $result = parent::RunQuery($query);
        return $result;
    }

    public function getMemberCardDetails($membercardid) {
        $query = "SELECT MemberCardID, MID, CardID, CardNumber, SiteID, 
            LifetimePoints, CurrentPoints, RedeemedPoints, DateCreated, 
            CreatedByAID, Status
        FROM membercards WHERE MemberCardID = '$membercardid'";
        $result = parent::RunQuery($query);
        return $result;
    }

    public function getUBCardDetails($cardnumber) {
        $query = "SELECT MemberCardID, MID, CardID, CardNumber, SiteID, 
            LifeTimePoints, CurrentPoints, RedeemedPoints, BonusPoints, DateCreated, 
            CreatedByAID, Status
        FROM membercards WHERE CardNumber = '$cardnumber'";
        $result = parent::RunQuery($query);
        return $result;
    }

    public function getAllCardDetails($mid) {
        $query = "SELECT MemberCardID, MID, CardID, CardNumber, SiteID, 
            LifeTimePoints, CurrentPoints, RedeemedPoints, BonusPoints, DateCreated, 
            CreatedByAID, Status
        FROM membercards WHERE MID = '$mid' AND Status = 1";
        $result = parent::RunQuery($query);
        return $result;
    }

    public function getCardDetailsActiveDeactivateBanned($mid) {
        $query = "SELECT MemberCardID, MID, CardID, CardNumber, SiteID, 
            LifeTimePoints, CurrentPoints, RedeemedPoints, BonusPoints, DateCreated, 
            CreatedByAID, Status
        FROM membercards WHERE MID = '$mid' AND Status IN (1,2,9)";
        $result = parent::RunQuery($query);
        return $result;
    }

    public function getInActiveCardDetails($mid) {
        $query = "SELECT MemberCardID, MID, CardID, CardNumber, SiteID, 
            LifeTimePoints, CurrentPoints, RedeemedPoints, BonusPoints, DateCreated, 
            CreatedByAID, Status
        FROM membercards WHERE MID = '$mid' AND Status != 1";
        $result = parent::RunQuery($query);
        return $result;
    }

    public function getCardDetailsFromStatus($mid, $status) {
        $query = "SELECT MemberCardID, MID, CardID, CardNumber, SiteID, 
            LifeTimePoints, CurrentPoints, RedeemedPoints, BonusPoints, DateCreated, 
            CreatedByAID, Status
        FROM membercards WHERE MID = '$mid' AND Status = '$status'";
        $result = parent::RunQuery($query);
        return $result;
    }

    public function getCardDetailsByMemID($membercardid) {
        $query = "SELECT MemberCardID, MID, CardID, CardNumber, SiteID, 
            LifeTimePoints, CurrentPoints, RedeemedPoints, BonusPoints, DateCreated, 
            CreatedByAID, Status
        FROM membercards WHERE MemberCardID = '$membercardid'";
        $result = parent::RunQuery($query);
        return $result;
    }

    public function updateMemberCardsStatus($cardid1, $cardid2, $status1, $status2, $aid, $dateupdated) {
        $this->StartTransaction();
        try {
            $this->ExecuteQuery("UPDATE membercards SET Status = $status1, UpdatedByAID = $aid, 
                DateUpdated = '$dateupdated' WHERE CardID = $cardid1");

            if (!App::HasError()) {
                if (!App::HasError()) {
                    $this->ExecuteQuery("UPDATE membercards SET Status = $status2, UpdatedByAID = $aid, 
                DateUpdated = '$dateupdated' WHERE CardID = $cardid2");

                    if (!App::HasError()) {
                        if (!App::HasError()) {
                            $this->CommitTransaction();
                        } else {
                            $this->RollBackTransaction();
                        }
                    } else {
                        $this->RollBackTransaction();
                    }
                } else {
                    $this->RollBackTransaction();
                }
            } else {
                $this->RollBackTransaction();
            }
        } catch (Exception $e) {
            $this->RollBackTransaction();
            App::SetErrorMessage($e->getMessage());
        }
    }

    public function getDeactivatedStatusByMID($MID) {

        $query = "SELECT Status FROM membercards WHERE MID = '$MID' AND Status = 2";

        $result = parent::RunQuery($query);
        return $result;
    }
    
    //@author JDLachica
    //@date 09/09/2014
    public function validateAndReturnCardNumber($CardNumber){
        $query = "SELECT COUNT(MemberCardID) as Count,Status FROM membercards WHERE CardNumber='$CardNumber' LIMIT 1";

        $result = parent::RunQuery($query);
        return $result; 
        
    }
    
    public function updateMemberBalance($CardNumber){
        $query = "UPDATE loyaltydb.membercards SET CurrentPoints = 0 WHERE CardNumber='$CardNumber'";
        
        return parent::ExecuteQuery($query);
    }
    
    public function insertMemberCards($membercards){
        parent::Insert($membercards);
    }
    /**
     * @author Mark Kenneth Esguerra
     * @param type $cardNumber
     */
    public function getMemberCardInfoByCardSP($cardNumber) {
        $query = "SELECT MID FROM loyaltydb.membercards 
                  WHERE CardNumber = '$cardNumber'";
        $getMID = parent::RunQuery($query);

        if (count($getMID) > 0) {
            $mid = $getMID[0]['MID'];
            $query1 = "CALL membership.sp_select_data(1, 1, 0, $mid, 'FirstName,MiddleName,LastName,NickName,Email,AlternateEmail,MobileNumber,AlternateMobileNumber,Address1,Address2,IdentificationNumber', @ResultCode, @ResultMsg, @ResultFields)";
            $result = parent::RunQuery($query1);
            
            $_memberInfo = new MemberInfo();
            $result2 = $_memberInfo->getGenericInfo($mid);
            
            $exp = explode(";", $result[0]['OUTfldListRet']);
            $arrdtls = array(0 => array('MID' => $mid, 
                                    'FirstName' => $exp[0], 
                                    'MiddleName' => $exp[1], 
                                    'LastName' => $exp[2], 
                                    'NickName' => $exp[3],
                                    'Email' => $exp[4], 
                                    'AlternateEmail' => $exp[5], 
                                    'MobileNumber' => $exp[6], 
                                    'AlternateMobileNumber' => $exp[7], 
                                    'Address1' => $exp[8], 
                                    'Address2' => $exp[9], 
                                    'IdentificationNumber' => $exp[10]));
            
            return array_merge($arrdtls[0], $result2[0]);
        }
        else {
            return array();
        }
    }
}

?>
