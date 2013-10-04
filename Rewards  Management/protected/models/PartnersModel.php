<?php

class PartnersModel extends CFormModel
{
    
    public function getPartnerPID($refpartnerid){
        
        $connection = Yii::app()->db;
         
        $sql="SELECT RefPartnerID FROM partners
            WHERE RefPartnerID = :refpartnerid LIMIT 1";
        $command = $connection->createCommand($sql);
        $command->bindValue(':refpartnerid', $refpartnerid);
        $result = $command->queryAll();
         
        return $result; 
    }
    /**
     * Check if the Username entered is existing in partners table
     * @param string $username Username entered by the user
     * @return array Array of partner's details
     * @author Mark Kenneth Esguerra
     * @date October 1, 2013
     */
    public function checkUsername($username)
    {
        $connection = Yii::app()->db;
        
        $query = "SELECT UserName, PartnerPID, AccountTypeID FROM partners WHERE
                  UserName = :username
                 ";
        $command = $connection->createCommand($query);
        $command->bindParam(":username", $username);
        $result = $command->queryAll();
        
        return $result;
        
    }
    /**
     * Check if the password entered by the user is valid
     * @param string $password Password entered by the user
     * @return array
     * @author Mark Kenneth Esguerra
     * @date October 1, 2013
     */
    public function checkPassword($partnerPID, $password)
    {
        $connection = Yii::app()->db;
        $password = sha1($password);
        
        $query = "SELECT Username, Password FROM partners WHERE
                  Password = :password AND PartnerPID = :partnerPID
                 ";
        $command = $connection->createCommand($query);
        $command->bindParam(":password", $password);
        $command->bindParam(":partnerPID", $partnerPID);
        $result = $command->queryAll();
        
        return $result;
    }
    /**
     * Check how many login attempts made by the user
     * @param int $partnerPID Partner user ID
     * @return int Login attempts
     * @author Mark Kenneth Esguerra
     * @date October 1, 2013
     */
    public function getLoginAttempts($partnerPID)
    {
        $connection = Yii::app()->db;
        
        $query = "SELECT LoginAttempts FROM partners WHERE PartnerPID = :partnerPID";
        
        $command = $connection->createCommand($query);
        $command->bindParam(":partnerPID", $partnerPID);
        $result = $command->queryAll();
        
        foreach ($result as $row)
        {
            return $row['LoginAttempts'];
        }
    }
    /**
     * Update login attempts when an invalid details has been entered <br />by the user.
     * Reset login attempts into 0 when successfully logged in
     * @param int $numofattempts Number of attempts
     * @param int $partnerPID Partner user ID
     * @return array
     * @author Mark Kenneth Esguerra
     * @date October 1, 2013
     */
    public function updateLoginAttempts($numofattempts, $partnerPID)
    {
        $connection = Yii::app()->db;
        
        $pdo = $connection->beginTransaction();
        
        $query = "UPDATE partners SET LoginAttempts = :numberofattempts
                  WHERE PartnerPID = :partnerPID
                 ";
        $command = $connection->createCommand($query);
        $command->bindParam(":numberofattempts", $numofattempts);
        $command->bindParam(":partnerPID", $partnerPID);
        $result = $command->execute();
        
        if ($result > 0)
        {
            try
            {
                $pdo->commit();
                return array('TransCode' => 1);
            }
            catch(CDbException $e)
            {
                $pdo->rollback();
                return array('TransCode' => 0, 'TransMsg' => 'Error: '.$e->getMessage());
            }
        }
        else
        {
            $pdo->rollback();
            return array('TransCode' => 0, 'An error occured while updating LoginAttempts');
        }
    }
    /**
     * Check if the Username and Password are existing in the database
     * @param string $username Username entered by the user
     * @param string $password Password entered by the user
     * @return array
     * @author Mark Kenneth Esguerra
     * @date October 1, 2013
     */
    public function checkAccount($username, $password)
    {
        $connection = Yii::app()->db;
        $password = sha1($password);
        
        $query = "SELECT UserName, Password FROM partners WHERE
                  UserName = :username AND Password = :password";
        
        $command = $connection->createCommand($query);
        $command->bindParam(":username", $username);
        $command->bindParam(":password", $password);
        $result = $command->queryAll();
        
        return $result;
    }
}
?>
