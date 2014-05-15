<?php

/**
 * @author owliber
 * @date Oct 17, 2012
 * @filename SSO.php
 * 
 */

class SSO extends OcActiveRecord
{
    /**
     * Returns the static model of the specified AR class.
     * @return User the static model class
     */
    public static function model($className=__CLASS__)
    {
            return parent::model($className);
    }
    
    /**
     * @return string the associated database table name
     */
    public function tableName()
    {
            return 'accounts';
    }
            
    //perform one-way encryption on the password before we store it in the database
    protected function afterValidate()
    {
        parent::afterValidate();
        $this->Password = $this->encrypt($this->Password);
    }

    public function encrypt($value)
    {
        return sha1($value);
    }
    
    public static function getUserStatus($username)
    {
        $query = "SELECT Status FROM accounts 
                  WHERE UserName=:username";
        $sql = Yii::app()->db2->createCommand($query);
        $sql->bindParam(":username",$username);
        $result = $sql->queryAll();
        
        if(count($result)> 0)
        {
            return $result[0]['Status'];
        }
    }
    
        
}
?>
