<?php
/**
 * Description of AccountsModel
 *
 * @author bryan
 */
class AccountsModel extends MI_Model{
    public function login($username,$password) {
        $sql = 'SELECT AID, UserName, Password, AccountTypeID, Passkey, AccountGroupID, LoginAttempts, SessionNoExpire, WithPasskey ' . 
            'FROM accounts WHERE Status IN (1,6) AND UserName = :username AND Password =  :password';
        //SELECT AID, UserName, Password, AccountTypeID, Passkey, AccountGroupID, LoginAttempts, SessionNoExpire, WithPasskey FROM accounts WHERE Status =1 AND UserName = :username AND Password =  :password
        $param = array(':username'=>$username,':password'=>sha1($password));
        $this->exec($sql, $param);
        return $this->find();
    }
    
    public function queryattempt($username) {
        $sql = 'Select LoginAttempts from accounts WHERE UserName =  :username';
        $param = array(':username'=>$username);
        $this->exec($sql, $param);
        $result =  $this->find();
        
        if(!$result) {
            return 0;
        }
        return $result['LoginAttempts'];
    }
    
    public function getAccountTypebyUN($username) {
        $sql = 'Select AccountTypeID from accounts WHERE UserName =  :username';
        $param = array(':username'=>$username);
        $this->exec($sql, $param);
        $result =  $this->find();
        
        if(!$result) {
            return 0;
        }
        return $result['AccountTypeID'];
    }
    
//    //when login is successful, it must updated attempt to 0, passkey if any
//    public function updateonlogin($zloginattempt, $zdate,$zusername){
//        $this->prepare("Update  accounts  SET LoginAttempts = :loginattempt, DateLastLogin = :lastlogin  WHERE UserName = :username");
//        $xparams = array(':loginattempt'=> $zloginattempt,':lastlogin'=> $zdate,':username'=> $zusername);
//        $result=$this->executewithparams($xparams );
//        return $result;
//    }    
    
    public function updateAttempt($loginattempt, $username) {
        $sql = 'Update  accounts  SET LoginAttempts = :loginattempt WHERE UserName = :username';
        $param = array(':loginattempt'=>$loginattempt,':username'=>$username);
        return $this->exec($sql, $param);
    }
    
    public function updateOnLogin($loginattempt,$date,$username) {
        $sql = 'Update  accounts  SET LoginAttempts = :loginattempt, DateLastLogin = :lastlogin  WHERE UserName = :username';
        $param = array(':loginattempt'=>$loginattempt,':lastlogin'=>$date,':username'=>$username);
        return $this->exec($sql, $param);
    }
    
    public function updateLoginAttempt($loginattempt,$aid) {
        $sql = 'UPDATE accounts SET LoginAttempts = :loginattempt WHERE AID = :aid';
        $param = array(':loginattempt'=>$loginattempt,':aid'=>$aid);
        return $this->exec($sql, $param);
    }
    
    //check if passkey entered is equal to the passkey saved in table
    public function checkpasskey($zPasskey, $aid) {
        $sql = 'SELECT Passkey FROM accounts WHERE Passkey = :passkey AND AID = :aid';
        $param = array(':passkey'=>$zPasskey, ':aid'=>$aid);
        $this->exec($sql, $param);
        $result = $this->find();
        if(isset($result['Passkey']) && $result['Passkey'] != '')
            return true;
        
        return false;
    }
    
    //change password: check if username and email exist
    public function checkusernameandemail($zusername,$zemail){
        $sql = "Select COUNT(*) count, a.Status from accounts a INNER JOIN accountdetails b ON a.AID = b.AID WHERE a.UserName = :username and b.Email = :email";
        $param = array(':username' => $zusername, ':email' => $zemail);
        $this->exec($sql, $param);
        return $this->find();
    }   
    
    public function checkpwdexpired($username) {
        $sql = 'SELECT COUNT(*) AS ctrpwd FROM accounts WHERE UserName = :username AND Status = 6 AND ForChangePassword = 0';
        $param = array(':username'=>$username);
        $this->exec($sql,$param);
        $result =  $this->find();
        if(!isset($result['ctrpwd']))
            return false;
        return $result['ctrpwd'];
    }
   
    public function getaid($zusername) {
        $sql = "SELECT AID FROM accounts WHERE UserName = :username";
        $param = array(':username'=>$zusername);
        $this->exec($sql, $param);
        $result = $this->find();
        if(!isset($result['AID']) && $result['AID'] == '') {
            return false;
        }
        return $result['AID'];
    }   
    
    //temporary change password: update accounts_>ForChangePassword and accounts->Password
//    public function temppassword($temppass, $zusername, $zemail){
//        $sql = "Update accounts a INNER JOIN accountdetails b ON a.AID = b.AID SET ForChangePassword = 0 , Password = :temppass WHERE a.UserName = :username and b.Email = :email";
//        $param = array(':username' => $zusername, ':temppass'=> $temppass,':email' => $zemail);
//        $this->exec($sql, $param);
//        return $this->rowCount();
//    }
   
    public function getcurrentpassword($zaid) {
        $sql = "SELECT Password FROM accounts WHERE AID = :aid";
        $param = array(':aid'=>$zaid);
        $this->exec($sql, $param);
        $result = $this->find();
        if(!isset($result['Password']) && $result['Password'] == '') {
            return false;
        }
        return $result['Password'];
    }   
    
    public function countRecentPasswordsByAID($zaid) {
        $sql = "SELECT COUNT(AID) AS Count FROM accountsrecentpasswords WHERE AID = :aid";
        $param = array(':aid'=>$zaid);
        $this->exec($sql, $param);
        $result = $this->find();
        return $result['Count'];
    }   
    
    public function getOldestRecentPassword($zaid) {
        $sql = "SELECT Password FROM accountsrecentpasswords WHERE AID = :aid ORDER BY DateCreated ASC LIMIT 0, 1 ";
        $param = array(':aid'=>$zaid);
        $this->exec($sql, $param);
        $result = $this->find();
        return $result['Password'];
    }   
   
    //temporary change password: update accounts_>ForChangePassword and accounts->Password
    /*
     * 
    public function temppassword($temppass, $zaid, $zusername, $zemail){
        $currpassword = $this->getcurrentpassword($zaid);
       try {
           $this->dbh->beginTransaction();
           $smt = $this->dbh->prepare('INSERT INTO accountsrecentpasswords (AID, Password, DateCreated)  VALUES (?, ?, NOW(6))');
           $smt->bindValue(1, $zaid, PDO::PARAM_STR);
           $smt->bindValue(2, $currpassword, PDO::PARAM_STR);
           
           if(!$smt->execute()) {
               $this->dbh->rollBack();
               return false;
           }  
           
           $smt = $this->dbh->prepare('Update accounts a INNER JOIN accountdetails b ON a.AID = b.AID SET a.ForChangePassword = 0 , a.Password = :temppass WHERE a.UserName = :username and b.Email = :email');
           $smt->bindValue(':username', $zusername, PDO::PARAM_STR);
           $smt->bindValue(':temppass', $temppass, PDO::PARAM_STR);
           $smt->bindValue(':email', $zemail, PDO::PARAM_STR);
           if(!$smt->execute()) {
               $this->dbh->rollBack();
               return false;
           }

            //count if recent passwords of user
            $countRecent = $this->countRecentPasswordsByAID($zaid);
            if ($countRecent['Count'] > 5) {
                //delete old recent password recorded
                //get the recent password
                $recentPassword = $this->getOldestRecentPassword($zaid);
                $smt = $this->dbh->prepare("DELETE FROM accountsrecentpasswords WHERE Password = ? AND AID = ?");
                $smt->bindValue(1, $recentPassword);
                $smt->bindValue(2, $zaid);
                if ($smt->execute()) {
                    $this->dbh->commit();
                    return true;
                } else {
                    logger("Failed to delete accountsrecentpasswords: ".$smt->execute());
                    $this->dbh->rollBack();
                    return false;
                }
            }
           
           $this->dbh->commit();
           return true;
       } catch(PDOException $e) {
           $this->dbh->rollBack();
       }
       return false;
    }
     * 
     */
   
    public function updatepwd($zusername, $zpassword ) {
        $sql = 'SELECT COUNT(*) FROM accounts WHERE UserName = :username AND Password =  :password';
        $param = array(':username' =>$zusername, ':password' =>$zpassword);
        $this->exec($sql, $param);
        return $this->find();
    }  
    
   //change password by user: update accounts->ForChangePassword and accounts->Password,; insert on passwordcheck
   public function resetpassword($zpassword, $zusername, $zaid) {
       try {
           $this->dbh->beginTransaction();
           $smt = $this->dbh->prepare('Update accounts SET ForChangePassword = 1, Password = ?, Status = 1 WHERE UserName = ?');
           $smt->bindValue(1, $zpassword, PDO::PARAM_STR);
           $smt->bindValue(2, $zusername, PDO::PARAM_STR);
           if(!$smt->execute()) {
               $this->dbh->rollBack();
               return false;
           }  
           
           $smt = $this->dbh->prepare('INSERT INTO passwordcheck(AID, DateChanged) VALUES (?, now(6))');
           $smt->bindValue(1, $zaid, PDO::PARAM_INT);
           if(!$smt->execute()) {
               $this->dbh->rollBack();
               return false;
           }
           $this->dbh->commit();
           return true;
       } catch(PDOException $e) {
           $this->dbh->rollBack();
       }
       return false;
   }
   
   //forgot password: check if email exist
   public function checkemail($zemail){
       $sql = 'SELECT COUNT(*) count, a.UserName, a.Status FROM accounts a INNER JOIN accountdetails b ON a.AID = b.AID where Email = :email';
       $param = array(':email' => $zemail);
       $this->exec($sql, $param);
       return $this->find();
   }   
   
   /**
    * get account  name
    * @author elperez
    * @param int $aid
    * @return array account name 
    */
    public function getAccountname($aid)
    {
        $sql = 'SELECT Name FROM accountdetails WHERE AID = :aid';
        $param = array(':aid'=>$aid);
        $this->exec($sql, $param);
        return $this->find();
    }
    
    
    public function getVirtualCashier($siteid){
        $sql = 'SELECT acct.AID FROm accounts acct INNER JOIN siteaccounts sa ON acct.AID = sa.AID WHERE sa.SiteID = :site_id AND acct.AccountTypeiD = 15';
        $param = array(':site_id'=>$siteid);
        $this->exec($sql, $param);
        return $this->find();
        
    }
    
    /*
     * @author Jeremiah D. Lachica
     * @date January 22, 2015
     * @param int AID
     * @return int AccountTypeID
     */
    public function getAccountTypeIDByAID($AID){
        $sql = 'Select AccountTypeID from accounts WHERE AID = :AID';
        $param = array(':AID'=>$AID);
        $this->exec($sql, $param);
        $result =  $this->find();
        
        if(!$result) {
            return 0;
        }
        return $result['AccountTypeID'];
    }
    
    //Check if the New Password is among the list of last 5 passwords of the account
    public function checkifrecentpassword($zaid, $znewpassword ) {
        $sql = "SELECT COUNT(AID) as Count 
                    FROM accountsrecentpasswords 
                    WHERE AID = :aid AND Password = :password";
        $param = array(':aid' =>$zaid, ':password' =>$znewpassword);
        $this->exec($sql, $param);
        return $this->find();
    }  
    
    public function checkAID($zusername, $zpassword ) {
        $sql = 'SELECT AID FROM accounts WHERE UserName = :username AND Password =  :password';
        $param = array(':username' =>$zusername, ':password' =>$zpassword);
        $this->exec($sql, $param);
        return $this->find();
    }  
     public function temppassword($temppass, $zaid){
        $currpassword = $this->getcurrentpassword($zaid);
       try {
           $this->dbh->beginTransaction();
           $smt = $this->dbh->prepare('INSERT INTO accountsrecentpasswords (AID, Password, DateCreated)  VALUES (?, ?, NOW(6))');
           $smt->bindValue(1, $zaid);
           $smt->bindValue(2, $currpassword);
           
           if(!$smt->execute()) {
               logger("Failed to insert accountsrecentpasswords: ".$smt->execute());
               $this->dbh->rollBack();
               return false;
           }  
           
           $smt = $this->dbh->prepare('UPDATE accounts SET ForChangePassword = 0 , Password = ? WHERE AID = ?');
           $smt->bindValue(1, $temppass);
           $smt->bindValue(2, $zaid);

           if(!$smt->execute()) {
               logger("Failed to update accounts: ".$smt->execute());
               $this->dbh->rollBack();
               return false;
           }

            //count if recent passwords of user
            $countRecent = $this->countRecentPasswordsByAID($zaid);
            if ($countRecent['Count'] > 5) {
                //delete old recent password recorded
                //get the recent password
                $recentPassword = $this->getOldestRecentPassword($zaid);
                $smt = $this->dbh->prepare("DELETE FROM accountsrecentpasswords WHERE Password = ? AND AID = ?");
                $smt->bindValue(1, $recentPassword);
                $smt->bindValue(2, $zaid);
                if ($smt->execute()) {
                    $this->dbh->commit();
                    return true;
                } else {
                    logger("Failed to delete accountsrecentpasswords: ".$smt->execute());
                    $this->dbh->rollBack();
                    return false;
                }
            }
           
           $this->dbh->commit();
           return true;
       } catch(PDOException $e) {
           logger("Error in inserting last password: ".$e);
           $this->dbh->rollBack();
       }
       return false;
    }
    
    public function checkCashierSiteClassification($zusername ) {
        $sql = 'SELECT s.SiteClassificationID FROM npos.accounts a
                INNER JOIN npos.siteaccounts sa ON sa.AID = a.AID
                INNER JOIN npos.sites s ON s.SiteID = sa.SiteID
                WHERE a.UserName = :username';
        $param = array(':username' =>$zusername);
        $this->exec($sql, $param);
        $result = $this->find();
        return $result['SiteClassificationID'];
    }
}

?>