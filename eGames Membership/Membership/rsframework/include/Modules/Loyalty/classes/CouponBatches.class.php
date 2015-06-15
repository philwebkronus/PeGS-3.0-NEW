<?php
/*
* Description: Use For Manipulating Table couponbatches
* @author: aqdepliyan
* DateCreated: 2013-07-15 07:22PM
*/

Class CouponBatches extends BaseEntity {
    
    function CouponBatches() {
        $this->TableName = "couponbatches";
        $this->ConnString = "loyalty";
        $this->Identity = "CouponBatchID";
        $this->DatabaseType = DatabaseTypes::PDO;
    }

}

?>
