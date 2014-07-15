<?php

/**
 * @author owliber
 * @date Oct 18, 2012
 * @filename AccountTypes.php
 * 
 */

class AccountTypes extends OcActiveRecord
{
    public static function model($className=__CLASS__)
    {
        return parent::model($className);
    }
    
    public function accountTypes()
    {
        $query = "SELECT AccountTypeID, Name FROM ref_accounttypes";
        $sql = Yii::app()->db2->createCommand($query);
        return $sql->queryAll();
    }
    
    public function getAccountTypes()
    {
        return CHtml::listData($this->accountTypes(), 'AccountTypeID', 'Name');
    }
    /**
     * Get Username by AID
     * @param int $aid ID of the user
     * @return array UserName
     * @author Mark Kenneth Esguerra [02-28-14]
     */
    public function getUsername($aid)
    {
        $connection = Yii::app()->db2;
        
        $query = "SELECT UserName FROM accounts WHERE AID = :aid";
        $command = $connection->createCommand($query);
        $command->bindParam(":aid", $aid);
        $result = $command->queryRow();
        
        if (is_array($result))
        {
            return $result['UserName'];
        }
        else
        {
            return "";
        }
    }
    
    /**
     * Change Account Status to 3 (Locked)
     * @param int $aid ID of the user
     * @return none
     * @author jefloresca
     * @date created 2014-07-14
     */
    public function changeStatusByUserName($UserName)
    {
        $connection = Yii::app()->db2;
        $sql = "UPDATE accounts SET Status = 3
            WHERE UserName = :UserName;";
        $command = $connection->createCommand($sql);
        $command->bindValue(':UserName', $UserName);

        return $command->execute();
    }
    
}
?>
