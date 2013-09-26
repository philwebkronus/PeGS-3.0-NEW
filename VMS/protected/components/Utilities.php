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
        $sql = YII::app()->db->createCommand($query);
        $sql->bindValue(":AID",Yii::app()->session['AID']);
        $result = $sql->queryAll();
        
        return $result;
    }
    
    public static function log($message) 
    {
        Yii::log($message, 'error');
    }
        
}
?>
