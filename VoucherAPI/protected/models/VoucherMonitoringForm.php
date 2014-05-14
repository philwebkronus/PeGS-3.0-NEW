<?php

class VoucherMonitoringForm extends CFormModel
{
    public $from;
    public $to;
    public $status;
    public $site;
    public $terminal;
    public $vouchercode;
    
    CONST VOUCHER_STATUS_ALL = 'All';
    CONST VOUCHER_STATUS_INACTIVE = 0;
    CONST VOUCHER_STATUS_ACTIVE = 1;
    CONST VOUCHER_STATUS_VOID = 2;
    CONST VOUCHER_STATUS_USED = 3;
    CONST VOUCHER_STATUS_REIMBURSED = 5;
    CONST VOUCHER_STATUS_CLAIMED = 4;    
    CONST VOUCHER_STATUS_EXPIRED = 6;
    CONST VOUCHER_STATUS_CANCELLED = 7;   
    
    public static function model($className=__CLASS__)
    {
	return parent::model($className);
    }
    
    public function rules()
    {
        return array(
            array('from, to, status, site, terminal','required'),
            array('vouchercode', 'length', 'allowEmpty'=>true),
            array('to', 'compare', 'compareAttribute'=>'from', 'operator'=>'>','message'=>'Invalid Date Range.')
        );        
    }
    public function getAllVouchers()
    {
        $query = "SELECT v.VoucherID AS `id`
                        , v.VoucherCode
                        , t.TerminalCode
                        , v.Amount
                        , v.DateCreated
                        , v.DateExpiry
                        , CASE v.Status
                          WHEN 0 THEN
                            'Unclaimed'
                          WHEN 1 THEN
                            'Claimed'
                          WHEN 2 THEN
                            'Expired'
                          WHEN 3 THEN
                            'Cancelled'
                          END AS `Status`
                  FROM vouchers v
                  INNER JOIN terminals t
                  ON v.TerminalID = t.TerminalID";
        $sql = Yii::app()->db->createCommand($query);
        return $sql->queryAll();
    }    
    
    public function getVouchersByRangeStatus($datefrom,$dateto,$status,$site,$terminal,$vouchercode)
    {
        if($status != 'All')
        {
            $where = ' AND v.Status='.$status;
        }else{
            $accounttype = Yii::app()->session['AccountType'];
            if($accounttype==4)
            {
                $status = '0,1,4';
            }
            else
            {
                $status = '0,1,2,3,4,5,6,7';   
            }
            $where = ' AND v.Status IN ('.$status.')';
        }
        
        if($site == 'All')
        {
            if(empty($vouchercode))
            {
                $where2 = "";
            }
            else
            {
                $where2 = "AND v.VoucherCode ='".$vouchercode."'";
            }
        }
        else
        {
            if($terminal == 'All')
            {
                if(empty($vouchercode))
                {
                    $where2 = "AND s.SiteID = '".$site."'";
                }
                else
                {
                    $where2 = "AND s.SiteID = '".$site."' AND v.VoucherCode ='".$vouchercode."'";
                }
            }
            else
            {
                if(empty($vouchercode))
                {
                    $where2 = " AND t.TerminalID = '".$terminal."'";
                }
                else
                {
                    $where2 = " AND t.TerminalID = '".$terminal."' 
                                AND v.VoucherCode ='".$vouchercode."'";
                }
                
            }
        }
        
        $query = "SELECT v.VoucherID AS `id`
                        , v.VoucherCode
                        , t.TerminalCode
                        , v.Amount
                        , v.DateCreated
                        , v.DateExpiry
                        , CASE v.Status
                          WHEN 0 THEN
                            'Inactive'
                          WHEN 1 THEN
                            'Active'
                          WHEN 2 THEN
                            'Expired'
                          WHEN 3 THEN
                            'Void'
                          WHEN 4 THEN
                            'Claimed'
                          WHEN 5 THEN
                            'Reimbursed'
                          WHEN 6 THEN
                            'Expired'
                          WHEN 7 THEN
                            'Cancelled'
                          END AS `Status`
                  FROM vouchers v
                  INNER JOIN terminals t
                  ON v.TerminalID = t.TerminalID
                  INNER JOIN sites s
                  ON t.SiteID = s.SiteID
                  WHERE v.DateCreated >=:dateFrom
                      AND v.DateCreated <:dateTo "
                  //   AND `Status` =:status"
                  .$where." ".$where2;
        //print_r($where." ".$where2);
        
        $sql = Yii::app()->db->createCommand($query);
        $sql->bindParam(":dateFrom", $datefrom);
        $sql->bindParam(":dateTo", $dateto);
       // $sql->bindParam(":status", $status);
        
        return $sql->queryAll();
    }
    
    public function getVoucherStatus()
    {
        $accounttype = Yii::app()->session['AccountType'];
        if ($accounttype == 4)
        {
            return array(
            self::VOUCHER_STATUS_ALL => 'All',
            self::VOUCHER_STATUS_ACTIVE => 'Active',
            self::VOUCHER_STATUS_USED => 'Used',
            self::VOUCHER_STATUS_CLAIMED => 'Claimed',
            );
                        
        }
        else
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
    
    public function getSite()
    {
        $connection = Yii::app()->db;
        $sql = 'select SiteID, substr(SiteCode,6) as SiteCode from sites where SiteID != 1 
            and isTestSite = 0 and Status = 1  ORDER BY SiteCode ASC'; //and SiteCode not like :Site';
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
