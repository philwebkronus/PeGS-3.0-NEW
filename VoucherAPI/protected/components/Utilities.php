<?php

/**
 * @author owliber
 * @date Nov 6, 2012
 * @filename Utilities.php
 * 
 */

class Utilities extends CFormModel
{
    CONST VOUCHER_STATUS_ALL = 'All';
    CONST VOUCHER_STATUS_INACTIVE = 0;
    CONST VOUCHER_STATUS_ACTIVE = 1;
    CONST VOUCHER_STATUS_VOID = 2;
    CONST VOUCHER_STATUS_USED = 3;
    CONST VOUCHER_STATUS_REIMBURSED = 5;
    CONST VOUCHER_STATUS_CLAIMED = 4;    
    CONST VOUCHER_STATUS_EXPIRED = 6;
    CONST VOUCHER_STATUS_CANCELLED = 7;    
    
    /**
     * 
     * @param string $paramName
     * @return string parameter value
     */
    public static function getParameters($paramName)
    {
        $query = "SELECT ParamValue FROM ref_parameters
                  WHERE ParamName =:paramName";
        $sql = Yii::app()->db->createCommand($query);
        $sql->bindParam(":paramName", $paramName);
        $result = $sql->queryRow();
        
        return $result["ParamValue"];
    }
    
    public static function getBatchStatus()
    {
        return array(
            self::VOUCHER_STATUS_ALL => 'All',
            self::VOUCHER_STATUS_INACTIVE => 'Inactive',
            self::VOUCHER_STATUS_ACTIVE => 'Active',
            self::VOUCHER_STATUS_USED => 'Used',
            self::VOUCHER_STATUS_REIMBURSED => 'Reimbursed',
            self::VOUCHER_STATUS_EXPIRED => 'Expired',
            self::VOUCHER_STATUS_CANCELLED => 'Cancelled',
        );
    }
    
    public static function getVoucherStatus()
    {
        if(Yii::app()->user->isAdmin())
        {
            return array(
                self::VOUCHER_STATUS_ALL => 'All',
                self::VOUCHER_STATUS_ACTIVE => 'Active',
                self::VOUCHER_STATUS_INACTIVE => 'Inactive',
                self::VOUCHER_STATUS_VOID => 'Void',
                self::VOUCHER_STATUS_USED => 'Used',
                self::VOUCHER_STATUS_CLAIMED => 'Claimed',
                self::VOUCHER_STATUS_EXPIRED => 'Expired',
                self::VOUCHER_STATUS_CANCELLED => 'Cancelled',        
            );
        }
        else
        {
            return array(
                self::VOUCHER_STATUS_ALL => 'All',
                self::VOUCHER_STATUS_ACTIVE => 'Active',
                self::VOUCHER_STATUS_CLAIMED => 'Claimed',
                self::VOUCHER_STATUS_USED => 'Used',
                self::VOUCHER_STATUS_VOID => 'Void',
                self::VOUCHER_STATUS_EXPIRED => 'Expired'
            );
            
        }
        
    }
    
    public function voucherTypes()
    {
        $query = "SELECT VoucherTypeID, Name FROM ref_vouchertypes ORDER BY VoucherTypeID DESC";
        $sql = Yii::app()->db->createCommand($query);
        
        return $sql->queryAll();
    }
    
    public function getVoucherTypes()
    {
        return array('All'=>'All')+CHtml::listData(Utilities::voucherTypes(), 'VoucherTypeID', 'Name');
    }
    
    public function loyaltyCreditable($voucherType)
    {
        $query = "SELECT LoyaltyCreditable FROM ref_vouchertypes WHERE VoucherTypeID =:voucherTypeID";
        $sql = Yii::app()->db->createCommand($query);
        $sql->bindValue(":voucherTypeID", $voucherType);
        $result = $sql->queryRow();
        
        return $result['LoyaltyCreditable'];
        
    }
    
    public static function getSiteInfo()
    {
        $query = "SELECT s.SiteID, s.SiteCode 
                    FROM sites s
                    INNER JOIN siteaccounts sa ON s.SiteID = sa.SiteID
                    WHERE sa.AID =:AID";
        $sql = Yii::app()->db2->createCommand($query);
        $sql->bindValue(":AID",Yii::app()->session['AID']);
        $result = $sql->queryAll();
        
        return $result;
    }
    
    public static function log($message) 
    {
        Yii::log($message, 'error');
    }
    
    public static function validateInput($string){
         if (!preg_match('/[\'^£$%&*()}{@#~?><>,|=_+¬]/', $string))
            return true;
         else
            return false;
    }
    /**
     * Customized Error Logger for KAPI. 
     * @param string $errormsg Error message
     * @param string $apiname API Name
     * @param string $otherinfo Additional information for the Error
     * @author Mark Kenneth Esguerra
     * @date March 24, 2014
     */
    public static function errorLogger($errormsg, $apiname, $otherinfo)
    {
        $errorfile = date('Y_m_d').".log";
        $logpath = "logs/".$errorfile;
        
        $message = "[".date('Y-m-d H:i:s')."] ".strtoupper($apiname)."; Error: ".$errormsg."; ".$otherinfo."\n \n";
        //Create file if not exist
        if (!file_exists($logpath))
        {
            $openfile = fopen($logpath, "w+");
            chmod($logpath, 0777);
            if (!$openfile)
            {
                throw new Exception("Unable to open write");
            }
            fclose($openfile);
        }
        if (file_put_contents($logpath, $message, FILE_APPEND) ===  false)
        {
            throw new Exception("Unable to write");
        }
    }   
}
?>
