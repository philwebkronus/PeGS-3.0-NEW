<?php
/**
* @Description: Use For Manipulating Table smsrequestlogs
* @Author: aqdepliyan
* @DateCreated: 2013-08-29
*/

Class SMSRequestLogs extends BaseEntity {
    
    const COUPON_REDEMPTION = 1;
    const ITEM_REDEMPTION = 2;
    const PLAYER_REGISTRATION = 3;
    
    public function SMSRequestLogs() {
        $this->TableName = "smsrequestlogs";
        $this->ConnString = "rewardsdb";
        $this->Identity = "SMSRequestLogID";
        $this->DatabaseType = DatabaseTypes::PDO;
    }
    
    /**
     * @Description: for fetching SMS Template ID from database using Method ID
     * @param int $methodid
     * @return int
     */
    public function getSMSMethodTemplateID($methodid){
        $query = "SELECT SMSTemplateID FROM ".$this->GetDBName().".ref_smsapimethods WHERE SMSMethodID = ".$methodid;
        $result = parent::RunQuery($query);
        if(is_array($result) && isset($result[0])){
            return $result[0]['SMSTemplateID'];
        } else { return $result = 0; }
    }
    
    /**
     * @Description: for inserting SMS request logs to database
     * @param int $methodid
     * @param string $mobileno
     * @param date $datecreated
     * @param string $couponseries
     * @param string $refno
     * @param int $itemcount
     * @param string $trackingid
     * @return int
     */
    public function insertSMSRequestLogs($methodid,$mobileno, $datecreated, $couponseries='',$refno='', $itemcount='', $trackingid=''){
        $arrEntries["SMSMethodID"] = $methodid;
        $arrEntries["CouponNo"] = $couponseries;
        $arrEntries["ReferenceNo"] = $refno;
        $arrEntries["RedeemedNo"] = $itemcount;
        $arrEntries["TrackingID"] = $trackingid;
        $arrEntries["MobileNo"] = $mobileno;
        $arrEntries["DateCreated"] = $datecreated;
        
        $retval = parent::Insert($arrEntries);
        if ($this->HasError && $retval == ""){
            App::SetErrorMessage($this->getError());
        }
        return $retval;
    }
}

?>
