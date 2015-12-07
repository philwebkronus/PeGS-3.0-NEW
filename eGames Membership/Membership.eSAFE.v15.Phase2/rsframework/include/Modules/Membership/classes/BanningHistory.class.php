<?php

/*
* Description: Class for BanningHistory table.
* @author: aqdepliyan
* DateCreated: 2013-06-18 06:59:58PM
*/

class BanningHistory extends BaseEntity
{
    public function BanningHistory()
    {
        $this->TableName = "banninghistory";
        $this->Identity = "BanningID";
        $this->ConnString = "membership";
        $this->DatabaseType = DatabaseTypes::PDO;
    }
    
    public function getRemarks($MID,$status)
    {
        $query = "SELECT Remarks FROM membership.banninghistory
                        WHERE MID = ".$MID." AND Status = ".$status." 
                        ORDER BY DateCreated desc LIMIT 1";
        return parent::RunQuery($query);
    }
    
    public function getMaxBannedDate($MID)
    {
        $query = "SELECT DateCreated FROM $this->TableName
                        WHERE MID = ".$MID." AND Status = 1 
                        ORDER BY DateCreated desc LIMIT 1";
        $result = parent::RunQuery($query);
        if(!empty($result)){
         $res = $result[0]['DateCreated'];
        } else {
         $res = '';
        }
        return $res;
    }
    
    public function getBanningHistoryUsingMemCardID($MemCardID)
    {
        $query = "SELECT MemberCardID, MID, Status, DateCreated, Remarks
                            FROM membership.banninghistory
                            WHERE MemberCardID =".$MemCardID." ORDER BY DateCreated ASC";
        return parent::RunQuery($query);
    }
    /**
     * 
     * @param type $MID
     * @param type $status
     */
    public function getRemarksSP($MID, $status) {
        $query = "CALL membership.sp_select_data(1, 2, 6, '$MID,$status', 'Remarks', @OUTRetCode, @OUTRetMessage, @OUTfldListRet)";
        $result = parent::RunQuery($query);
        if (count($result) > 0) {
            $exp = explode(";", $result[0]['OUTfldListRet']);
            
            return array(0 => array('Remarks' => $exp[0]));
        }
    }
    /**
     * @author Mark Kenneth Esguerra
     * @date June 30, 2015 
     * @param type $entries
     * @param type $MemberCardID
     * @param type $MID
     * @param type $Status
     * @param type $Remarks
     * @param type $DateCreated
     * @param type $CreatedByAID
     * @return string
     */
    public function insertBanningHistory($entries, $MemberCardID, $MID, $Status, $Remarks, $DateCreated, $CreatedByAID) {
        $this->StartTransaction();
        
        $insertBanning = "CALL membership.sp_insert_data2(0, $MemberCardID, $MID, $Status, '$Remarks', $CreatedByAID, Null, Null, Null, Null, Null, Null, @OUT_ResultCode, @OUT_Result, @OUT_ResultID)";
        $result = parent::ExecuteQuery($insertBanning);
        
        if ($result) {
            $_Members = new Members();
            $_Members->updateMemberStatusUsingMID($entries['Status'], $entries['MID']);

            if($entries['Status'] == "5"){
                $status = 9;
            } else {
                $status = strpos($entries['CardNumber'], 'eGames') !== false ? 5:1;
            }

            $_MemberCards = new MemberCards();
            $_MemberCards->updateMemberCardStatusUsingCardNumber($status, $entries['CardNumber']);
            if(!App::HasError()){
                $this->CommitTransaction();
                if($entries['Status'] == "5"){
                    $message = "MID: ".$MID." ,Transaction Successful.";
                    $_AuditTrail = new AuditTrail();
                    $_AuditTrail->StartTransaction();
                    $_AuditTrail->logEvent(AuditFunctions::BAN_PLAYER, $message, array('ID'=>$_SESSION['userinfo']['AID'], 'SessionID'=>$_SESSION['userinfo']['SessionID']));
                    if(!App::HasError()){
                        $_AuditTrail->CommitTransaction();
                        return $message;
                    } else {
                        $message = "Failed to log event on database.";
                        $_AuditTrail->RollBackTransaction();
                        return $message;
                    }
                } else {
                    $message = "MID: ".$MID." ,Transaction Successful.";
                    $_AuditTrail = new AuditTrail();
                    $_AuditTrail->StartTransaction();
                    $_AuditTrail->logEvent(AuditFunctions::UNBAN_PLAYER, $message, array('ID'=>$_SESSION['userinfo']['AID'], 'SessionID'=>$_SESSION['userinfo']['SessionID']));
                    if(!App::HasError()){
                        $_AuditTrail->CommitTransaction();
                        return $message;
                    } else {
                        $message = "Failed to log event on database.";
                        $_AuditTrail->RollBackTransaction();
                        return $message;
                    }
                }

            } else {
                $_BanningHistory->RollBackTransaction();
                $message = "MID: ".$MID." ,Transaction Failed.";
                return $message;
            } 
        }
        
    }
}
?>
