<?php

/*
 * @Date Dec 6, 2012
 * @Author owliber
 */

class Statistics extends CFormModel
{
    public function egmCountBySite($siteid=null,$status=null)
    {
        if(empty($siteid))
        {
            if(empty($status))
            {
                $query = "SELECT count(EGMMachineInfoId_PK) as `total`
                            FROM egmmachineinfo";

                $sql = Yii::app()->db->createCommand($query);
                $sql->bindValue(":siteid", $siteid);
            }
            else
            {
                $query = "SELECT count(EGMMachineInfoId_PK) as `total`
                            FROM egmmachineinfo
                            WHERE Status =:status";

                $sql = Yii::app()->db->createCommand($query);
                $sql->bindValue(":status", $status);
            }
        }
        else
        {
            if(empty($status))
            {
                $query = "SELECT count(EGMMachineInfoId_PK) as `total`
                            FROM egmmachineinfo
                            WHERE POSAccountNo =:siteid";

                $sql = Yii::app()->db->createCommand($query);
                $sql->bindValue(":siteid", $siteid);
            }
            else
            {
                $query = "SELECT count(EGMMachineInfoId_PK) as `total`
                            FROM egmmachineinfo
                            WHERE POSAccountNo =:siteid
                            AND Status =:status";

                $sql = Yii::app()->db->createCommand($query);
                $sql->bindValue(":siteid", $siteid);
                $sql->bindValue(":status", $status);
            }
        }
        
        
        $result = $sql->queryAll();
        return $result[0]['total'];
    }
    
    public function redeemedTicketsBySite($siteid=null)
    {
        if(empty($siteid))
        {
            $query = "SELECT count(VoucherID) AS `total`
                    FROM
                      vouchers v
                      INNER JOIN egmmachineinfo e on (v.TerminalID = e.TerminalID OR v.TerminalID = e.TerminalIDVIP)
                    WHERE
                      v.DateClaimed >= curdate()
                      AND v.DateClaimed < date_add(curdate(), INTERVAL 1 DAY)
                      AND v.VoucherTypeID = 1
                      AND v.`Status` = 4";
        
            $sql = Yii::app()->db->createCommand($query);
        }
        else
        {
            $query = "SELECT count(VoucherID) AS `total`
                    FROM
                      vouchers v
                      INNER JOIN egmmachineinfo e on (v.TerminalID = e.TerminalID OR v.TerminalID = e.TerminalIDVIP)
                    WHERE
                      v.DateClaimed >= curdate()
                      AND v.DateClaimed < date_add(curdate(), INTERVAL 1 DAY)
                      AND v.VoucherTypeID = 1
                      AND v.`Status` = 4
                      AND e.POSAccountNo =:siteid;";
        
            $sql = Yii::app()->db->createCommand($query);
            $sql->bindValue(":siteid", $siteid);
        }
        
        
        $result = $sql->queryAll();
        return $result[0]['total'];
    }
    
    public function generatedTicketsBySite($siteid=null)
    {
        if(empty($siteid))
        {
            $query = "SELECT count(VoucherID) AS `total`
                    FROM
                      vouchers v
                      INNER JOIN egmmachineinfo e on (v.TerminalID = e.TerminalID OR v.TerminalID = e.TerminalIDVIP)
                    WHERE
                      v.DateCreated >= curdate()
                      AND v.DateCreated < date_add(curdate(), INTERVAL 1 DAY)
                      AND v.VoucherTypeID = 1";
        
            $sql = Yii::app()->db->createCommand($query);
        }
        else
        {
            $query = "SELECT count(VoucherID) AS `total`
                    FROM
                      vouchers v
                      INNER JOIN egmmachineinfo e on (v.TerminalID = e.TerminalID OR v.TerminalID = e.TerminalIDVIP)
                    WHERE
                      v.DateCreated >= curdate()
                      AND v.DateCreated < date_add(curdate(), INTERVAL 1 DAY)
                      AND v.VoucherTypeID = 1
                      AND e.POSAccountNo =:siteid;";
        
            $sql = Yii::app()->db->createCommand($query);
            $sql->bindValue(":siteid", $siteid);
        }
        
        $result = $sql->queryAll();
        return $result[0]['total'];
    }
    
    public function usedTicketsBySite($siteid=null)
    {
        if(empty($siteid))
        {
            $query = "SELECT count(VoucherID) AS `total`
                    FROM
                      vouchers v
                      INNER JOIN egmmachineinfo e on (v.TerminalID = e.TerminalID OR v.TerminalID = e.TerminalIDVIP)
                    WHERE
                      v.DateUsed >= curdate()
                      AND v.DateUsed < date_add(curdate(), INTERVAL 1 DAY)
                      AND v.VoucherTypeID = 1
                      AND v.`Status` = 3";
        
            $sql = Yii::app()->db->createCommand($query);
        }
        else
        {
            $query = "SELECT count(VoucherID) AS `total`
                    FROM
                      vouchers v
                      INNER JOIN egmmachineinfo e on (v.TerminalID = e.TerminalID OR v.TerminalID = e.TerminalIDVIP)
                    WHERE
                      v.DateUsed >= curdate()
                      AND v.DateUsed < date_add(curdate(), INTERVAL 1 DAY)
                      AND v.VoucherTypeID = 1
                      AND v.`Status` = 3
                      AND e.POSAccountNo =:siteid;";
        
            $sql = Yii::app()->db->createCommand($query);
            $sql->bindValue(":siteid", $siteid);
        }
        
        $result = $sql->queryAll();
        return $result[0]['total'];
    }
    
    public function usedCouponsBySite($siteid=null)
    {
        if(empty($siteid))
        {
            $query = "SELECT count(VoucherID) AS `total`
                    FROM
                      vouchers v
                      INNER JOIN egmmachineinfo e on (v.TerminalID = e.TerminalID OR v.TerminalID = e.TerminalIDVIP)
                    WHERE
                      v.DateUsed >= curdate()
                      AND v.DateUsed < date_add(curdate(), INTERVAL 1 DAY)
                      AND v.VoucherTypeID = 2
                      AND v.`Status` = 3";
        
            $sql = Yii::app()->db->createCommand($query);
        }
        else
        {
            $query = "SELECT count(VoucherID) AS `total`
                    FROM
                      vouchers v
                      INNER JOIN egmmachineinfo e on (v.TerminalID = e.TerminalID OR v.TerminalID = e.TerminalIDVIP)
                    WHERE
                      v.DateUsed >= curdate()
                      AND v.DateUsed < date_add(curdate(), INTERVAL 1 DAY)
                      AND v.VoucherTypeID = 2
                      AND v.`Status` = 3
                      AND e.POSAccountNo =:siteid;";
        
            $sql = Yii::app()->db->createCommand($query);
            $sql->bindValue(":siteid", $siteid);
        }
        
        $result = $sql->queryAll();
        return $result[0]['total'];
    }
    
    public function voidTicketsBySite($siteid=null)
    {
        if(empty($siteid))
        {
            $query = "SELECT count(VoucherID) AS `total`
                    FROM
                      vouchers v
                      INNER JOIN egmmachineinfo e on (v.TerminalID = e.TerminalID OR v.TerminalID = e.TerminalIDVIP)
                    WHERE
                      v.DateUsed >= curdate()
                      AND v.DateUsed < date_add(curdate(), INTERVAL 1 DAY)
                      AND v.VoucherTypeID = 1
                      AND v.`Status` = 2";
            
            $sql = Yii::app()->db->createCommand($query);
        }
        else
        {
            $query = "SELECT count(VoucherID) AS `total`
                    FROM
                      vouchers v
                      INNER JOIN egmmachineinfo e on (v.TerminalID = e.TerminalID OR v.TerminalID = e.TerminalIDVIP)
                    WHERE
                      v.DateUsed >= curdate()
                      AND v.DateUsed < date_add(curdate(), INTERVAL 1 DAY)
                      AND v.VoucherTypeID = 1
                      AND v.`Status` = 2
                      AND e.POSAccountNo =:siteid;";
        
            $sql = Yii::app()->db->createCommand($query);
            $sql->bindValue(":siteid", $siteid);
        }
        
        $result = $sql->queryAll();
        return $result[0]['total'];
    }
}
?>
