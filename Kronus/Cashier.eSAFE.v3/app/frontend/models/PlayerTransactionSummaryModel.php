<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of PlayerTransacitonSummaryModel
 *
 * @author jdlachica
 */
class PlayerTransactionSummary extends MI_Model {
    public function insertTransactionSummary(){
        try {
            $this->beginTransaction();
            $sql = 'INSERT INTO playertransactionsummary (MID, ServiceID, LoyaltyCardNumber, 
                SiteID, TerminalID, Deposit, Withdraw, DateStarted, 
                DateEnded, CreatedByAID, UpdatedByAID, OptionID1, OptionID2, Option1, Option2, Option3) 
                VALUES (:mid, :service_id, :card_number, :site_id, :terminal_id, 
                :deposit, :withdraw, now(6), :date_ended, :created_by, :updated_by,
                :option_id1, :option_id2, :option1, :option2, :option3)';

            $stmt = $this->dbh->prepare($sql);

            $stmt->bindValue(':mid', $terminal_id);
            $stmt->bindValue(':service_id', $service_id);
            $stmt->bindValue(':card_number', $amount);
            $stmt->bindValue(':site_id', $trans_summary_id);
            $stmt->bindValue(':terminal_id', $loyalty_card);
            $stmt->bindValue(':deposit', $mid);
            $stmt->bindValue(':withdraw', $user_mode);
            $stmt->bindValue(':date_ended', null);
            $stmt->bindValue(':created_by', $casino_pwd);
            $stmt->bindValue(':updated_by', null);
            $stmt->bindValue(':option_id1', null);
            $stmt->bindValue(':option_id2', null);
            $stmt->bindValue(':option1', null);
            $stmt->bindValue(':option2', null);
            $stmt->bindValue(':option3', null);
            

            if($stmt->execute()){
                try {
                    $this->dbh->commit();
                    return true;
                } catch(Exception $e) {
                    $this->dbh->rollBack();
                    return false;
                }
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
