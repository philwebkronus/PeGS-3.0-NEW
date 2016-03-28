<?php

class Ref_EwalletAllowedUserModel extends CFormModel
{
    public $connection;
    
    public function __construct() 
    {
        $this->connection = Yii::app()->db2;
    }
    
    /**
     * Check if card is white listed
     * @param type $mid
     * @author Ralph Sison
     * @date 06-24-2015
     */
    public function checkIfCardIsWhiteListed($mid) {
        $sql = "SELECT COUNT(MID) as Count
                FROM ref_ewalletalloweduser 
                WHERE MID = :mid";
        $command = $this->connection->createCommand($sql);
        $command->bindValue(":mid", $mid);
        $result = $command->queryRow();
        
        return $result;
    }
    
}
?>
