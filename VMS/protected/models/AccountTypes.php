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
        $query = "SELECT AccountTypeID,Name FROM ref_accounttypes";
        $sql = Yii::app()->db2->createCommand($query);
        return $sql->queryAll();
    }
    
    public function getAccountTypes()
    {
        return CHtml::listData($this->accountTypes(), 'AccountTypeID', 'Name');
    }
}
?>
