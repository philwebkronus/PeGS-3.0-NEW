<?php

class CompPointsLogsModel extends BaseEntity {

    function CompPointsLogsModel() {
        $this->TableName = "comppointslogs";
        $this->ConnString = "kronus";
        $this->Identity = "CompPointsID";
        $this->DatabaseType = DatabaseTypes::PDO;
    }

    function checkUserMode($serviceid) {
        $query = "SELECT UserMode FROM ref_services WHERE ServiceID = $serviceid";

        $result = parent::RunQuery($query);
        return isset($result['UserMode']) ? $result['UserMode'] : 0;
    }

    function insertLogs($mid, $card_number, $site_id, $service_id, $amount, $trans_date, $trans_type) {
        $this->StartTransaction();
        try {
            $query = "INSERT INTO comppointslogs 
                    (MID,LoyaltyCardNumber, SiteID, ServiceID, Amount, TransactionDate,
                    TransactionType) VALUES ($mid, '$card_number', $site_id, $service_id , $amount , '$trans_date' , '$trans_type')";
            $this->ExecuteQuery($query);

            if (!App::HasError()) {
                $this->CommitTransaction();
                return true;
            } else {
                $this->RollBackTransaction();
                return false;
            }
        } catch (Exception $e) {
            $this->RollBackTransaction();
            App::SetErrorMessage($e->getMessage());
        }


//        $this->StartTransaction();
//        try {
//            $arrLogs['MID'] = $mid;
//            $arrLogs['LoyaltyCardNumber'] = $card_number;
//            $arrLogs['SiteID'] = $trans_type;
//            $arrLogs['ServiceID'] = $service_id;
//            $arrLogs['Amount'] = $amount;
//            $arrLogs['TransactionDate'] = $trans_date;
//            $arrLogs['TransactionType'] = $trans_type;
//
//            $this->Insert($arrLogs);
//
//            if (!App::HasError()) {
//                $this->CommitTransaction();
//                return true;
//            } else {
//                $this->RollBackTransaction();
//                return false;
//            }
//        } catch (Exception $e) {
//            $this->RollBackTransaction();
//            App::SetErrorMessage($e->getMessage());
//        }
    }

}

?>
