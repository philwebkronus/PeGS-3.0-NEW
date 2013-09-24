<?php
/*
 * Description: AccountForm model
 * @author: gvjagolino
 * DateCreated: 2013-08-30
 */

class AccountForm extends CFormModel
{
    /*
     * Description: get total login attempts
     * @author: gvjagolino
     * result: object array
     * DateCreated: 2013-08-30
     */
	public function getLoginAttempts($aid)
    {
        $connection = Yii::app()->db2;
         
        $sql="SELECT LoginAttempts FROM accounts 
            WHERE AID = :aid;";
        $command = $connection->createCommand($sql);
        $command->bindValue(':aid', $aid);
        $result = $command->queryAll();
        
        return $result;

    }
    
    
    /*
     * Description: update acct status of a certain account
     * @author: gvjagolino
     * result: object array
     * DateCreated: 2013-08-30
     */
    public function updateAcctStatus($aid)
    {
        $connection = Yii::app()->db2;

        $sql="UPDATE accounts SET Status = 3
            WHERE AID = :aid;";
        $command = $connection->createCommand($sql);
        $command->bindValue(':aid', $aid);
    
        return $command->execute();

    }
    
    
    /*
     * Description: update login attempts of a certain account
     * @author: gvjagolino
     * result: object array
     * DateCreated: 2013-08-30
     */
    public function updateLoginAttempts($aid, $attempts)
    {
        $connection = Yii::app()->db2;
         
        $sql="UPDATE accounts SET LoginAttempts = :attempts
            WHERE AID = :aid;";
        $command = $connection->createCommand($sql);
        $command->bindValue(':attempts', $attempts);
        $command->bindValue(':aid', $aid);
        
        return $command->execute();

    }
    
    
    /*
     * Description: check given username if existing
     * @author: gvjagolino
     * result: object array
     * DateCreated: 2013-08-30
     */
    public function checkUsername($username)
    {
        $connection = Yii::app()->db2;
        
        $sql="SELECT AID, UserName, Password, Status FROM accounts 
            WHERE UserName = :username;";
        $command = $connection->createCommand($sql);
        $command->bindValue(':username', $username);
        $result = $command->queryAll();
        
        return $result;    

    }
    
    
    /*
     * Description: check given password if existing
     * @author: gvjagolino
     * result: object array
     * DateCreated: 2013-08-30
     */
    public function checkPassword($password)
    {
        $connection = Yii::app()->db2;
        $password = sha1($password);
         
        $sql="SELECT AID, UserName, Password, Status FROM accounts 
            WHERE Password = :password;";
        $command = $connection->createCommand($sql);
        $command->bindValue(':password', $password);
        $result = $command->queryAll();
        
        return $result;
        

    }
    
    public function getAccountStatus($status){
        
        switch ($status) {
            case 0:
               $statusword = 'Pending';     
            break;

            case 2:
                $statusword = 'Suspended';
            break;

            case 3:
                $statusword = 'Locked';
            break;

            case 4:
                $statusword = 'Locked';
            break;

            case 5:
                $statusword = 'Terminated';
            break;

            case 6:
                $statusword = 'Password Expired';
            break;


            default:
                break;
        }
        
        return $statusword;
        
    }
    
}
