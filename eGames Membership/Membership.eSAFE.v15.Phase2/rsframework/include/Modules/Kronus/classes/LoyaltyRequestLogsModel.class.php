<?php

class LoyaltyRequestLogsModel extends BaseEntity {

    function LoyaltyRequestLogsModel() {
        $this->TableName = "loyaltyrequestlogs";
        $this->ConnString = "kronus";
        $this->Identity = "LoyaltyRequestLogID";
        $this->DatabaseType = DatabaseTypes::PDO;
    }

    public function insertLogs($mid, $trans_type, $transdate, $amount, $isCreditable = "") {

        $this->StartTransaction();
        try {
            $arrLogs['MID'] = $mid;
            $arrLogs['DateCreated'] = $transdate;
            $arrLogs['TransactionType'] = $trans_type;
            $arrLogs['TransactionOrigin'] = 1;
            $arrLogs['TerminalID'] = null;
            $arrLogs['Amount'] = $amount;
            $arrLogs['TransactionDetailsID'] = null;
            $arrLogs['PaymentType'] = 1;
            $arrLogs['IsCreditable'] = $isCreditable;
            $arrLogs['Status'] = 0;

            $this->Insert($arrLogs);

            if (!App::HasError()) {
                $this->CommitTransaction();
                return $this->LastInsertID;
            } else {
                $this->RollBackTransaction();
                return false;
            }
        } catch (Exception $e) {
            $this->RollBackTransaction();
            App::SetErrorMessage($e->getMessage());
        }
    }

    public function updateLoyaltyRequestLogs($loyaltyrequestlogID, $status) {
        $query = "UPDATE loyaltyrequestlogs SET Status = $status, DateUpdated = now(6) WHERE LoyaltyRequestLogID = $loyaltyrequestlogID ";
        return parent::ExecuteQuery($query);
    }

}

?>
