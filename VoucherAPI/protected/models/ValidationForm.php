<?php
class ValidationForm extends CFormModel
{
    public $from;
    public $to;
    public $site;
    public $terminal;
    public $vouchercode;
    
    public static function model($className=__CLASS__)
    {
	return parent::model($className);
    }
    
    public function rules()
    {
        return array(
            array('from, to, site, terminal','required'),
            array('vouchercode', 'length', 'allowEmpty'=>true),
            array('to', 'compare', 'compareAttribute'=>'from', 'operator'=>'>','message'=>'Invalid Date Range.')
        );        
    }
    
    public function getVoucherValidation($from, $to, $site, $terminal, $vouchercode)
    {
        $connection = Yii::app()->db;
        
        if($site == 'All')
        {
            if(empty($vouchercode))
            {
                $where = "";
            }
            else
            {
                $where = "AND v.VoucherCode ='".$vouchercode."'";
            }
        }
        else
        {
            if($terminal == 'All')
            {
                if(empty($vouchercode))
                {
                    $where = "AND s.SiteID = '".$site."'";
                }
                else
                {
                    $where = "AND s.SiteID = '".$site."' AND v.VoucherCode ='".$vouchercode."'";
                }
            }
            else
            {
                if(empty($vouchercode))
                {
                    $where = " AND t.TerminalID = '".$terminal."'";
                }
                else
                {
                    $where = " AND t.TerminalID = '".$terminal."' 
                                AND v.VoucherCode ='".$vouchercode."'";
                }
                
            }
        }
        
        $sql = "select case v.VoucherTypeID 
                when 1 then 'Ticket' 
                when 2 then 'Voucher' 
                end as VoucherType,
                v.TrackingID, v.VoucherCode, t.TerminalCode, v.Amount, ifnull(v.DateCreated,'-') as DateCreated, 
                ifnull(v.DateUsed,'-') as DateUsed, ifnull(v.DateClaimed,'-') as DateClaimed, ifnull(v.DateReimbursed,'-') as DateReimbursed, 
                ifnull(v.DateExpiry,'-') as DateExpiry, ifnull(v.DateCancelled, '-') as DateCancelled,
                case v.Status 
                when 0 then 'Inactive'
                when 1 then 'Active'
                when 2 then 'Expired'
                when 3 then 'Void'
                when 4 then 'Claimed'
                when 5 then 'Reimbursed'
                when 6 then 'Expired'
                when 7 then 'Cancelled'
                end as Status
                from x_vouchers v
                inner join terminals t
                on v.TerminalID = t.TerminalID
                inner join sites s
                on t.SiteID = s.SiteID
                and v.DateCreated >= :from
                and v.DateCreated < :to "
                .$where;
        //print_r($where);
        $command = $connection->createCommand($sql);
        $command->bindValue(':from', $from);
        $command->bindValue(':to', $to);
        $result = $command->queryAll();
        
        return $result;
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
        //return array('TST','TIM');
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
