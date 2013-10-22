<?php

/**
 * Description of CouponModel
 *
 * @author elperez
 */
class CouponModel extends CFormModel{
    
    public $_connection;


    public function __construct() {
        $this->_connection = Yii::app()->db;
    }
    
    /**
     * inserts coupon
     * @param int $voucherTypeID
     * @param str $trackingID
     * @param str $couponCode
     * @param int $batchNo
     * @param int $terminalID
     * @param float $amount
     * @param int $aid
     * @param int $source
     * @param str $couponBatchTable
     * @param int $loyaltyCreditable
     * @return boolean success | failed transaction
     */
    public function insertCoupon($voucherTypeID, $trackingID, $couponCode, 
            $batchNo, $terminalID, $amount, $aid, $source, 
            $couponBatchTable, $loyaltyCreditable, $dateExpiry){
        
        $beginTrans = $this->_connection->beginTransaction();
        
        try{
            $query = "INSERT INTO coupons(VoucherTypeID, TrackingID, CouponCode, VoucherBatchID, 
                      TerminalID, Amount, DateCreated, DateUsed, CreatedByAID, Source, 
                      Status, LoyaltyCreditable, DateExpiry) 
                      VALUES(:vouchertypeid, :trackingid, :couponcode, :batchno, :terminalid, 
                             :amount, now_usec(), now_usec(), :aid, :source, 3, :loyaltycreditable,
                             :dateexpiry)";
            
            $sql = $this->_connection->createCommand($query);
            
            $sql->bindValues(array(
                    ":vouchertypeid"=>$voucherTypeID,
                    ":trackingid"=>$trackingID,
                    ":couponcode"=>$couponCode,
                    ":batchno"=>$batchNo,
                    ":terminalid"=>$terminalID,
                    ":amount"=>$amount,
                    ":dateexpiry"=>$dateExpiry,
                    ":aid"=>$aid,
                    ":source"=>$source,
                    ":loyaltycreditable"=>$loyaltyCreditable
            ));

            $sql->execute();

            $couponID = $this->_connection->getLastInsertID();
            
            try{
                $query = "UPDATE $couponBatchTable SET Status = 2, DateUpdated = now_usec(), UpdatedByAID = :aid
                          WHERE CouponCode = :couponcode";
                
                $sql = $this->_connection->createCommand($query);
                
                $sql->bindValues(array(
                        ":couponcode"=>$couponCode,
                        ":aid"=>$aid
                ));
                
                $sql->execute();
                
                try {
                        
                    $beginTrans->commit();

                    return true;

                } catch(Exception $e) {
                    $beginTrans->rollback();
                    Utilities::log($e->getMessage());
                    return false;
                }
                
            }catch(Exception $e){
                $beginTrans->rollback();
                Utilities::log($e->getMessage());
                return false;
            }
        
        } catch (Exception $e){
            $beginTrans->rollback();
            Utilities::log($e->getMessage());
            return false;
        }
    }
    
    public function isTrackingIDExists($trackingID){
        $query = 'SELECT COUNT(CouponID) ctrtracking FROM coupons WHERE TrackingID = :trackingid';
        $sql = $this->_connection->createCommand($query);
        $sql->bindValues(array(
           ":trackingid"=>$trackingID 
        ));
        
        $result = $sql->queryRow();
        
        return $result['ctrtracking'];
    }
    
    public function verifyCouponTransaction($trackingID){
        $query = 'SELECT COUNT(CouponID) ctrtracking,  LoyaltyCreditable FROM coupons WHERE TrackingID = :trackingid AND Status = 1';
        $sql = $this->_connection->createCommand($query);
        $sql->bindValues(array(
           ":trackingid"=>$trackingID 
        ));
        
        $result = $sql->queryRow();
        
        return $result['ctrtracking'];
    }
    
    /**
     * @author JunJun S. Hernandez
     * @datecreated 10/21/13
     * @param str $date
     * @return object
     */
    public function getAllUsedCouponList($date){
        $datetime = new DateTime($date);
        $datetime->modify('+1 day');
        $vdate = $datetime->format('Y-m-d H:i:s');
        $sql = "SELECT c.CouponID AS VoucherID, c.VoucherTypeID, c.CouponCode AS VoucherCode, 
                c.Status, c.TerminalID, c.Amount, c.DateCreated, c.DateExpiry, c.Source, c.LoyaltyCreditable, st.SiteName
                FROM coupons c INNER JOIN terminals t ON t.TerminalID = c.TerminalID
                INNER JOIN sites st ON st.SiteID = t.SiteID
                WHERE c.DateCreated >= :transdate AND  c.DateCreated < :vtransdate AND c.Status = 3";
        $command = $this->_connection->createCommand($sql);
        $command->bindValue(":transdate", $date);
        $command->bindValue(":vtransdate", $vdate);
        $result = $command->queryAll();
        return $result;
    }
    
    /**
     * @author JunJun S. Hernandez
     * @datecreated 10/21/13
     * @param int $site
     * @param str $date
     * @return object
     */
    public function getUsedCouponListBySite($site, $date){
        $datetime = new DateTime($date);
        $datetime->modify('+1 day');
        $vdate = $datetime->format('Y-m-d H:i:s');
        $sql = "SELECT c.CouponID AS VoucherID, c.VoucherTypeID, c.CouponCode AS VoucherCode, 
                c.Status, c.TerminalID, c.Amount, c.DateCreated, c.DateExpiry, c.Source, c.LoyaltyCreditable, st.SiteName
                FROM coupons c INNER JOIN terminals t ON t.TerminalID = c.TerminalID
                INNER JOIN sites st ON st.SiteID = t.SiteID
                WHERE t.SiteID = '$site' AND c.DateCreated >= :transdate AND c.DateCreated < :vtransdate AND c.Status = 3";
        $command = $this->_connection->createCommand($sql);
        $command->bindValue(":transdate", $date);
        $command->bindValue(":vtransdate", $vdate);
        $result = $command->queryAll();
        return $result;
    }
}

?>
