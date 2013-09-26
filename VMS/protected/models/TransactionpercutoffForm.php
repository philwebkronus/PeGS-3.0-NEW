<?php

class TransactionpercutoffForm extends CFormModel
{
    
    public $transactiondate;
    public $site;
    public $vouchertype;
    public $status;
    
    public static function model($className=__CLASS__)
    {
	return parent::model($className);
    }
    
    public function rules()
    {
        return array(
            array('transactiondate, site, vouchertype, status','required'),
        );  
    }
    
    public function getCouponList($terminals, $date, $status){
        $connection = Yii::app()->db;
        if($terminals == 'All')
        {
            $where = "";
        }
        else
        {
            $where = " and c.TerminalID IN (".$terminals.")";
        }
        
        if($status == 'All'){
            $where2 = " ";
        }
        else
        {
            $where2 = " and c.Status='".$status."'";
        }
        $datetime = new DateTime($date);
        $datetime->modify('+1 day');
        $vdate = $datetime->format('Y-m-d H:i:s');

        $sql = "SELECT c.CouponID AS VoucherID, c.VoucherTypeID, c.CouponCode AS VoucherCode, 
                c.Status, c.TerminalID, c.Amount, c.DateCreated, c.DateExpiry, c.Source, 
                c.LoyaltyCreditable FROM coupons c
                WHERE c.DateCreated >= :transdate AND  c.DateCreated < :vtransdate "
                .$where." "
                .$where2.
                "group by c.CouponID, c.CouponCode";
        
        $command = $connection->createCommand($sql);
        $command->bindValue(":transdate", $date);
        $command->bindValue(":vtransdate", $vdate);
        $result = $command->queryAll();
        //print_r($sql);
        return $result;
    }
    
    
    public function getTicketList($terminals, $date, $status){
        
        $connection = Yii::app()->db;
        if($terminals == 'All')
        {
            $where = "";
        }
        else
        {
            $where = " and c.TerminalID IN (".$terminals.")";
        }
        
        if($status == 'All'){
            $where2 = " ";
        }
        else
        {
            $where2 = " and c.Status='".$status."'";
        }
        
        $datetime = new DateTime($date);
        $datetime->modify('+1 day');
        $vdate = $datetime->format('Y-m-d H:i:s');
        $sql = "SELECT c.TicketID AS VoucherID, '1' AS VoucherTypeID,  c.TicketCode AS VoucherCode, 
                c.Status, c.TerminalID, c.Amount, c.DateCreated, c.DateExpiry, 
                c.Source, c.LoyaltyCreditable FROM tickets c 
                WHERE c.DateCreated >= :transdate AND  c.DateCreated < :vtransdate"
                .$where." "
                .$where2.
                "group by c.TicketID, c.TicketCode";
        
        $command = $connection->createCommand($sql);
        $command->bindValue(":transdate", $date);
        $command->bindValue(":vtransdate", $vdate);
        $result = $command->queryAll();
        //print_r($sql);
        return $result;
    }
    
    
    public function getTerminalNamesUsingSiteID($siteid){
        
        $connection = Yii::app()->db2;
        
        $sql = "SELECT TerminalID, TerminalName FROM terminals
                WHERE SiteID = :siteid";
        $command = $connection->createCommand($sql);
        $command->bindValue(":siteid", $siteid);
        $result = $command->queryAll();

        return $result;
    }
    
    
    public function getTerminalNamesUsingTerminalID($terminalid){
        
        $connection = Yii::app()->db2;
        
        $sql = "SELECT TerminalID, TerminalName FROM terminals
                WHERE TerminalID = :terminalid";
        $command = $connection->createCommand($sql);
        $command->bindValue(":terminalid", $terminalid);
        $result = $command->queryAll();

        return $result;
    }
    
    
    public function getSiteIDfromterminals($terminalid){
        
        $connection = Yii::app()->db2;
        
        $sql = "SELECT SiteID FROM terminals
                WHERE TerminalID = :terminalid";
        $command = $connection->createCommand($sql);
        $command->bindValue(":terminalid", $terminalid);
        $result = $command->queryAll();

        return $result;
    }

    public function getSiteName($siteid){
        
        $connection = Yii::app()->db2;
        
        $sql = "SELECT SiteID, SiteName FROM sites
                WHERE SiteID = :siteid";
        $command = $connection->createCommand($sql);
        $command->bindValue(":siteid", $siteid);
        $result = $command->queryAll();

        return $result;
    }
}
?>
