<?php
/**
 * Update Password Model
 * @author Mark Kenneth Esguerra
 * @date September 30, 2013
 * @copyright (c) 2013, Philweb Corporation
 */
class UpdatePasswordModel extends CFormModel
{
    public $TempPass;
    public $NewPassword;
    public $ConfirmPassword;
    public $Username;
    
    public function rules()
    {
        return array(
          array('Username, NewPassword, ConfirmPassword, TempPass', 'required')  
        );
    }
    public function attributeLabels()
    {
        return array(
            'TempPass' => 'Old Password',
        );
    }
    /**
     * Check if the Username and Password are valid. <br /><br />
     * @param string $username Username of the partner
     * @param string $password Temporary password of the partner
     * @author Mark Kenneth Esguerra
     * @date September 30, 2013
     * @return boolean Returns <b>TRUE</b> if valid, else  <b>FALSE</b>
     */
    public function checkUsernamePassword($username, $password)
    {
        $connection = Yii::app()->db;
        
        $query = "SELECT UserName, Password FROM partners 
                  WHERE UserName = :username AND Password = :password";
        $command = $connection->createCommand($query);
        $command->bindParam(":username", $username);
        $command->bindParam(":password", $password);
        $result = $command->queryAll();
        
        //Check if username and password are exist
        if (count($result) > 0)
        {
            return true;
        }
        else
        {
            return false;
        }
    }
    /**
     * Updates the password of the user
     * @param string $newpassword Password entered by the user
     * @param string $username Username of the user
     * @return array TransMsg and TransCode
     * @author Mark Kenneth Esguerra
     * @date September 30, 2013
     */
    public function updatePassword($newpassword, $username)
    {
        $connection = Yii::app()->db;
        
        $pdo = $connection->beginTransaction();

        $query = "UPDATE partners SET
                  Password = :newpassword
                  WHERE UserName = :username
                 ";
        $command = $connection->createCommand($query);
        $command->bindParam(":newpassword", $newpassword);
        $command->bindParam(":username", $username);
        $result = $command->execute();
        //Check if successfully updated
        if ($result > 0)
        {
            try
            {
                $pdo->commit();
                return array('TransMsg' => 'Password successfully updated.',
                             'TransCode' => 1);
            }
            catch(CDbException $e)
            {
                $pdo->rollback();
                return array('TransMsg' => 'Error: '.$e->getMessage(),
                             'TransCode' => 2);
            }
        }
        else
        {
            $pdo->rollback();
            return array('TransMsg' => 'Record details unchanged.',
                         'TransCode' => 2);
        }
    }
}
?>
