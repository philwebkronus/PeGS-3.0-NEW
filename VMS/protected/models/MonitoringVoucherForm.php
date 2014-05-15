<?php
/**
 * MonitoringVoucherForm
 * @datecreated 2013
 * @author gvjagolino
 */
class MonitoringVoucherForm extends CFormModel
{
    public $vouchertype;
    public $expirydate;
    public $datefrom;
    public $dateto;
    public $hdn_voucher;
    public $rawdata;
    
    public static function model($className=__CLASS__)
    {
        return parent::model($className);
    }
    
    public function rules()
    {
        return array(
            array('vouchertype, expirydate, datefrom, dateto, hdn_voucher','required'),
            array('vouchertype, expirydate', 'safe'),
        );
    }
    
    public function getVoucherCount($tablename, $status){
        $query = "SELECT COUNT(SerialNumber) AS Count FROM $tablename WHERE Status = $status;";
        $sql = Yii::app()->db->createCommand($query);
        
        $result = $sql->queryAll();
        
        foreach ($result as $row5) {
            $paramvalue = $row5['Count'];
        }
        
        return $paramvalue;
    }
    
    public function checkGeneratedVoucherTable($tablename){
        $query = "SELECT COUNT(TABLE_NAME) AS TABLE_COUNT 
                FROM INFORMATION_SCHEMA.COLUMNS
                WHERE TABLE_NAME LIKE '%$tablename%'
                  AND TABLE_SCHEMA='vouchermanagement';";
        $sql = Yii::app()->db->createCommand($query);
        
        $result = $sql->queryAll();
        
        foreach ($result as $row5) {
            $paramvalue = $row5['TABLE_COUNT'];
        }
        
        return $paramvalue;
    }
    
    public function getTicketCount($status, $datefrom, $dateto, $queued = null){
        //Divide Active tickets, get QUEUED tickets
        if ($status != 1)
        {
            $query = "SELECT COUNT(TicketID) AS Count FROM tickets WHERE Status = :status 
                  AND DateCreated >= '$datefrom 00:00:00' AND DateCreated <= '$dateto 23:59:59'";
        }
        else
        {
            //get queued tickets. Queued tickets are those TISiteID that is null
            if ($queued != null)
            {
                $query = "SELECT COUNT(TicketID) AS Count FROM tickets WHERE Status = :status 
                  AND SiteID IS NULL AND DateCreated >= '$datefrom 00:00:00' AND DateCreated <= '$dateto 23:59:59'";
            }
            else //get active tickets
            {
                $query = "SELECT COUNT(TicketID) AS Count FROM tickets WHERE Status = :status 
                  AND SiteID IS NOT NULL AND DateCreated >= '$datefrom 00:00:00' AND DateCreated <= '$dateto 23:59:59'";
            }
        }
        $sql = Yii::app()->db->createCommand($query);
        $sql->bindValues(array(
                ":status"=>$status
        ));
        
        $ticketcount = $sql->queryRow();
        
        return $ticketcount['Count'];
    }
    
    public function getAllTicketCount($datefrom, $dateto){
        $query = "SELECT COUNT(TicketID) AS Count FROM tickets 
                  WHERE DateCreated >= '$datefrom 00:00:00' AND DateCreated <= '$dateto 23:59:59'";
        $sql = Yii::app()->db->createCommand($query);
        $ticketcount = $sql->queryRow();
        
        return $ticketcount['Count'];
    }
    
    public function getCouponCount($status){
        $query = "SELECT COUNT(CouponID) AS Count FROM coupons WHERE Status = :status";
        $sql = Yii::app()->db->createCommand($query);
        $sql->bindValues(array(
                ":status"=>$status
        ));
        
        $couponcount = $sql->queryRow();
        
        return $couponcount['Count'];
    }
    
    public function getAllCouponCount(){
        $query = "SELECT COUNT(CouponID) AS Count FROM coupons";
        $sql = Yii::app()->db->createCommand($query);
        
        $ticketcount = $sql->queryRow();
        
        return $ticketcount['Count'];
    }
    
   
}    
?>
