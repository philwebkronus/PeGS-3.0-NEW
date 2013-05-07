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
                $where = " and s.SiteCode='".$site."'";
            }
            else
            {
                $where = " and s.SiteCode='".$site."' and t.TerminalCode='".$terminal."'";
            }
        }
        $sql = "select case VoucherTypeID when 1 then 'Ticket' when 2 then 'Voucher' end As VoucherType, 
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
    
   public function getSite()
   {
        $connection = Yii::app()->db;
        $sql = 'select SiteCode from sites where isTestSite = 0 and Status = 1'; //and SiteCode not like :Site';
        $command = $connection->createCommand($sql);
        //$command->bindValue(':Site', '%TST%');
        
        $result = $command->queryAll();
        
        $site = array('All'=>'All');
        foreach($result as $row)
        {
            $site[$row['SiteCode']] = $row['SiteCode'];
        }
        return $site;
    }
    
    public function getTerminal($site)
    {
        $connection = Yii::app()->db;
        $sql = 'select t.TerminalCode 
                from terminals t
                inner join sites s
                on t.SiteID = s.SiteID
                where s.SiteCode = :Site';
        $command = $connection->createCommand($sql);
        $command->bindValue(':Site', $site);
        
        $result = $command->queryAll();
        
        $terminal = array('All'=>'All');
        foreach($result as $row)
        {
            $terminal[$row['TerminalCode']] = $row['TerminalCode'];
        }
        return json_encode($terminal);
    }
}
?>
