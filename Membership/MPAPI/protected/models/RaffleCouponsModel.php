<?php

/**
 * @author fdlsison
 *
 * @date 07-01-2014
 */
class RaffleCouponsModel {

    public static $_instance = null;
    public $_connection;

    public function __construct() {
        $this->_connection = Yii::app()->db4;
    }

    public static function model() {
        if (self::$_instance == null)
            self::$_instance = new RaffleCouponsModel();
        return self::$_instance;
    }

    //@date 07-01-2014
    //@purpose get all available raffle coupons
    public function getAvailableCoupons($rewardItemID, $couponQuantity) {
        $activeRaffleCoupon = Yii::app()->params['activeRaffleCoupon'];
        $sql = "SELECT RaffleCouponID
                FROM " . $activeRaffleCoupon . "
                WHERE Status = 0 AND RewardItemID = :RewardItemID
                ORDER BY CouponNumber
                LIMIT " . $couponQuantity;
        $param = array(':RewardItemID' => $rewardItemID);
        $command = $this->_connection->createCommand($sql);
        $command->bindValues($param);
        $result = $command->queryAll();

        return $result;
    }

    //@purpose updating of raffle coupon status
    public function updateRaffleCouponsStatus($quantity, $couponRedemptionLogID, $rewardItemID, $updatedbyaid) {
        $activeRaffleCoupon = Yii::app()->params['activeRaffleCoupon'];

        $unlockedmsg = '';
        $resultmsg = '';
        $returningarray = array();
        //Status Code 1-Error in locking, 2-Error in unlocking, 3-Error in updating, 4-serialcode unavailable

        $startTrans = $this->_connection->beginTransaction();

        try {
            $sql = "LOCK TABLES " . $activeRaffleCoupon . " WRITE";
            $command = $this->_connection->createCommand($sql);
            $isLocked = $command->execute();

            try {
                //Check if the available coupon is greater than or match with the quantity avail by the player.
                $availableCoupon = $this->getAvailableCoupons($rewardItemID, $quantity);
                if (count($availableCoupon) == $quantity) {
                    try {
                        //Proceed with update query if the table is already locked.
                        $sql = "UPDATE " . $activeRaffleCoupon . " SET CouponRedemptionLogID = :couponRedemptionLogID, Status = 1, UpdatedByAID = :updatedbyaid, DateUpdated = NOW(6)
                                        WHERE CouponRedemptionLogID IS NULL AND Status = 0 AND RewardItemID = :rewardItemID ORDER BY CouponNumber ASC LIMIT " . $quantity; //.$quantity;//.$quantity;
                        $param = array(':couponRedemptionLogID' => $couponRedemptionLogID, ':updatedbyaid' => $updatedbyaid, ':rewardItemID' => $rewardItemID);
                        $command = $this->_connection->createCommand($sql);
                        $command->bindValues($param);
                        $result = $command->execute();

                        try {
                            //unlock table if the update query succeeds
                            $sql5 = 'UNLOCK TABLES';
                            $command5 = $this->_connection->createCommand($sql5);
                            $isUnlocked = $command5->execute();
                            try {
                                $startTrans->commit();
                                $returningarray["IsSuccess"] = true;
                                $returningarray["StatusCode"] = $result;
                                return $returningarray;
                            } catch (PDOException $e) {
                                $startTrans->rollback();
                                Utilities::log($e->getMessage());
                                $unlockedmsg = 'Failed to unlock tables';
                                $returningarray["IsSuccess"] = $isUnlocked;
                                $returningarray["StatusCode"] = 2;
                                return $returningarray;
                            }
                        } catch (PDOException $e) {
                            $resultmsg = 'Failed to update rafflecoupons';

                            //unlock the table if update query failed
                            $sql4 = 'UNLOCK TABLES';
                            $command4 = $this->_connection->createCommand($sql4);
                            $isUnlocked = $command4->execute();
                            if ($isUnlocked == 0 || $isUnlocked == FALSE)
                                $unlockedmsg = 'Failed to unlock tables';
                            $result == 0 ? $returnvalue = 3 : $returnvalue = 2;
                            $returningarray["IsSuccess"] = false;
                            $returningarray["StatusCode"] = $returnvalue;
                            return $returningarray;
                        }
                    } catch (Exception $e) {

                        $startTrans->rollback();
                        Utilities::log($e->getMessage());
                        return 0;
                    }
                } else {
                    //unlock the table if the update query succeeds
                    $sql2 = 'UNLOCK TABLES';
                    $command2 = $this->_connection->createCommand($sql2);
                    $isUnlocked = $command2->execute();

                    if ($isUnlocked == 0 || $isUnlocked == FALSE) {
                        $unlockedmsg = $e->getMessage();
                        $returningarray["IsSuccess"] = $isUnlocked;
                        $returningarray["StatusCode"] = 2;
                        return $returningarray;
                    }
                    $returningarray["IsSuccess"] = false;
                    $returningarray["StatusCode"] = 4;
                    return $returningarray;
                }
            } catch (PDOException $e) {

                $startTrans->rollback();
                Utilities::log($e->getMessage());
                $returningarray["IsSuccess"] = $isLocked;
                $returningarray["StatusCode"] = 1;
                return $returningarray;
            }
        } catch (Exception $e) {

            $startTrans->rollback();
            Utilities::log($e->getMessage());
            return 0;
        }
    }

    public function getCouponRedemptionInfo($couponRedemptionLogID) {
        $activeRaffleCoupon = Yii::app()->params['activeRaffleCoupon'];

        $sql = "SELECT MIN(CouponNumber) MinCouponNumber,
                       MAX(CouponNumber) MaxCouponNumber,
                       COUNT(CouponNumber) CouponNumberCount
                FROM " . $activeRaffleCoupon . "
                WHERE CouponRedemptionLogID = :CouponRedemptionLogID";
        $param = array(':CouponRedemptionLogID' => $couponRedemptionLogID);
        $command = $this->_connection->createCommand($sql);
        $result = $command->queryRow(true, $param);

        return $result;
    }

}