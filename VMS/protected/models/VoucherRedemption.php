<?php

/**
 * @author owliber
 * @date Oct 10, 2012
 * @filename Vouchers.php
 * 
 */
class VoucherRedemption extends CFormModel {

    CONST VOUCHER_STATUS_INACTIVE = 0;
    CONST VOUCHER_STATUS_ACTIVE = 1;
    CONST VOUCHER_STATUS_VOID = 2;
    CONST VOUCHER_STATUS_USED = 3;
    CONST VOUCHER_STATUS_REIMBURSED = 5;
    CONST VOUCHER_STATUS_CLAIMED = 4;
    CONST VOUCHER_STATUS_EXPIRED = 6;
    CONST VOUCHER_STATUS_CANCELLED = 7;
    CONST VOUCHER_TYPE_TICKET = 1;

    public function getVoucherInfo($vouchercode) {
        $query = "SELECT CASE v.VoucherTypeID
                        WHEN 1 THEN 'Payout Ticket'
                        WHEN 2 THEN 'Marketing Voucher'
                    END `VoucherType`,
                    CASE v.Status
                        WHEN 0 THEN 'Inactive'
                        WHEN 1 THEN 'Active'
                        WHEN 2 THEN 'Void'
                        WHEN 3 THEN 'Used'
                        WHEN 4 THEN 'Claimed'
                        WHEN 5 THEN 'Reimbursed'
                        WHEN 6 THEN 'Expired'
                        WHEN 7 THEN 'Cancelled'
                    END `Status`,
                    v.VoucherTypeID AS VoucherTypeCode,
                    v.Status AS StatusCode,
                    v.VoucherCode,
                    v.Amount,
                    v.DateCreated,
                    v.DateClaimed,
                    v.DateExpiry,
                    v.TerminalID,
                    t.TerminalCode,
                    s.SiteName
                FROM vouchers v                    
                    INNER JOIN terminals t ON v.TerminalID = t.TerminalID
                    INNER JOIN sites s ON t.SiteID = s.SiteID
                WHERE v.VoucherCode =:vouchercode";

        $sql = Yii::app()->db->createCommand($query);
        $sql->bindValue(":vouchercode", $vouchercode);
        $result = $sql->queryRow();

        return $result;
    }

    public function verifyVoucher($AID, $vouchercode) {
        $query = "SELECT *
                FROM
                  vouchers v
                INNER JOIN terminals t
                ON v.TerminalID = t.TerminalID
                INNER JOIN sites s
                ON s.SiteID = t.SiteID
                INNER JOIN siteaccounts sa
                ON s.SiteID = sa.SiteID
                WHERE
                  sa.AID = :AID
                  AND v.VoucherCode = :vouchercode
                  AND VoucherTypeID =:vouchertype";

        $sql = Yii::app()->db->createCommand($query);
        $sql->bindValues(array(
            ":AID" => $AID,
            ":vouchercode" => $vouchercode,
            ":vouchertype" => self::VOUCHER_TYPE_TICKET
        ));

        $result = $sql->queryAll();

        if (count($result) > 0)
            return true;
        else
            return false;
    }

    public function redeemVoucher($vouchercode) {

        $conn = Yii::app()->db;

        $trx = $conn->beginTransaction();

        $query = "UPDATE vouchers
                  SET Status =:status,
                      DateClaimed = NOW(6),
                      ClaimedByAID =:aid
                  WHERE VoucherCode =:vouchercode";
        $sql = $conn->createCommand($query);
        $sql->bindValues(array(':status' => self::VOUCHER_STATUS_CLAIMED,
            ':vouchercode' => $vouchercode,
            ':aid' => Yii::app()->user->getId()));
        $result = $sql->execute();

        if ($result == 1) {
            try {
                $trx->commit();
                return 1;
            } catch (Exception $e) {
                $trx->rollback();
                return 0;
            }
        }
    }
    
    public function verifyVoucherExist($voucherCode)
    {
        $query = "SELECT VoucherTypeID FROM vouchers WHERE voucherCode=:voucherCode";
        $sql = Yii::app()->db->createCommand($query);
        $sql->bindValue(":voucherCode", $voucherCode);
        $result = $sql->queryRow();
        
        return $result;
    }

}

?>
