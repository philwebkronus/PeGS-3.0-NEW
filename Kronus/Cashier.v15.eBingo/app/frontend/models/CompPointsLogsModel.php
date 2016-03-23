<?php

class CompPointsLogsModel extends MI_Model {

    public function checkUserMode($serviceid) {
        $sql = "SELECT UserMode FROM ref_services WHERE ServiceID = :serviceid";
        $param = array(":serviceid" => $serviceid);
        $this->exec($sql, $param);
        $result = $this->find();

        return isset($result['UserMode']) ? $result['UserMode'] : 0;
    }

    public function insert($mid, $card_number, $terminal_id, $site_id, $service_id, $amount, $trans_date, $trans_type) {
        $beginTrans = $this->beginTransaction();
        try {
            
            $stmt = $this->dbh->prepare("INSERT INTO comppointslogs (MID,
                    LoyaltyCardNumber, TerminalID, SiteID, ServiceID, Amount, TransactionDate,
                    TransactionType) VALUES (:mid,
                    :card_number, :terminal_id, :site_id,
                    :service_id, :amount, :trans_date, :trans_type)");

            $stmt->bindValue(':mid', $mid);
            $stmt->bindValue(':card_number', $card_number);
            $stmt->bindValue(':terminal_id', $terminal_id);
            $stmt->bindValue(':site_id', $site_id);
            $stmt->bindValue(':service_id', $service_id);
            $stmt->bindValue(':amount', $amount);
            $stmt->bindValue(':trans_date', $trans_date);
            $stmt->bindValue(':trans_type', $trans_type);

            
            
            if ($stmt->execute()) {
                $this->dbh->commit();
                return true;
            } else {
                $this->dbh->rollBack();
                return false;
            }
        } catch (Exception $e) {
            $this->dbh->rollBack();
            return false;
        }
    }

}

?>
