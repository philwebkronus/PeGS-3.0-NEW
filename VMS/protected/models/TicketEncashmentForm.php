<?php

/**
 * @author Noel Antonio
 * @dateCreated November 14, 2013
 */

class TicketEncashmentForm extends CFormModel {
    
    public $ticketCode;
    public $memberCardNumber;
    
    /**
     * Create rules for the attributes.
     * @return array
     */
    public function rules()
    {
        return array(
            array('ticketCode, memberCardNumber', 'required'),
            array('ticketCode,', 'length', 'max' => 10),
            array('memberCardNumber', 'length', 'max' => 20)
        );
    }
    
    /**
     * This function is used to retrieve data of the specific
     * ticket code to be encashed.
     * @param string $ticketCode Ticket Code
     * @return array resultset
     */
    public function checkTicketCode($ticketCode)
    {
        $query = "SELECT * FROM tickets WHERE
                    Amount IS NOT NULL AND TicketCode = :ticketCode;";
        $command = Yii::app()->db->createCommand($query);
        $command->bindValue(":ticketCode", $ticketCode);
        $result = $command->queryRow();
        
        return $result;
    }
    
    
    /**
     * This model function is used to check existing and active
     * member by card number inputted.
     * @param string $cardNumber The member card number
     * @return array resultset
     */
    public function checkMemberCardNumber($cardNumber)
    {
        $query = "SELECT MID FROM membercards 
                    WHERE Status = 1 AND CardNumber = :cardNumber";
        $command = Yii::app()->db3->createCommand($query);
        $command->bindValue(":cardNumber", $cardNumber);
        $result = $command->queryAll();
        
        return $result;
    }
    
    
    /**
     * This model function is used to update the status of 
     * the ticket code successfully encashed by the cashier.
     * @param string $ticketCode Ticket code used.
     * @param int $aid Cashier account ID
     * @return array transCode (integer) for transaction result.
     */
    public function processTicket($ticketCode, $aid)
    {
        $vmsConn = Yii::app()->db;
        
        $try = $vmsConn->beginTransaction();
        
        $query = "UPDATE tickets
                  SET Status = 4, DateEncashed = NOW(6), EncashedByAID = :aid
                  WHERE TicketCode =:ticketCode AND Amount IS NOT NULL
                  AND Status IN (1, 2)";
        
        $sql = $vmsConn->createCommand($query);
        $sql->bindParam(":aid", $aid);
        $sql->bindParam(":ticketCode", $ticketCode);
        $sql->execute();
        
        try
        {
            $try->commit();
            return array('transCode'=>1, 'transMsg'=>'Successful');
        }
        catch(Exception $e)
        {
            $try->rollback();
            return array('transCode'=>2, 'transMsg'=>$e);
        }
    }
    
    
    /**
     * This model function is used to log 
     * ticket encashment transactions.
     * @param int $aid cashier account id.
     * @param string $transDetails transaction details.
     * @param string $ipAddress machine ip address.
     * @return array transCode (integer) for transaction result.
     */
    public function log($aid, $transDetails, $ipAddress)
    {
        $vmsConn = Yii::app()->db;
        
        $try = $vmsConn->beginTransaction();
        
        $query = "INSERT INTO audittrail (AID, AuditTrailFunctionID, TransDetails, TransDateTime, RemoteIP)
                  VALUES (:aid, 35, :transDetails, NOW(6), :ipAddress)";
        
        $sql = $vmsConn->createCommand($query);
        $sql->bindParam(":aid", $aid);
        $sql->bindParam(":transDetails", $transDetails);
        $sql->bindParam(":ipAddress", $ipAddress);
        $sql->execute();
        
        try
        {
            $try->commit();
            return array('transCode'=>1, 'transMsg'=>'Ticket encashment successful.');
        }
        catch(Exception $e)
        {
            $try->rollback();
            return array('transCode'=>2, 'transMsg'=>$e);
        }
    }
}
?>
