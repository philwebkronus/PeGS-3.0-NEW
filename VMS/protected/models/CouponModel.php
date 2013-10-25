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
        $query = 'SELECT COUNT(CouponID) ctrtracking,  IsCreditable FROM coupons WHERE TrackingID = :trackingid';
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
     * @return array
     */
    public function getAllUsedCouponList($date){
        $datetime = new DateTime($date);
        $datetime->modify('+1 day');
        $vdate = $datetime->format('Y-m-d H:i:s');
        $sql = "SELECT c.CouponID AS VoucherID, '2' AS VoucherTypeID, st.SiteName, c.CouponCode AS VoucherCode, 
                c.Status, c.TerminalID, c.Amount, c.DateCreated, c.ValidToDate, c.IsCreditable, st.SiteName,
                c.DateUpdated
                FROM coupons c INNER JOIN terminals t ON t.TerminalID = c.TerminalID
                INNER JOIN sites st ON st.SiteID = t.SiteID
                WHERE c.DateUpdated >= :transdate AND  c.DateUpdated < :vtransdate AND c.Status = 3
                ORDER BY st.SiteID, c.TerminalID, c.DateUpdated";
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
     * @return array
     */
    public function getUsedCouponListBySite($site, $date){
        $datetime = new DateTime($date);
        $datetime->modify('+1 day');
        $vdate = $datetime->format('Y-m-d H:i:s');
        $sql = "SELECT c.CouponID AS VoucherID, '2' AS VoucherTypeID, st.SiteName, c.CouponCode AS VoucherCode, 
                c.Status, c.TerminalID, c.Amount, c.DateCreated, c.ValidToDate, c.IsCreditable, st.SiteName,
                c.DateUpdated
                FROM coupons c INNER JOIN terminals t ON t.TerminalID = c.TerminalID
                INNER JOIN sites st ON st.SiteID = t.SiteID
                WHERE t.SiteID = '$site' AND c.DateUpdated >= :transdate 
                AND c.DateUpdated < :vtransdate AND c.Status = 3
                ORDER BY st.SiteID, c.TerminalID, c.DateUpdated";
        $command = $this->_connection->createCommand($sql);
        $command->bindValue(":transdate", $date);
        $command->bindValue(":vtransdate", $vdate);
        $result = $command->queryAll();
        return $result;
    }
    
    /**
     * @author Edson Perez
     * @purpose checks the available coupon that can be used
     * @param str $couponCode
     * @return object
     */
    public function chkCouponAvailable($couponCode) {
        $sql = "SELECT Status, Amount, IsCreditable, ValidFromDate, ValidToDate, 
                DateCreated FROM coupons  WHERE CouponCode = :couponCode";
        $command = $this->_connection->createCommand($sql);
        $command->bindValues(array(':couponCode'=>$couponCode));
        return $command->queryRow();
    }
    
    /**
     * @author Edson Perez
     * @date 10/24/13
     * @purpose tag coupons as used
     * @param int $siteID
     * @param int $terminalID
     * @param int $mid
     * @param int $aid
     * @param str $trackingID
     * @param str $couponCode
     * @return object
     */
    public function usedCoupon($siteID, $terminalID, $mid, $aid, $trackingID, $couponCode){
        $sql = "UPDATE coupons SET SiteID = :siteid, TerminalID = :terminalid, MID = :mid, 
                DateUpdated = now_usec(), UpdatedByAID = :aid, TrackingID = :trackingid,
                Status = 3
                WHERE CouponCode = :couponcode AND Status = 1";
        $command = $this->_connection->createCommand($sql);
        $command->bindValues(array(':siteid'=>$siteID,
                                   ':terminalid'=>$terminalID,
                                   ':mid'=>$mid,
                                   ':aid'=>$aid,
                                   ':trackingid'=>$trackingID,
                                   ':couponcode'=>$couponCode));
        return $command->execute();
    }
    
    /**
     * @author JunJun S. Hernandez
     * @datecreated 10/23/13
     * @param str $voucherTicketBarcode
     * @return object
     */
    public function getTerminalIDByCouponCode($voucherTicketBarcode){
        $sql = "SELECT TerminalID FROM coupons WHERE CouponCode = :voucherTicketBarcode";
        $command = $this->_connection->createCommand($sql);
        $command->bindValue(":voucherTicketBarcode", $voucherTicketBarcode);
        $result = $command->queryRow();
        
        return $result;
    }
    
    /**
     * @author JunJun S. Hernandez
     * @datecreated 10/23/13
     * @param int $aid
     * @return object
     */
    public function getAIDByCouponCode($voucherTicketBarcode){
        $sql = "SELECT CreatedByAID FROM coupons WHERE CouponCode = :voucherTicketBarcode";
        $command = $this->_connection->createCommand($sql);
        $command->bindValue(":voucherTicketBarcode", $voucherTicketBarcode);
        $result = $command->queryAll();
        return $result;
}

    /**
     * @author JunJun S. Hernandez
     * @datecreated 10/24/13
     * @param int $terminalID
     * @param int $source
     * @param int $amount
     * @return object
     */
    public function getCouponIDByCodeAndSource($terminalID, $amount, $couponStatus){
        $sql = "SELECT CouponID FROM coupons WHERE TerminalID = :terminal_id
                AND Amount = :amount AND Status = :coupon_status";
        $command = $this->_connection->createCommand($sql);
        $command->bindValue(":terminal_id", $terminalID);
        $command->bindValue(":amount", $amount);
        $command->bindValue(":coupon_status", $couponStatus);
        $result = $command->queryAll();
        
        if(isset($result[0]['CouponID'])){
            return $result[0]['CouponID'];
        } else {
            return 0;
        }
    }
    
    /**
     * @author JunJun S. Hernandez
     * @datecreated 10/24/13
     * @param int $couponID
     * @param int $statusUsed
     * @return object
     */
    public function updateCouponStatus($couponID, $statusUsed){
        $beginTrans = $this->_connection->beginTransaction();
        try{
                $query = "UPDATE coupons SET Status = $statusUsed WHERE CouponID = $couponID";
                $sql = $this->_connection->createCommand($query);
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
    }
    
}

?>
