<?php

class PartnersModel extends CFormModel
{
    
    public function getPartnerPID($refpartnerid){
        
        $connection = Yii::app()->db;
         
        $sql="SELECT PartnerPID FROM partners
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
    /**
     * Check if username of added partner already exist
     * @param string $username Entered username for the partner
     * @return int Count, to check if exist
     * @author Mark Kenneth Esguerra
     * @date November 29, 2013
     */
    public function checkUsernameExist($username)
    {
        $connection = Yii::app()->db;
        
        $query = "SELECT Count(PartnerPID) as ctrusername FROM partners
                  WHERE UserName = :username";
        
        $command = $connection->createCommand($query);
        $command->bindParam(":username", $username);
        $result = $command->queryRow();
        
        return $result;
    }
    /**
     * Update DateLastLogin of partners table after logging in
     * @param int $partnerPID Partner ID
     * @return boolean True if successfully updated false if not
     * @author Mark Kenneth Esguerra
     * @date December 4, 2013
     */
    public function updateLastLogin($partnerPID)
    {
        $connection = Yii::app()->db;
        
        $pdo = $connection->beginTransaction();
        
        try
        {
            $query = "UPDATE partners SET DateLastLogin = NOW_USEC() 
                      WHERE PartnerPID = :partnerPID";
            $sql = $connection->createCommand($query);
            $sql->bindParam(":partnerPID", $partnerPID);
            
            $result = $sql->execute();
            
            if ($result > 0)
            {
                try
                {
                    $pdo->commit();
                    return true;
                }
                catch (CDbException $e)
                {
                    $pdo->rollback();
                    return false;
                }
            }
            else
            {
                $pdo->rollback();
                return false;
            }
        }
        catch (CDbException $e)
        {
            $pdo->rollback();
            return false;
        }
    }
}
?>
