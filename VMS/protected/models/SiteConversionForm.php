<?php

class SiteConversionForm extends CFormModel
{
    public $from;
    public $to;
    public $site;
    
    public static function model($className=__CLASS__)
    {
	return parent::model($className);
    }
    
    public function rules()
    {
        return array(
            array('from, to, site','required'),
            array('to', 'compare', 'compareAttribute'=>'from', 'operator'=>'>','message'=>'Invalid Date Range.')
        );  
    }
    public function getSiteConversion($from,$to,$site)
    {
        $connection = Yii::app()->db;
        if($site == 'All')
        {
            $where = "";
        }
        else
        {
            $where = " and s.SiteCode='".$site."'";
        }
        $sql = "select v.VoucherTypeID, s.SiteCode, sum(v.Amount) as TotalAmountReimbursed, count(v.VoucherCode) as TotalCount
                from vouchers v
                inner join terminals t
                on v.TerminalID = t.TerminalID
                inner join sites s
                on t.SiteID = s.SiteID
                where v.DateReimbursed >= :from
                and v.DateReimbursed < :to
                and v.Status = 5"
                .$where.
                " group by s.SiteCode";
        $command = $connection->createCommand($sql);
        $command->bindValue(":from", $from);
        $command->bindValue(":to", $to);
        $result = $command->queryAll();
        //print_r($sql);
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
}
?>
