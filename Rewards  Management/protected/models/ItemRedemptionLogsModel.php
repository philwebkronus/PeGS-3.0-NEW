<?php

class ItemRedemptionLogsModel extends CFormModel
{
    CONST ALL = 0;
    CONST ITEM = 1;
    CONST PARTNER = 2;
    CONST CATEGORY = 3;
    
    CONST PLAYER_ALL = 1;
    
    CONST REWARDS_REDEMPTION = 1;
    CONST UNIQUE_MEMBER_PARTICIPATION = 2;
    CONST REWARDS_POINTS_USAGE = 3;
    
    public function updateItemRedemptionLogs($rewarditemid, $cashiername, $branchdetails, $remarks, $mid, $aid, $securitycode, $serialcode){

            $connection = Yii::app()->db;
            $date = date("Y-m-d H:i:s"); 
            $sql="UPDATE itemredemptionlogs SET CashierName = :cashiername, BranchDetails = :branchdetails,
                Remarks = :remarks, Status = 3, DateClaimed = :date, ClaimedByAID = :aid 
                WHERE RewardItemID = :rewarditemid AND MID = :mid AND SecurityCode = :securitycode 
                AND SerialCode = :serialcode";
            $command = $connection->createCommand($sql);
            $command->bindValue(':cashiername', $cashiername);
            $command->bindValue(':branchdetails', $branchdetails);
            $command->bindValue(':remarks', $remarks);
            $command->bindValue(':mid', $mid);
            $command->bindValue(':date', $date);
            $command->bindValue(':aid', $aid);
            $command->bindValue(':rewarditemid', $rewarditemid);
            $command->bindValue('securitycode', $securitycode);
            $command->bindValue('serialcode', $serialcode);
            $result = $command->execute();

            return $result;

        }
        
        
    public function checkSerialSecCodes($serial, $security, $rewarditemid){
        
        $connection = Yii::app()->db;
         
        $sql="SELECT MID, RewardItemID, SecurityCode, SerialCode, ValidFrom, ValidTo, Status, Source FROM itemredemptionlogs 
            WHERE SerialCode = :serial AND SecurityCode = :security AND RewardItemID = :rewarditemid;";
        $command = $connection->createCommand($sql);
        $command->bindValue(':serial', $serial);
        $command->bindValue(':security', $security);
        $command->bindValue(':rewarditemid', $rewarditemid);
        $result = $command->queryAll();
         
        return $result;
        
    }
    /**
     * Inquiry for Item Redemption Logs
     * @param int $fitler <p>The filter selected 1 - Item 2 - Partner 3 - Category 0 - ALL</p>
     * @param string $particular <p>The value of selected particular</p>
     * @param int $player <p>The selected player segment 1 - ALL 2 - Regular 3 - VIP</p>
     * @param date $date_from <p>The From Date entered in date range</p>
     * @param date $date_to <p>The To Date entered in date range</p>
     * @author Mark Kenneth Esguerra
     * @date Sep-06-13
     */
    public function inquiry($inquiry, $filter, $particular, $player, $date_from, $date_to, $all = NULL)
    {
        //Determine what select method will going to use depending on the inquiry
        switch($inquiry)
        {
            case self::REWARDS_REDEMPTION:
                if (is_null($all))
                    $select = "SELECT COUNT(ItemRedemptionLogID) as ItemRedeemed, a.DateCreated,";
                else
                    $select = "SELECT a.ItemRedemptionLogID, a.DateCreated";
                    Yii::app()->session['inquiry'] = self::REWARDS_REDEMPTION;
                break;
            case self::UNIQUE_MEMBER_PARTICIPATION:
                if (is_null($all))
                    $select = "SELECT COUNT(DISTINCT(MID)) as MembersRedeemed, a.DateCreated,";
                else 
                    $select = "SELECT DISTINCT(MID) as MembersRedeemed, a.DateCreated";
                    Yii::app()->session['inquiry'] = self::UNIQUE_MEMBER_PARTICIPATION;
                break;
            case self::REWARDS_POINTS_USAGE:
                if (is_null($all))
                    $select = "SELECT SUM(RedeemedPoints) as TotalRedeemedPoints, a.DateCreated,";
                else
                    $select = "SELECT a.RedeemedPoints, a.DateCreated";
                    Yii::app()->session['inquiry'] = self::REWARDS_POINTS_USAGE;
                break;
        }
        
        $connection = Yii::app()->db;
        $particularID = substr($particular, 1, 1); //get only the real ID of the ITEM (Exclude the appended letter)
        /**
         * Check if the selected Particular is ALL.
         * IF not ALL, get the number of redeemed items depending on the selected specific particular 
         * and player segment.
         * IF ALL is the selected particular, get the number of redeemed items depending on 
         * all particulars created on the specific filter.
         * Ex. If the filter is PARTNER, it will get the number of redeemed items depending on all 
         * partners created.
         * 
         */
        if ($particularID != 0) //If specific Particular was selected
        {
            switch ($filter) //CHECK FILTER
            {
                case self::ITEM: //ITEM
                    if ($player != self::PLAYER_ALL) //If a specific PLAYER SEGMENT is selected
                    {
                        $query[0] = $select;
                        $query[1] = "FROM itemredemptionlogs a
                                    INNER JOIN rewarditems b ON a.RewardItemID = b.RewardItemID
                                    WHERE b.PClassID = :player  AND b.RewardItemID = ".$particularID." AND
                                    a.DateCreated >= '$date_from 00:00:00' AND a.DateCreated <= '$date_to 11:59:59'"."
                                    ";
                    }
                    else //If ALL PLAYER SEGMENTS selected
                    {
                        $query[0] = $select;
                        $query[1] = "FROM itemredemptionlogs a
                                     INNER JOIN rewarditems b ON a.RewardItemID = b.RewardItemID
                                     WHERE b.PClassID IN (1, 2) AND b.RewardItemID = ".$particularID." AND
                                     a.DateCreated >= '$date_from 00:00:00' AND a.DateCreated <= '$date_to 11:59:59'"."
                                     ";
                    }
                    break;
                case self::PARTNER: //PARTNER
                    if ($player != self::PLAYER_ALL) //If a specific PLAYER SEGMENT is selected
                    {
                        $query[0] = $select;
                        $query[1] = "FROM itemredemptionlogs a
                                     INNER JOIN rewarditems b ON a.RewardItemID = b.RewardItemID
                                     WHERE b.PClassID = :player AND b.PartnerID = ".$particularID." AND
                                     a.DateCreated >= '$date_from 00:00:00' AND a.DateCreated <= '$date_to 11:59:59'"."
                                     ";
                    }
                    else //If ALL PLAYER SEGMENTS is selected
                    {
                        $query[0] = $select;
                        $query[1] = "FROM itemredemptionlogs a
                                     INNER JOIN rewarditems b ON a.RewardItemID = b.RewardItemID
                                     WHERE b.PClassID IN (1, 2) AND b.PartnerID = ".$particularID." AND
                                     a.DateCreated >= '$date_from 00:00:00' AND a.DateCreated <= '$date_to 11:59:59'"."
                                     ";
                    }
                    break;
                case self::CATEGORY: //CATEGORY
                    if ($player != self::PLAYER_ALL) //If a specific PLAYER SEGMENT is selected
                    {
                        $query[0] = $select;
                        $query[1] = "FROM itemredemptionlogs a
                                     INNER JOIN rewarditems b ON a.RewardItemID = b.RewardItemID
                                     WHERE b.PClassID = :player AND b.CategoryID = ".$particularID." AND
                                     a.DateCreated >= '$date_from 00:00:00' AND a.DateCreated <= '$date_to 11:59:59'"."
                                     ";
                    }
                    else //If ALL PLAYER SEGMENTS is selected
                    {
                        $query[0] = $select;
                        $query[1] = "FROM itemredemptionlogs a
                                     INNER JOIN rewarditems b ON a.RewardItemID = b.RewardItemID
                                     WHERE b.PClassID IN (1, 2) AND b.CategoryID = ".$particularID." AND
                                     a.DateCreated >= '$date_from 00:00:00' AND a.DateCreated <= '$date_to 11:59:59'"."
                                     ";
                    }
                    break;
                case self::ALL: //ALL
                    if ($player != self::PLAYER_ALL) //If a specific PLAYER SEGMENT is selected
                    {
                        //If ALL is the filter, determine its classification by the 
                        //appended letter in the each ID
                        //'I' - Item; 'P' - Partner; 'C' - Category; 'A' - ALL
                        $appendedLetter = substr($particular, 0, 1); //get the letter appended
                        if ($appendedLetter == "I")
                        {
                            $query[0] = $select;
                            $query[1] = "FROM itemredemptionlogs a
                                         INNER JOIN rewarditems b ON a.RewardItemID = b.RewardItemID
                                         WHERE b.PClassID = :player  AND b.RewardItemID = ".$particularID." AND
                                         a.DateCreated >= '$date_from 00:00:00' AND a.DateCreated <= '$date_to 11:59:59'"."
                                         ";
                        }
                        else if ($appendedLetter == "P")
                        {
                            $query[0] = $select;
                            $query[1] = "FROM itemredemptionlogs a
                                         INNER JOIN rewarditems b ON a.RewardItemID = b.RewardItemID
                                         WHERE b.PClassID = :player AND b.PartnerID = ".$particularID." AND
                                         a.DateCreated >= '$date_from 00:00:00' AND a.DateCreated <= '$date_to 11:59:59'"."    
                                         ";
                        }
                        else if ($appendedLetter == "C")
                        {
                            $query[0] = $select;
                            $query[1] = "FROM itemredemptionlogs a
                                         INNER JOIN rewarditems b ON a.RewardItemID = b.RewardItemID
                                         WHERE b.PClassID = :player AND b.CategoryID = ".$particularID." AND 
                                         a.DateCreated >= '$date_from 00:00:00' AND a.DateCreated <= '$date_to 11:59:59'"."
                                         ";
                        }
                        else if ($appendedLetter == "A")
                        {
                            $query[0] = $select;
                            $query[1] = "FROM itemredemptionlogs a
                                         INNER JOIN rewarditems b ON a.RewardItemID = b.RewardItemID
                                         WHERE b.PClassID = :player AND
                                         a.DateCreated >= '$date_from 00:00:00' AND a.DateCreated <= '$date_to 11:59:59'"."
                                         ";
                        }
                    }
                    else //If ALL PLAYER SEGMENTS is selected
                    {
                        //If ALL is the filter, determine its filter classification by the 
                        //appended letter in the each ID
                        //'I' - Item; 'P' - Partner; 'C' - Category; 'A' - ALL
                        $appendedLetter = substr($particular, 0, 1); //get the letter appended
                        if ($appendedLetter == "I")
                        {
                            $query[0] = $select;
                            $query[1] = "FROM itemredemptionlogs a
                                         INNER JOIN rewarditems b ON a.RewardItemID = b.RewardItemID
                                         WHERE b.PClassID = b.PClassID IN (1, 2)  AND b.RewardItemID = ".$particularID." AND 
                                         a.DateCreated >= '$date_from 00:00:00' AND a.DateCreated <= '$date_to 11:59:59'"."
                                         ";
                        }
                        else if ($appendedLetter == "P")
                        {
                            $query[0] = $select;
                            $query[1] = "FROM itemredemptionlogs a
                                         INNER JOIN rewarditems b ON a.RewardItemID = b.RewardItemID
                                         WHERE b.PClassID = b.PClassID IN (1, 2) AND b.PartnerID = ".$particularID." AND
                                         a.DateCreated >= '$date_from 00:00:00' AND a.DateCreated <= '$date_to 11:59:59'"."
                                        ";
                        }
                        else if ($appendedLetter == "C")
                        {
                            $query[0] = $select;
                            $query[1] = "FROM itemredemptionlogs a
                                         INNER JOIN rewarditems b ON a.RewardItemID = b.RewardItemID
                                         WHERE b.PClassID = b.PClassID IN (1, 2) AND b.CategoryID = ".$particularID." AND 
                                         a.DateCreated >= '$date_from 00:00:00' AND a.DateCreated <= '$date_to 11:59:59'"."
                                         ";
                        }
                        else if ($appendedLetter == "A")
                        {
                            $query[0] = $select;
                            $query[1] = "FROM itemredemptionlogs a
                                         INNER JOIN rewarditems b ON a.RewardItemID = b.RewardItemID
                                         WHERE b.PClassID = b.PClassID IN (1, 2) AND 
                                         a.DateCreated >= '$date_from 00:00:00' AND a.DateCreated <= '$date_to 11:59:59'"."
                                         ";
                        }
                    }
                    break;
            }
        }
        else //If all was selected
        {
            switch ($filter) // CHECK FILTER
            {
                case self::ITEM: //ITEM
                    if ($player != self::PLAYER_ALL) //If a specific PLAYER SEGMENT IS SELECTED
                    {
                        $query[0] = $select; 
                        $query[1] = "FROM itemredemptionlogs a
                                     INNER JOIN rewarditems b ON a.RewardItemID = b.RewardItemID
                                     WHERE b.PClassID = :player AND
                                     a.DateCreated >= '$date_from 00:00:00' AND a.DateCreated <= '$date_to 11:59:59'"."
                                     ";
                    }
                    else //If ALL PLAYER SEGMENTS IS SELECTED
                    {
                        $query[0] = $select; 
                        $query[1] = "FROM itemredemptionlogs a
                                     INNER JOIN rewarditems b ON a.RewardItemID = b.RewardItemID
                                     WHERE b.PClassID IN (1, 2) AND
                                     a.DateCreated >= '$date_from 00:00:00' AND a.DateCreated <= '$date_to 11:59:59'"."
                                     ";
                    }
                    break;
                case self::PARTNER: //PARTNER
                    //Select Partners
                    $getPartners = "SELECT PartnerID FROM ref_partners";
                    $command = $connection->createCommand($getPartners);
                    $partners = $command->queryAll();
                    if ($player != self::PLAYER_ALL) //If a specific PLAYER SEGMENT IS SELECTED
                    {
                        for ($i = 0; count($partners) > $i; $i++)
                        {
                            $arrpartners[] = $partners[$i]['PartnerID'];
                        }
                        $query[0] = $select; 
                        $query[1] = "FROM itemredemptionlogs a
                                  INNER JOIN rewarditems b ON a.RewardItemID = b.RewardItemID
                                  WHERE b.PClassID = :player AND b.PartnerID IN ("."'".implode("','",$arrpartners)."'".") AND 
                                  a.DateCreated >= '$date_from 00:00:00' AND a.DateCreated <= '$date_to 11:59:59'"."
                                  ";
                    }
                    else //If ALL PLAYER SEGMENTS IS SELECTED
                    {
                        for ($i = 0; count($partners) > $i; $i++)
                        {
                            $arrpartners[] = $partners[$i]['PartnerID'];
                        }
                        $query[0] = $select;
                        $query[1] = "FROM itemredemptionlogs a
                                     INNER JOIN rewarditems b ON a.RewardItemID = b.RewardItemID
                                     WHERE b.PClassID IN (1, 2) AND b.PartnerID IN ("."'".implode("','",$arrpartners)."'".") AND 
                                     a.DateCreated >= '$date_from 00:00:00' AND a.DateCreated <= '$date_to 11:59:59'"."
                                     ";
                    }
                    break;
                case self::CATEGORY: //CATEGORY
                    //Select Categories
                    $getCategories = "SELECT CategoryID FROM ref_category";
                    $command = $connection->createCommand($getCategories);
                    $categories = $command->queryAll();
                    
                    if ($player != self::PLAYER_ALL) //If a specific PLAYER SEGMENT IS SELECTED
                    {
                        for ($i = 0; count($categories) > $i; $i++)
                        {
                            $arrcategories[] = $categories[$i]['CategoryID'];
                        }
                        $query[0] = $select; 
                        $query[1] = "FROM itemredemptionlogs a
                                     INNER JOIN rewarditems b ON a.RewardItemID = b.RewardItemID
                                     WHERE b.PClassID IN (1, 2) AND b.CategoryID IN ("."'".implode("','",$arrcategories)."'".") AND 
                                     a.DateCreated >= '$date_from 00:00:00' AND a.DateCreated <= '$date_to 11:59:59'"."
                                     ";
                    }
                    else //If a specific PLAYER SEGMENTS IS SELECTED
                    {
                        for ($i = 0; count($categories) > $i; $i++)
                        {
                            $arrcategories[] = $categories[$i]['CategoryID'];
                        }
                        $query[0] = $select;
                        $query[1] = "FROM itemredemptionlogs a
                                     INNER JOIN rewarditems b ON a.RewardItemID = b.RewardItemID
                                     WHERE b.PClassID IN (1, 2) AND b.CategoryID IN ("."'".implode("','",$arrcategories)."'".") AND 
                                     a.DateCreated >= '$date_from 00:00:00' AND a.DateCreated <= '$date_to 11:59:59'"."
                                     ";
                    }
                    break;
                case self::ALL: //ALL
                    if ($player != self::PLAYER_ALL)
                    {
                        //If ALL is the filter, determine its classification by the 
                        //appended letter in the each ID
                        //'I' - Item; 'P' - Partner; 'C' - Category; 'A' - ALL
                        $appendedLetter = substr($particular, 0, 1); //get the letter appended
                        if ($appendedLetter == "I")
                        {
                            $query[0] = $select; 
                            $query[1] = "FROM itemredemptionlogs a
                                         INNER JOIN rewarditems b ON a.RewardItemID = b.RewardItemID
                                         WHERE b.PClassID = :player AND 
                                         a.DateCreated >= '$date_from 00:00:00' AND a.DateCreated <= '$date_to 11:59:59'"."
                                         ";
                        }
                        else if ($appendedLetter == "P")
                        {
                            //Select Partners
                            $getPartners = "SELECT PartnerID FROM ref_partners";
                            $command = $connection->createCommand($getPartners);
                            $partners = $command->queryAll();
                            for ($i = 0; count($partners) > $i; $i++)
                            {
                                $arrpartners[] = $partners[$i]['PartnerID'];
                            }
                            $query[0] = $select;
                            $query[1] = "FROM itemredemptionlogs a
                                         INNER JOIN rewarditems b ON a.RewardItemID = b.RewardItemID 
                                         WHERE b.PClassID = :player AND b.PartnerID IN ("."'".implode("','",$arrpartners)."'".") AND 
                                         a.DateCreated >= '$date_from 00:00:00' AND a.DateCreated <= '$date_to 11:59:59'"."
                                         ";
                        }
                        else if ($appendedLetter == "C")
                        {
                            //Select Categories
                            $getCategories = "SELECT CategoryID FROM ref_category";
                            $command = $connection->createCommand($getCategories);
                            $categories = $command->queryAll();
                            for ($i = 0; count($categories) > $i; $i++)
                            {
                                $arrcategories[] = $categories[$i]['CategoryID'];
                            }
                            $query[0] = $select; 
                            $query[1] = "FROM itemredemptionlogs a
                                         INNER JOIN rewarditems b ON a.RewardItemID = b.RewardItemID
                                         WHERE b.PClassID = :player AND b.CategoryID IN ("."'".implode("','",$arrcategories)."'".") AND 
                                         a.DateCreated >= '$date_from 00:00:00' AND a.DateCreated <= '$date_to 11:59:59'"."
                                         ";
                        }
                        else if ($appendedLetter == "A")
                        {
                            $query[0] = $select;
                            $query[1] = "FROM itemredemptionlogs a
                                         INNER JOIN rewarditems b ON a.RewardItemID = b.RewardItemID
                                         WHERE b.PClassID = :player AND 
                                         a.DateCreated >= '$date_from 00:00:00' AND a.DateCreated <= '$date_to 11:59:59'"."
                                         ";
                        }
                    }
                    else
                    {
                        //If ALL is the filter, determine its filter classification by the 
                        //appended letter in the each ID
                        $appendedLetter = substr($particular, 0, 1); //get the letter appended
                        if ($appendedLetter == "I")
                        {
                            $query[0] = $select;
                            $query[1] = "FROM itemredemptionlogs a
                                         INNER JOIN rewarditems b ON a.RewardItemID = b.RewardItemID
                                         WHERE b.PClassID IN (1, 2) AND
                                         a.DateCreated >= '$date_from 00:00:00' AND a.DateCreated <= '$date_to 11:59:59'"."
                                         ";
                        }
                        else if ($appendedLetter == "P")
                        {
                            //Select Partners
                            $getPartners = "SELECT PartnerID FROM ref_partners";
                            $command = $connection->createCommand($getPartners);
                            $partners = $command->queryAll();
                            for ($i = 0; count($partners) > $i; $i++)
                            {
                                $arrpartners[] = $partners[$i]['PartnerID'];
                            }
                            $query[0] = $select;
                            $query[1] = "FROM itemredemptionlogs a
                                         INNER JOIN rewarditems b ON a.RewardItemID = b.RewardItemID
                                         WHERE b.PClassID IN (1, 2) AND b.PartnerID IN ("."'".implode("','",$arrpartners)."'".") AND 
                                         a.DateCreated >= '$date_from 00:00:00' AND a.DateCreated <= '$date_to 11:59:59'"."
                                         ";
                        }
                        else if ($appendedLetter == "C")
                        {
                            //Select Categories
                            $getCategories = "SELECT CategoryID FROM ref_category";
                            $command = $connection->createCommand($getCategories);
                            $categories = $command->queryAll();
                            for ($i = 0; count($categories) > $i; $i++)
                            {
                                $arrcategories[] = $categories[$i]['CategoryID'];
                            }
                            $query[0] = $select; 
                            $query[1] = "FROM itemredemptionlogs a
                                         INNER JOIN rewarditems b ON a.RewardItemID = b.RewardItemID
                                         WHERE b.PClassID IN (1, 2) AND b.CategoryID IN ("."'".implode("','",$arrcategories)."'".") AND 
                                         a.DateCreated >= '$date_from 00:00:00' AND a.DateCreated <= '$date_to 11:59:59'"."
                                         ";
                        }
                        else if ($appendedLetter == "A")
                        {
                            $query[0] = $select;
                            $query[1] = "FROM itemredemptionlogs a
                                         INNER JOIN rewarditems b ON a.RewardItemID = b.RewardItemID
                                         WHERE b.PClassID IN (1, 2) AND 
                                         a.DateCreated >= '$date_from 00:00:00' AND a.DateCreated <= '$date_to 11:59:59'"."
                                         ";
                        }
                    }
                    break;
            }
        }
        return $query;
    }
        /**
     * Run the Final Query after appending a GROUP BY function <br />on the query retrieved from other function.
     * @param type $query The query without GROUP BY function
     * @param type $player The PlayerSegmentID, used for binding param
     * @return array Array result
     * @author Mark Kenneth Esguerra
     * @date Sep-12-13
     */
    public function runQuery($query, $player)
    {
        $connection = Yii::app()->db;
        $command = $connection->createCommand($query);
        $command->bindParam(":player", $player);
        $result = $command->queryAll();
        return $result;
    }
    
 }
?>
