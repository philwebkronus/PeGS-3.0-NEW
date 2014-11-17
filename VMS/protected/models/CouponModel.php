<?php

/**
 * Description of CouponModel
 *
 * @author elperez
 */
class CouponModel extends CFormModel {

    public $_connection;
    CONST ACCOUNTTYPE_ID_SITE_OPERATOR = 2;
    CONST ACCOUNTTYPE_ID_SITE_SUPERVISOR = 3;
    CONST ACCOUNTTYPE_ID_SITE_CASHIER = 4;

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
    public function insertCoupon($voucherTypeID, $trackingID, $couponCode, $batchNo,
            $terminalID, $amount, $aid, $source, $couponBatchTable, $loyaltyCreditable, $dateExpiry) {

        $beginTrans = $this->_connection->beginTransaction();

        try {
            $query = "INSERT INTO coupons(VoucherTypeID, TrackingID, CouponCode, VoucherBatchID,
                      TerminalID, Amount, DateCreated, DateUsed, CreatedByAID, Source,
                      Status, LoyaltyCreditable, DateExpiry)
                      VALUES(:vouchertypeid, :trackingid, :couponcode, :batchno, :terminalid,
                             :amount, NOW(6), NOW(6), :aid, :source, 3, :loyaltycreditable,
                             :dateexpiry)";

            $sql = $this->_connection->createCommand($query);

            $sql->bindValues(array(
                ":vouchertypeid" => $voucherTypeID,
                ":trackingid" => $trackingID,
                ":couponcode" => $couponCode,
                ":batchno" => $batchNo,
                ":terminalid" => $terminalID,
                ":amount" => $amount,
                ":dateexpiry" => $dateExpiry,
                ":aid" => $aid,
                ":source" => $source,
                ":loyaltycreditable" => $loyaltyCreditable
            ));

            $sql->execute();

            $couponID = $this->_connection->getLastInsertID();

            try {
                $query = "UPDATE $couponBatchTable SET Status = 2, DateUpdated = NOW(6), UpdatedByAID = :aid
                          WHERE CouponCode = :couponcode";

                $sql = $this->_connection->createCommand($query);

                $sql->bindValues(array(
                    ":couponcode" => $couponCode,
                    ":aid" => $aid
                ));

                $sql->execute();

                try {

                    $beginTrans->commit();

                    return true;
                } catch (Exception $e) {
                    $beginTrans->rollback();
                    Utilities::log($e->getMessage());
                    return false;
                }
            } catch (Exception $e) {
                $beginTrans->rollback();
                Utilities::log($e->getMessage());
                return false;
            }
        } catch (Exception $e) {
            $beginTrans->rollback();
            Utilities::log($e->getMessage());
            return false;
        }
    }

    public function isTrackingIDExists($trackingID) {
        $query = 'SELECT COUNT(CouponID) ctrtracking FROM coupons WHERE TrackingID = :trackingid';
        $sql = $this->_connection->createCommand($query);
        $sql->bindValues(array(
            ":trackingid" => $trackingID
        ));

        $result = $sql->queryRow();

        return $result['ctrtracking'];
    }

    public function verifyCouponTransaction($trackingID) {
        $query = 'SELECT COUNT(CouponID) ctrtracking,  IsCreditable FROM coupons WHERE TrackingID = :trackingid';
        $sql = $this->_connection->createCommand($query);
        $sql->bindValues(array(
            ":trackingid" => $trackingID
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
    public function getCouponDetails($trackingID) {
        $query = 'SELECT CouponCode, Amount, ValidFromDate, ValidToDate, IsCreditable FROM coupons WHERE TrackingID = :trackingid';
        $sql = $this->_connection->createCommand($query);
        $sql->bindValues(array(
            ":trackingid" => $trackingID
        ));

        $result = $sql->queryAll();
        return $result;
    }

    /**
     * @author JunJun S. Hernandez
     * @datecreated 10/21/13
     * @param str $date
     * @return array
     */
    public function getAllUsedCouponList($date) {
        if (($_SESSION['AccountType'] == self::ACCOUNTTYPE_ID_SITE_OPERATOR) ||
                ($_SESSION['AccountType'] == self::ACCOUNTTYPE_ID_SITE_SUPERVISOR) ||
                ($_SESSION['AccountType'] == self::ACCOUNTTYPE_ID_SITE_CASHIER)) {
            //get kronus database name
            $kronusConnString = Yii::app()->db2->connectionString;
            $dbnameresult = explode(";", $kronusConnString);
            $dbname = str_replace("dbname=", "", $dbnameresult[1]);

            $datetime = new DateTime($date);
            $datetime->modify('+1 day');
            $vdate = $datetime->format('Y-m-d H:i:s');
            $sql = "SELECT c.CouponID AS VoucherID, '2' AS VoucherTypeID, st.SiteName, c.CouponCode AS VoucherCode,
                c.Status, c.TerminalID, c.Amount, c.DateCreated, c.ValidToDate, c.IsCreditable, st.SiteName,
                c.DateUpdated
                FROM coupons c
                INNER JOIN $dbname.terminals t ON t.TerminalID = c.TerminalID
                INNER JOIN $dbname.sites st ON st.SiteID = t.SiteID
                INNER JOIN siteaccounts sa ON sa.SiteID = st.SiteID
                INNER JOIN accounts a ON a.AID = sa.AID
                WHERE c.DateUpdated >= :transdate AND  c.DateUpdated < :vtransdate AND c.Status = 3
                AND a.AccountTypeID = :account_type_id AND a.AID = :aid
                ORDER BY c.DateUpdated DESC";
            $command = $this->_connection->createCommand($sql);
            $command->bindValue(":account_type_id", $_SESSION['AccountType']);
            $command->bindValue(":aid", $_SESSION['AID']);
        } else {
            //get kronus database name
            $kronusConnString = Yii::app()->db2->connectionString;
            $dbnameresult = explode(";", $kronusConnString);
            $dbname = str_replace("dbname=", "", $dbnameresult[1]);

            $datetime = new DateTime($date);
            $datetime->modify('+1 day');
            $vdate = $datetime->format('Y-m-d H:i:s');
            $sql = "SELECT c.CouponID AS VoucherID, '2' AS VoucherTypeID, st.SiteName, c.CouponCode AS VoucherCode,
                c.Status, c.TerminalID, c.Amount, c.DateCreated, c.ValidToDate, c.IsCreditable, st.SiteName,
                c.DateUpdated
                FROM coupons c
                INNER JOIN $dbname.terminals t ON t.TerminalID = c.TerminalID
                INNER JOIN $dbname.sites st ON st.SiteID = t.SiteID
                WHERE c.DateUpdated >= :transdate AND  c.DateUpdated < :vtransdate AND c.Status = 3
                ORDER BY c.DateUpdated DESC";
            $command = $this->_connection->createCommand($sql);
        }
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
    public function getUsedCouponListBySite($site, $date) {

        //get kronus database name
        $kronusConnString = Yii::app()->db2->connectionString;
        $dbnameresult = explode(";", $kronusConnString);
        $dbname = str_replace("dbname=", "", $dbnameresult[1]);

        $datetime = new DateTime($date);
        $datetime->modify('+1 day');
        $vdate = $datetime->format('Y-m-d H:i:s');
        $sql = "SELECT c.CouponID AS VoucherID, '2' AS VoucherTypeID, st.SiteName, c.CouponCode AS VoucherCode,
                c.Status, c.TerminalID, c.Amount, c.DateCreated, c.ValidToDate, c.IsCreditable, st.SiteName,
                c.DateUpdated
                FROM coupons c INNER JOIN $dbname.terminals t ON t.TerminalID = c.TerminalID
                INNER JOIN $dbname.sites st ON st.SiteID = t.SiteID
                WHERE t.SiteID = '$site' AND c.DateUpdated >= :transdate
                AND c.DateUpdated < :vtransdate AND c.Status = 3
                ORDER BY c.DateUpdated DESC";
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
        $command->bindValues(array(':couponCode' => $couponCode));
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
    public function usedCoupon($siteID, $terminalID, $mid, $aid, $trackingID, $couponCode) {
        $sql = "UPDATE coupons SET SiteID = :siteid, TerminalID = :terminalid, MID = :mid,
                DateUpdated = NOW(6), UpdatedByAID = :aid, TrackingID = :trackingid,
                Status = 3
                WHERE CouponCode = :couponcode AND Status = 1";
        $command = $this->_connection->createCommand($sql);
        $command->bindValues(array(':siteid' => $siteID,
            ':terminalid' => $terminalID,
            ':mid' => $mid,
            ':aid' => $aid,
            ':trackingid' => $trackingID,
            ':couponcode' => $couponCode));
        return $command->execute();
    }

    /**
     * @author JunJun S. Hernandez
     * @datecreated 10/23/13
     * @param str $voucherTicketBarcode
     * @return object
     */
    public function getTerminalIDByCouponCode($voucherTicketBarcode) {
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
    public function getAIDByCouponCode($voucherTicketBarcode) {
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
    public function getCouponIDByCodeAndSource($terminalID, $amount, $couponStatus) {
        $sql = "SELECT CouponID FROM coupons WHERE TerminalID = :terminal_id
                AND Amount = :amount AND Status = :coupon_status";
        $command = $this->_connection->createCommand($sql);
        $command->bindValue(":terminal_id", $terminalID);
        $command->bindValue(":amount", $amount);
        $command->bindValue(":coupon_status", $couponStatus);
        $result = $command->queryAll();

        if (isset($result[0]['CouponID'])) {
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
    public function updateCouponStatus($couponID, $statusUsed) {
        $beginTrans = $this->_connection->beginTransaction();
        try {
            $query = "UPDATE coupons SET Status = $statusUsed WHERE CouponID = $couponID";
            $sql = $this->_connection->createCommand($query);
            $sql->execute();

            try {
                $beginTrans->commit();
                return true;
            } catch (Exception $e) {
                $beginTrans->rollback();
                Utilities::log($e->getMessage());
                return false;
            }
        } catch (Exception $e) {
            $beginTrans->rollback();
            Utilities::log($e->getMessage());
            return false;
        }
    }

    /**
     * Get Voucher Info
     * @param int $batch
     * @return array
     * @author Mark Kenneth Esguerra
     * @date November 4, 2013
     */
    public function getVoucherInfo($batch) {
        $connection = Yii::app()->db;

        $query = "SELECT IsCreditable,
                         ValidFromDate,
                         ValidToDate
                  FROM coupons
                  WHERE CouponBatchID = :batch
                  LIMIT 0, 1";
        $command = $connection->createCommand($query);
        $command->bindParam(":batch", $batch);
        $result = $command->queryRow();

        return $result;
    }

    /**
     * Regenerate Coupons
     * @param int $amount Amount of each Coupon
     * @param int $count Remaining no. of coupons to generate
     * @param int $couponbatch BatchID of Coupon
     * @param int $iscreditable Yes | No
     * @param int $user AID of the user
     * @return array TransCode and TransMsg
     * @author Mark Kenneth Esguerra
     * @date November 5, 2013
     */
    public function regenerateCoupons($amount, $count, $couponbatch, $iscreditable, $user, $status, $validfrom, $validto) {
        $model = new GenerationToolModel();

        $connection = Yii::app()->db;

        $pdo = $connection->beginTransaction();
        //Generate Remaining no. of coupons
        for ($i = 0; $count > $i; $i++) {
            //Generate Coupon Code
            $code = "";
//            for ($x = 0; 6 > $x; $x++)
//            {
//                $num = mt_rand(1, 36);
//                $code .= $model->getRandValue($num);
//            }
            $code = $model->mt_rand_str(6);
            $couponcode = "C" . $code;

            try {
                $secondquery = "INSERT INTO coupons (CouponBatchID,
                                                     CouponCode,
                                                     Amount,
                                                     Status,
                                                     DateCreated,
                                                     CreatedByAID,
                                                     IsCreditable,
                                                     ValidFromDate,
                                                     ValidToDate
                                ) VALUES (:couponbatch,
                                          :couponcode,
                                          :amount,
                                          :status,
                                          NOW(6),
                                          :aid,
                                          :iscreditable,
                                          :validfrom,
                                          :validto
                                )";
                $sql = $connection->createCommand($secondquery);
                $sql->bindParam(":couponbatch", $couponbatch);
                $sql->bindParam(":couponcode", $couponcode);
                $sql->bindParam(":amount", $amount);
                $sql->bindParam(":aid", $user);
                $sql->bindParam(":iscreditable", $iscreditable);
                $sql->bindParam(":status", $status);
                $sql->bindParam(":validfrom", $validfrom);
                $sql->bindParam(":validto", $validto);

                $secondresult = $sql->execute();
                if ($secondresult > 0) {
                    continue;
                } else {
                    $pdo->rollback();
                    return array('TransCode' => 0,
                        'TransMsg' => 'An error occured while regenerating the coupons [0001]');
                }
            } catch (CDbException $e) {
                //Check if error is 'Duplicate Key constraints violation'
                $errcode = $e->getCode();
                if ($errcode == 23000) {
                    try {
                        $pdo->commit();
                        //get total coupon count
                        $totalcoupon = "SELECT CouponCount FROM couponbatch WHERE CouponBatchID = :couponbatchID";
                        $sql = $connection->createCommand($totalcoupon);
                        $sql->bindParam(":couponbatchID", $couponbatch);
                        $totalcouponcount = $sql->queryRow();
                        //get generated coupons after duplication
                        $querycount = "SELECT COUNT(CouponID) as GeneratedCoupon FROM coupons
                                   WHERE CouponBatchID = :couponbatch";

                        $sql = $connection->createCommand($querycount);
                        $sql->bindParam(":couponbatch", $couponbatch);
                        $couponcount = $sql->queryRow();
                        $remainingcoupon = (int) $totalcouponcount['CouponCount'] - (int) $couponcount['GeneratedCoupon'];

                        return array('TransCode' => 2,
                            'TransMsg' => 'Coupon already exist. There are ' . $remainingcoupon . ' remaining coupons
                                         to generate. Click Retry to continue',
                            'CouponBatchID' => $couponbatch,
                            'RemainingCoupon' => $remainingcoupon,
                            'Amount' => $amount,
                            'IsCreditable' => $iscreditable,
                            'Status' => $status,
                            'ValidFrom' => $validfrom,
                            'ValidTo' => $validto);

                    } catch (CDbException $e) {
                        $pdo->rollback();
                        return array('TransCode' => 0,
                            'TransMsg' => 'An error occured while regenerating the coupons [0002]');
                    }
                } else {
                    $pdo->rollback();
                    return array('TransCode' => 0, 'TransMsg' => 'An error occured while regenerating the coupons [0003]');
                }
            }
        }
        try {
            $pdo->commit();

            AuditLog::logTransactions(31, " - Generate Coupons");
            return array('TransCode' => 1,
                'TransMsg' => 'Coupons successfully generated');
        } catch (PDOException $e) {
            $pdo->rollback();
            return array('TransCode' => 0,
                'TransMsg' => 'An error occured while regenerating the coupons [0004]');
        }
    }

    /**
     * @author JunJun S. Hernandez
     * @datecreated 10/22/13
     * @param str $voucherTicketBarcode
     * @param str $siteID
     * @return array
     */
    public function getCouponDataByCodeAndSiteID($voucherTicketBarcode, $siteID) {
        $sql = "SELECT CouponCode, TerminalID, TrackingID, Amount, DateCreated, CreatedByAID,  IsCreditable, Status FROM coupons WHERE CouponCode = :voucherTicketBarcode AND SiteID = :site_id";
        $command = $this->_connection->createCommand($sql);
        $command->bindValue(":voucherTicketBarcode", $voucherTicketBarcode);
        $command->bindValue(":site_id", $siteID);
        $result = $command->queryAll();
        return $result;
    }

    /**
     * @author JunJun S. Hernandez
     * @datecreated 10/22/13
     * @param str $trackingID
     * @return array
     */
    public function getCouponDetailsByTrackingID($trackingID) {
        $sql = "SELECT CouponCode, TerminalID, TrackingID, Amount, DateCreated, CreatedByAID,  IsCreditable, Status FROM coupons WHERE TrackingID = :tracking_id";
        $command = $this->_connection->createCommand($sql);
        $command->bindValue(":tracking_id", $trackingID);
        $result = $command->queryAll();
        return $result;
    }

    /**
     * @author JunJun S. Hernandez
     * @datecreated 10/22/13
     * @param str $voucherTicketBarcode
     * @return array
     */
    public function getCouponDataByCodeAndTerminalIDAndAID($voucherTicketBarcode, $terminalID, $aid) {
        $sql = "SELECT CouponCode, TerminalID, TrackingID, Amount, DateCreated, CreatedByAID, IsCreditable, Status
                FROM coupons
                WHERE CouponCode = :voucherTicketBarcode AND TerminalID = :terminal_id AND CreatedByAID = :aid";
        $command = $this->_connection->createCommand($sql);
        $command->bindValue(":voucherTicketBarcode", $voucherTicketBarcode);
        $command->bindValue(":terminal_id", $terminalID);
        $command->bindValue(":aid", $aid);
        $result = $command->queryAll();
        return $result;
    }

    /**
     * @author JunJun S. Hernandez
     * @datecreated 10/22/13
     * @param str $voucherTicketBarcode
     * @return array
     */
    public function getCouponDataByTrackingIdAndAID($trackingid, $aid) {
        $sql = "SELECT CouponCode, TerminalID, TrackingID, Amount, DateCreated, CreatedByAID, IsCreditable, Status
                FROM coupons
                WHERE TrackingID = :tracking_id AND CreatedByAID = :aid";
        $command = $this->_connection->createCommand($sql);
        $command->bindValue(":tracking_id", $trackingid);
        $command->bindValue(":aid", $aid);
        $result = $command->queryAll();
        return $result;
    }

    /**
     * @author JunJun S. Hernandez
     * @datecreated 10/22/13
     * @param str $trackingID
     * @return object
     */
    public function getCouponCodeByTrackingId($trackingID) {
        $sql = "SELECT CouponCode FROM coupons WHERE TrackingID = :tracking_id";
        $command = $this->_connection->createCommand($sql);
        $command->bindValue(":tracking_id", $trackingID);
        $result = $sql->queryRow();

        if ($result != '') {
            return $result['CouponCode'];
        } else {
            return 0;
        }
    }

    /**
     * @author JunJun S. Hernandez
     * @datecreated 10/22/13
     * @param int $terminalID
     * @param int $AID
     * @param int $status
     * @return array
     */
    public function getCouponDataByTeminalIDAIDAndStatus($terminalID, $aid, $status) {
        $sql = "SELECT CouponID, CouponCode, Amount, DateCreated, ValidToDate
                FROM coupons
                WHERE TerminalID = :terminal_id AND CreatedByAID = :aid AND Status = :status AND  Amount IS NULL";
        $command = $this->_connection->createCommand($sql);
        $command->bindValues(array(":terminal_id" => $terminalID, ":aid" => $aid, ":status" => $status));
        $result = $command->queryAll();
        return $result;
    }

    /**
     * @author JunJun S. Hernandez
     * @datecreated 10/22/13
     * @param int $couponID
     * @param int $siteID
     * @param int $terminalID
     * @param int $mid
     * @param float $amount
     * @param string $dateUpdated
     * @param int $updatedByAID
     * @param string $validFromDate
     * @param string $validToDate
     * @param int $trackingID
     * @return boolean success | failed transaction
     */
    public function updateCouponData($couponID, $siteID, $terminalID, $mid, $amount, $dateUpdated, $updatedByAID, $validFromDate, $validToDate, $trackingID) {

        $beginTrans = $this->_connection->beginTransaction();
        try {
            $query = "UPDATE coupons SET SiteID = :site_id, TerminalID = :terminal_id,
                                             MID = :mid, Amount = :amount,
                                             DateUpdated = :date_updated,
                                             UpdatedByAID = :updated_by_aid, ValidFromDate = :valid_from_date,
                                             ValidToDate = :valid_to_date, TrackingID = :tracking_id
                                             WHERE CouponID = :coupon_id";
            $sql = $this->_connection->createCommand($query);

            $sql->bindValues(array(
                ":site_id" => $siteID,
                ":terminal_id" => $terminalID,
                ":mid" => $mid,
                ":amount" => $amount,
                ":date_updated" => $dateUpdated,
                ":updated_by_aid" => $updatedByAID,
                ":valid_from_date" => $validFromDate,
                ":valid_to_date" => $validToDate,
                ":tracking_id" => $trackingID,
                ":coupon_id" => $couponID
            ));
            $sql->execute();

            try {
                $beginTrans->commit();
                return true;
            } catch (Exception $e) {
                $beginTrans->rollback();
                Utilities::log($e->getMessage());
                return false;
            }
        } catch (Exception $e) {
            $beginTrans->rollback();
            Utilities::log($e->getMessage());
            return false;
        }
    }

    /**
     * @author JunJun S. Hernandez
     * @datecreated 10/22/13
     * @param int $terminalID
     * @param int $AID
     * @param int $status
     * @return array
     */
    public function getCouponDataByStatus($status) {
        $sql = "SELECT CouponID, CouponCode, DateCreated, ValidFromdate, ValidToDate
                FROM coupons
                WHERE Status = :status AND  Amount IS NULL";
        $command = $this->_connection->createCommand($sql);
        $command->bindValues(array(":status" => $status));
        $result = $command->queryAll();
        return $result;
    }
    /**
     * Search coupons based selected search fields
     * @param string $wherefx WHERE function for the query.
     * @param array $search array of FieldID and its Value
     * @return array Coupon batch details
     * @author Mark Kenneth Esguerra
     * @date June 18, 2014
     */
    public function searchCouponBatch($wherefx, $search, $start = null, $limit = null)
    {

        $pagination = "";
        if (!is_null($start) && !is_null($limit))
        {
            $pagination = "LIMIT $start, $limit";
        }

        $query = "SELECT cb.CouponBatchID,
                         c.ValidFromDate,
                         c.ValidToDate,
                         c.IsCreditable,
                         cb.CouponCount,
                         cb.Amount,
                         cb.DistributionTagID,
                         cb.Status,
                         cb.DateCreated,
                         cb.CreatedByAID,
                         cb.DateUpdated,
                         cb.UpdatedByAID,
                         cb.PromoName
                  FROM coupons c
                  INNER JOIN couponbatch cb ON cb.CouponBatchID = c.CouponBatchID
                  $wherefx
                  GROUP BY cb.CouponBatchID
                  $pagination
                  ";
        $command = $this->_connection->createCommand($query);
        //bind value of search fields
        foreach ($search as $s)
        {
            switch ($s['FieldID'])
            {
                case 1:
                    $command->bindValue(":amount", $s['Value']);
                    break;
                case 2:
                    $command->bindValue(":distribtag", $s['Value']);
                    break;
                case 3:
                    $command->bindValue(":creditable", $s['Value']);
                    break;
                case 4:
                    $command->bindValue(":generatedfrom", $s['Value']." 00:00:00");
                    break;
                case 5:
                    $command->bindValue(":generatedto", $s['Value']." 23:59:59");
                    break;
                case 6:
                    $command->bindValue(":aid", $s['Value']);
                    break;
                case 7:
                    $command->bindValue(":validfrom", $s['Value']);
                    break;
                case 8:
                    $command->bindValue(":validto", $s['Value']);
                    break;
                case 9:
                    $command->bindValue(":status", $s['Value']);
                    break;
                case 10:
                    $command->bindValue(":promoname", $s['Value']);
                    break;
            }
        }
        $result = $command->queryAll();
        return $result;
    }
    /**
     * Get Coupons by Batch
     * @param int $batchID Batch ID
     * @return array Arrays of coupons
     */
    public function getCouponsByBatchID($batchID, $start = null, $limit = null)
    {
        $pagination = null;
        if (!is_null($start) && !is_null($limit))
        {
            $pagination = "LIMIT $start, $limit";
        }
        $sql = "SELECT c.CouponID,
                       c.CouponBatchID,
                       c.CouponCode,
                       c.Amount,
                       cb.DistributionTagID,
                       c.IsCreditable,
                       c.DateCreated,
                       c.DateUpdated,
                       ad.Name  `CreatedBy`,
                       c.ValidFromDate,
                       c.ValidToDate,
                       c.SiteID,
                       c.TerminalID,
                       c.Status,
                       cb.PromoName,
                       c.DateReimbursed,
                       c.ReimbursedByAID
                FROM coupons c
                INNER JOIN couponbatch cb ON cb.CouponBatchID = c.CouponBatchID
		LEFT JOIN npos.accountdetails ad ON cb.CreatedByAID = ad.AID
                WHERE c.CouponBatchID = :batchID
                $pagination";
        $command = $this->_connection->createCommand($sql);
        $command->bindValue(":batchID", $batchID);
        $result = $command->queryAll();

        return $result;
    }
    public function searchCoupons($wherefx, $search, $start = null, $limit = null)
    {
        $pagination = "";
        if (!is_null($start) && !is_null($limit))
        {
            $pagination = "LIMIT $start, $limit";
        }
        $sql = "SELECT c.CouponID, 
                       c.CouponBatchID, 
                       c.CouponCode, 
                       c.Amount, 
                       cb.DistributionTagID, 
                       c.IsCreditable, 
                       c.DateCreated, 
                       c.DateUpdated, 
                       ad.Name  `CreatedBy`,
                       cb.PromoName, 
                       c.ValidFromDate, 
                       c.ValidToDate, 
                       c.SiteID, 
                       c.TerminalID, 
                       c.Status, 
                       c.DateReimbursed, 
                       c.ReimbursedByAID 
                FROM coupons c 
                INNER JOIN couponbatch cb ON cb.CouponBatchID = c.CouponBatchID 
		LEFT JOIN npos.accountdetails ad ON cb.CreatedByAID = ad.AID
                $wherefx 
                $pagination";
        $command = $this->_connection->createCommand($sql);
        //bind value of search fields
        foreach ($search as $s)
        {
            switch ($s['FieldID'])
            {
                case 0:
                    $command->bindValue(":batchID", $s['Value']);
                    break;
                case 1:
                    $command->bindValue(":couponcode", $s['Value']);
                    break;
                case 2:
                    $command->bindValue(":status", $s['Value']);
                    break;
                case 3:
                    $command->bindValue(":transdatefrom", $s['Value']." 00:00:00");
                    $command->bindValue(":transdateto", $s['Value']." 23:59:59");
                    break;
                case 4:
                    $command->bindValue(":site", $s['Value']);
                    break;
                case 5:
                    $command->bindValue(":terminal", $s['Value']);
                    break;
                case 6:
                    $command->bindValue(":source", $s['Value']);
                    break;
                case 7:
                    $command->bindValue(":promoname", $s['Value']);
                    break;
            }
        }
        $result = $command->queryAll();

        return $result;
    }
    /**
     * Get Coupons By Code
     * @param type $couponcode
     * @return type
     */
    public function getCouponsByCode($batchID, $couponcode)
    {
        $sql = "SELECT c.CouponID, 
                       c.CouponBatchID, 
                       c.CouponCode, 
                       c.Amount, 
                       cb.DistributionTagID, 
                       c.IsCreditable, 
                       c.DateCreated, 
                       c.DateUpdated, 
                       ad.Name  `CreatedBy`,
                       c.ValidFromDate, 
                       c.ValidToDate, 
                       c.SiteID, 
                       c.TerminalID, 
                       c.Status, 
                       cb.PromoName, 
                       c.DateReimbursed, 
                       c.ReimbursedByAID 
                FROM coupons c 
                INNER JOIN couponbatch cb ON cb.CouponBatchID = c.CouponBatchID 
                LEFT JOIN npos.accountdetails ad ON cb.CreatedByAID = ad.AID
                WHERE c.CouponBatchID = :batchID AND CouponCode = :couponcode";
        $command = $this->_connection->createCommand($sql);
        $command->bindValue(":batchID", $batchID);
        $command->bindValue(":couponcode", $couponcode);
        $result = $command->queryAll();
        
        return $result;
    }
    public function getValidityOfCoupon($batchID)
    {
        $sql = "SELECT cb.Status, c.ValidFromDate, c.ValidToDate
                FROM coupons c
                INNER JOIN couponbatch cb ON cb.CouponBatchID = c.CouponBatchID
                WHERE c.CouponBatchID = :batchID AND
                c.Status = 1
                LIMIT 0, 1";
        $command = $this->_connection->createCommand($sql);
        $command->bindValue(":batchID", $batchID);
        $result = $command->queryRow();

        return $result;
    }
}
?>
