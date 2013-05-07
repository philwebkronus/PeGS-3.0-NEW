<?php

class VoucherUsageForm extends CFormModel
{
    public $from;
    public $to;
    public $vouchertype;
    public $site;
    public $status;
    
    CONST VOUCHER_STATUS_ALL = 'All';
    CONST VOUCHER_STATUS_INACTIVE = 0;
    CONST VOUCHER_STATUS_ACTIVE = 1;
    CONST VOUCHER_STATUS_VOID = 2;
    CONST VOUCHER_STATUS_USED = 3;
    CONST VOUCHER_STATUS_REIMBURSED = 5;
    CONST VOUCHER_STATUS_CLAIMED = 4;    
    CONST VOUCHER_STATUS_EXPIRED = 6;
    CONST VOUCHER_STATUS_CANCELLED = 7;
    
    CONST VOUCHER_TYPE_ALL = 'All';
    CONST VOUCHER_TYPE_TICKET = 1;
    CONST VOUCHER_TYPE_VOUCHER = 2;
    
    public static function model($className=__CLASS__)
    {
	return parent::model($className);
    }
    
    public function rules()
    {
        return array(
            array('from, to, vouchertype, site, status','required'),
            array('to', 'compare', 'compareAttribute'=>'from', 'operator'=>'>','message'=>'Invalid Date Range.')
        );        
    }
    
    public function getVoucherUsageStatus($from, $to, $vouchertype, $site, $status)
    {
        $connection = Yii::app()->db;
        
        if($vouchertype=='All')
        {
            $where = "";
        }
        else
        {
            $where = " and v.VoucherTypeID=".$vouchertype;
        }
        
        if($site=='All')
        {
            $where2 = "";
        }
        else
        {
            $where2 = " and t.TerminalCode like '%".$site."%'";
        }
        
        if($status=='All')
        {
            $where3 = "";
        }
        else
        {
            $where3 = " and v.Status=".$status;
        }
        
        $sql="select CASE v.VoucherTypeID
                    WHEN 1 THEN
                    'Ticket'
                    WHEN 2 THEN
                    'Voucher'
                    END AS `VoucherType`,
                    CASE v.Status
                     WHEN 0 THEN
                     'Inactive'
                     WHEN 1 THEN
                     'Active'
                     WHEN 2 THEN
                     'Void'
                     WHEN 3 THEN
                     'Used'
                     WHEN 4 THEN
                     'Claimed'
                     WHEN 5 THEN
                     'Reimbursed'
                     WHEN 6 THEN
                     'Expired'
                     WHEN 7 THEN
                     'Cancelled'
             END AS `Status`, 
             sum(v.Amount) as `TotalAmount`, count(v.Status) as `TotalCount` 
             from vouchers v
             left join terminals t
             on v.TerminalID = t.TerminalID
             where v.DateCreated >= :from
             and v.DateCreated < :to".$where.$where2.$where3.
             " group by v.vouchertypeid, v.status";
        $command = $connection->createCommand($sql);
        $command->bindValue(':from', $from);
        $command->bindValue(':to', $to);
        $result = $command->queryAll();
        //print_r($sql);
        return $result;
        

    }
    
    public function getVoucherType()
    {
        return array(
            self::VOUCHER_TYPE_ALL => 'All',
            self::VOUCHER_TYPE_TICKET => 'Ticket',
            self::VOUCHER_TYPE_VOUCHER => 'Voucher',
        );
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
    
    public function getVoucherStatus()
    {
        return array(
            self::VOUCHER_STATUS_ALL => 'All',
            self::VOUCHER_STATUS_INACTIVE => 'Inactive',
            self::VOUCHER_STATUS_ACTIVE => 'Active',
            self::VOUCHER_STATUS_VOID => 'Void',
            self::VOUCHER_STATUS_USED => 'Used',
            self::VOUCHER_STATUS_CLAIMED => 'Claimed',
            self::VOUCHER_STATUS_REIMBURSED => 'Reimbursed',
            self::VOUCHER_STATUS_EXPIRED => 'Expired',
            self::VOUCHER_STATUS_CANCELLED => 'Cancelled', 
        );
    }
}
?>
