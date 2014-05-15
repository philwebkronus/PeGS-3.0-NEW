<?php

class ReimbursableVoucherForm extends CFormModel
{
    public $from;
    public $to;
    public $site;
    public $terminal;
    
    public static function model($className=__CLASS__)
    {
	return parent::model($className);
    }
    
    public function rules()
    {
        return array(
            array('from, to, site, terminal','required'),
            array('from, to, site, terminal', 'safe'),
            array('to', 'compare', 'compareAttribute'=>'from', 'operator'=>'>','message'=>'Invalid Date Range.')
        );
    }
    
    public function getReimbursableVoucher($from, $to, $site, $terminal)
    {
        $connection = Yii::app()->db;
        if($site == 'All')
        {
            $where = "";
        }
        else
        {
            if($terminal == 'All')
            {
                $where = " and s.SiteID='".$site."'";
            }
            else
            {
                $where = " and s.SiteID='".$site."' and t.TerminalID='".$terminal."'";
            }
        }
        $sql = "select v.VoucherID, case VoucherTypeID when 1 then 'Ticket' when 2 then 'Voucher' end As VoucherType, 
                v.VoucherCode, t.TerminalCode, v.Amount, 
                ifnull(v.DateCreated, '-') as DateCreated, ifnull(v.DateUsed,'-') as DateUsed, 
                ifnull(v.DateClaimed, '-') as DateClaimed, ifnull(v.DateExpiry,'-') as DateExpiry
                from vouchers v
                inner join terminals t
                on v.TerminalID = t.TerminalID
                inner join sites s
                on t.SiteID = s.SiteID
                where v.DateCreated >= :from
                and v.DateCreated < :to
                and v.Status in (3,4)"
                .$where;

        $command = $connection->createCommand($sql);
        $command->bindValue(":from", $from);
        $command->bindValue(":to", $to);
        $result = $command->queryAll();
        
        return $result;
    }
    
    public function getReimburseVoucher($vouchercode)
    {
        $connection = Yii::app()->db;
        $trans = $connection->beginTransaction();
        try
        {
            $sql = "update vouchers set Status = 5, DateReimbursed = NOW(6), ReimbursedByAID = ".Yii::app()->user->getId()." where VoucherCode in (".$vouchercode.")";
            $command = $connection->createCommand($sql);
            $result = $command->execute();
            $vouchers = array();
            $vouchers = Yii::app()->session['reimburselist'];
            foreach($vouchers as $v)
            {
                AuditLog::logTransactions(29,"Voucher Code: ".$v);
            }
            $trans->commit();
        }
        catch (Exeption $e)
        {
            $trans->rollback();
        }
        
    }
    
   public function getSite()
   {
        $connection = Yii::app()->db;
        $sql = 'select SiteID, substr(SiteCode,6) as SiteCode from sites where SiteID != 1 
            and isTestSite = 0 and Status = 1 ORDER BY SiteCode ASC'; //and SiteCode not like :Site';
        $command = $connection->createCommand($sql);
        //$command->bindValue(':Site', '%TST%');
        
        $result = $command->queryAll();
        
        $site = array('All'=>'All');
        foreach($result as $row)
        {
            $site[$row['SiteID']] = $row['SiteCode'];
        }
        return $site;
    }
    
    public function getTerminal($site)
    {
        $connection = Yii::app()->db;
        $sql = 'select t.TerminalID, t.TerminalCode, s.SiteCode  
                from terminals t
                inner join sites s
                on t.SiteID = s.SiteID
                where s.SiteID = :Site';
        $command = $connection->createCommand($sql);
        $command->bindValue(':Site', $site);
        
        $result = $command->queryAll();
        
        $terminal = array('All'=>'All');
        foreach($result as $row)
        {
            $vcode = substr($row['TerminalCode'], strlen($row['SiteCode']));
            $terminal[$row['TerminalID']] = $vcode;
        }
        return json_encode($terminal);
        //return $terminal;
        //$terminal = array('ICSA-TST01','ICSA-TST02');
    }
}
?>
