<?php

/**
 * Model for accounts table
 * @package application.modules.managerss.models
 * @author Bryan Salazar
 */
class RssAccounts extends RssModel
{
    /**
     *
     * @var RssAccounts 
     */
    private static $_instance = null;
    
    private function __construct() 
    {
        $this->_connection = RssDB::app();
    }    
    
    /**
     * Get instance of RssAccounts
     * @return RssAccounts 
     */
    public static function model()
    {
        if(self::$_instance == null)
            self::$_instance = new RssAccounts();
        return self::$_instance;
    }        
    
    /**
     *
     * @param string $username
     * @param string $password
     * @return boolean|int false if failed or int number of row affected 
     */
    public function isLogin($username,$password)
    {
        $query = 'SELECT ad.Email, a.AID, a.UserName, a.Password, a.Status, a.LoginAttempts, a.ForChangePassword FROM accounts a ' . 
            'INNER JOIN accountdetails ad ON ad.AID = a.AID ' .
            'WHERE a.UserName = :username AND a.Password = :password AND a.AccountTypeID = :accounttypeid AND a.Status IN '.RssConfig::app()->params['account_status'];
        
        
        $command = $this->_connection->createCommand($query);
        $row =  $command->queryRow(true,array(
            ':username'=>$username,
            ':password'=>sha1($password),
            ':accounttypeid'=>RssConfig::app()->params['accoounttypeid']
        ));
        return $row;
    }   
    
    /**
     *
     * @param string $username
     * @param string $newpassword
     * @param string $oldpassword
     * @return bool|int false if there is error or int number of rows affected 
     */
    public function resetPassword($username,$newpassword,$oldpassword) {
        $query = 'UPDATE accounts SET Password = :newpassword, ForChangePassword = 1 ' . 
            'WHERE UserName = :username AND Password = :password';
        $data = array(
            ':newpassword'=>sha1($newpassword),
            ':username'=>$username,
            ':password'=>$oldpassword
        );
        $command = $this->_connection->createCommand($query);
        $n = $command->execute($data);
        if(!$n) {
            $this->log($command->getText().$command->getBound()." failed to update password", 'managerss.models.RssAccounts');
        }
        return $n;
    }
    
    /**
     *
     * @param string $username
     * @return boolean|array false if failed
     */
    public function getAccountByUsername($username)
    {
        $query = 'SELECT AID, LoginAttempts, Password, UserName FROM accounts WHERE UserName = :username AND Status IN '.RssConfig::app()->params['account_status'];
        $command = $this->_connection->createCommand($query);
        $row = $command->queryRow(true,array(':username'=>$username));
        return $row;
    }
    
    /**
     *
     * @param int $aid
     * @return bool|int false if there is error or int number of rows affected 
     */
    public function resetLoginAttempts($aid) {
        $query = 'UPDATE accounts SET LoginAttempts = 0 ' . 
            'WHERE AID = :aid';
        $command = $this->_connection->createCommand($query);
        $data = array(':aid'=>$aid);
        $n = $command->execute($data);
        if(!$n) {
            $this->log($command->getText().$command->getBound()." failed to reset loginattempts accounts", 'managerss.models.RssAccounts');
        }
        return $n;
    }
    
    /**
     *
     * @param int $currentLoginAttemptCounts
     * @param string $username
     * @return bool|int false if there is error or int number of rows affected
     */
    public function incrementLoginAttempts($currentLoginAttemptCounts,$username)
    {
        $loginAttempts = $currentLoginAttemptCounts + 1;
        $query = 'UPDATE accounts SET LoginAttempts = :loginattempts ' . 
            'WHERE UserName = :username';
        $data = array(
            ':loginattempts'=>$loginAttempts,
            ':username'=>$username
        );
        $command = $this->_connection->createCommand($query);
        $n = $command->execute($data);
        if(!$n) {
            $this->log($command->getText().$command->getBound()." failed to update accounts", 'managerss.models.RssAccounts');
        }
        return $n;
    }
}
