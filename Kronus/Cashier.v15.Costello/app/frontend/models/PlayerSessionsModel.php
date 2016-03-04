<?php
/**
 * 
 * For removal / Cleanup
 * 
class PlayerSessionsModel extends MI_Model {
    public function insertPlayerSessions(){
        try {
            $this->beginTransaction();
            $sql = 'INSERT INTO playersessions (MID, ServiceID, LoyaltyCardNumber, 
                TerminalID, UserMode, UBServiceLogin, UBServicePassword, 
                UBHashedServicePassword, DateStarted, LastBalance, LastTransactionDate,
                PlayerTransactionSummaryID, HasLoyalty, Status, HelpPlayer,
                OptionID1, OptionID2, Option1, Option2, Option3) 
                VALUES (:mid, :service_id, :card_number, :terminal_id, :user_mode, 
                :ub_service_login, :ub_service_password, ub_hashed_service_password, 
                :date_started, :last_balance, :last_transaction_date, 
                :player_transaction_summary_id, :has_loyalty, :status, :help_player,
                :option_id1, :option_id2, :option1, :option2, :option3)';

            $stmt = $this->dbh->prepare($sql);

            $stmt->bindValue(':mid', $terminal_id);
            $stmt->bindValue(':service_id', $service_id);
            $stmt->bindValue(':card_number', $amount);
            $stmt->bindValue(':terminal_id', $loyalty_card);
            $stmt->bindValue(':user_mode', $mid);
            $stmt->bindValue(':ub_service_login', $user_mode);
            $stmt->bindValue(':ub_service_password', null);
            $stmt->bindValue(':ub_hashed_service_password', $casino_pwd);
            $stmt->bindValue(':date_started', 'now(6)');
            $stmt->bindValue(':last_balance', null);
            $stmt->bindValue(':last_transaction_date', null);
            $stmt->bindValue(':player_transaction_summary_id', null);
            $stmt->bindValue(':has_loyalty', null);
            $stmt->bindValue(':status', null);
            $stmt->bindValue(':help_player', null);
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
 * 
 */
