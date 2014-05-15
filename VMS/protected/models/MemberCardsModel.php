<?php
/**
 * Description of MemberCardsModel
 *
 * @author jshernandez
 */

class MemberCardsModel extends CFormModel{
    
    public $_connection;


    public function __construct() {
        $this->_connection = Yii::app()->db3;
    }
    
    /**
     * @author JunJun S. Hernandez
     * @datecreated 11/11/13
     * @param str $cardnumber
     * @return object
     */
    public function getMIDByCardNumber($cardnumber){
        $sql = "SELECT MID FROM membercards WHERE CardNumber = :cardnumber AND Status = 1";
        $command = $this->_connection->createCommand($sql);
        $command->bindValue(":cardnumber", $cardnumber);
        $result = $command->queryRow();
        
        return $result;
    }
}
?>
