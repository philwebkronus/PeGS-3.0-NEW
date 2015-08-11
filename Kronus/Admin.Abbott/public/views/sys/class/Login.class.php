<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
include "DbHandler.class.php";
ini_set('display_errors',true);
ini_set('log_errors',true);

class Login extends DBHandler
{
       public function __construct($sconectionstring)
      {
          parent::__construct($sconectionstring);
      }

    public function login( $zusername,$zpassword )
    {
         //$this->_stmt = $this->_PDO->prepare("SELECT AID, UserName, Password, AccountTypeID, Passkey, AccountGroupID, LoginAttempts, SessionNoExpire FROM accounts WHERE Status =1 AND UserName = :username AND Password =  :password");
         $this->prepare("SELECT AID, UserName, Password, AccountTypeID, Passkey, AccountGroupID, LoginAttempts, SessionNoExpire, WithPasskey FROM accounts WHERE Status =1 AND UserName = :username AND Password =  :password");
         $convertpass= sha1($zpassword);
         $xparams = array(':username' =>$zusername, ':password' =>$convertpass);
         $this->executewithparams( $xparams);
         $result = $this ->fetchData();
         return $result;
    }

    //validation: insert session on accountsessions table
    public function insertsession($zaid, $zsessionID, $zdate){
       $this->prepare("INSERT INTO accountsessions (AID, SessionID, DateCreated) VALUES(:aid, :sessionid, :date)");
       $xparams = array(':aid' => $zaid, ':sessionid' => $zsessionID,':date'=> $zdate);
      return  $this->executewithparams( $xparams);

   }

   //validation: check if attempts > 3
   public function queryattempt($zusername) {
       $this->prepare("SELECT LoginAttempts FROM accounts WHERE UserName =  :username");
       $checkattempt = array(':username'=>$zusername);
       $this->executewithparams($checkattempt );
       $result = $this ->fetchData();
       return $result;
   }

   //when login is successful, it must updated attempt to 0, passkey if any
    public function updateonlogin($zloginattempt, $zdate,$zusername){
       $this->prepare("UPDATE  accounts  SET LoginAttempts = :loginattempt, DateLastLogin = :lastlogin  WHERE UserName = :username");
       $xparams = array(':loginattempt'=> $zloginattempt,':lastlogin'=> $zdate,':username'=> $zusername);
       $result=$this->executewithparams($xparams );
       return $result;
   }

    //validation: delete sesion if exist or upon logout
    public function deletesession($aid)
    {
       $stmt = "DELETE FROM accountsessions WHERE AID = ?"   ;
       $this->prepare($stmt);
       $this->bindparameter(1, $aid);
       $this->execute();
    }

   //delete session for multiple login

   //update LoginAttempts based on invalid login attempt
   public function updateattempt($zloginattempt, $zusername){
        $this->prepare("UPDATE  accounts  SET LoginAttempts = :loginattempt  WHERE UserName = :username");
        $xparams = array(':loginattempt'=> $zloginattempt,':username'=> $zusername);
        $this->executewithparams($xparams );
        return $this->rowCount();
   }

    //change password: check if username and email exist
   public function checkusernameandemail($zusername,$zemail){
       $this->prepare("SELECT COUNT(*) count FROM accounts a INNER JOIN accountdetails b ON a.AID = b.AID WHERE a.UserName = :username and b.Email = :email");
       $xparams = array(':username' => $zusername, ':email' => $zemail);
       $this->executewithparams($xparams);
       return $this->fetchData();
   }

   //temporary change password: update accounts_>ForChangePassword and accounts->Password
   public function temppassword($temppass, $zusername, $zemail){
       $this->begintrans();
       /**
        * Added by: Mark Kenneth Esguerra
        * @date January 12, 2015
        * Log recent password
        */
       $credentials = $this->getAIDAndPassword($zusername);
       $this->prepare("INSERT INTO accountsrecentpasswords (AID, Password, DateCreated) VALUES (?, ?, NOW(6))");
       $this->bindparameter(1, $credentials['AID']);
       $this->bindparameter(2, $credentials['Password']);  
       if ($this->execute()) 
       {
           //update password after logging previous password
           $this->prepare("UPDATE accounts a INNER JOIN accountdetails b ON a.AID = b.AID 
                           SET ForChangePassword = 0, 
                           Password = :temppass 
                           WHERE a.UserName = :username AND 
                           b.Email = :email");
            $xparams = array(':username' => $zusername, ':temppass'=> $temppass,':email' => $zemail);
            $this->executewithparams($xparams);
            if ($this->execute())
            {
                //count if recent passwords of user
                $countRecent = $this->countRecentPasswordsByAID($credentials['AID']);
                if ($countRecent['Count'] > 5) 
                {
                    //delete old recent password recorded
                    //get the recent password
                    $recentPassword = $this->getOldestRecentPassword($credentials['AID']);
                    $stmt = "DELETE FROM accountsrecentpasswords 
                             WHERE Password = ? AND AID = ?";
                    $this->prepare($stmt);
                    $this->bindparameter(1, $recentPassword['Password']);
                    $this->bindparameter(2, $credentials['AID']);
                    if ($this->execute())
                    {
                        $this->committrans();
                    }
                    else
                    {
                        $this->rollbacktrans();
                    }
                }
                //else commit 
                else
                {
                    $this->committrans();
                }
            }
            else
            {
                $this->rollbacktrans();
            }
       }
       else
       {
           $this->rollbacktrans();
       }
       return $this->rowCount();
   }

    //update password from UpdatePassword.php
    public function updatepwd($zusername, $zpassword )
    {
         $this->prepare("SELECT COUNT(*) FROM accounts WHERE UserName = :username AND Password =  :password");
         $xparams = array(':username' =>$zusername, ':password' =>$zpassword);
         $this->executewithparams($xparams);
         return $this->fetchData();
    }

   //change password by user: update accounts->ForChangePassword and accounts->Password,; insert on passwordcheck
   public function resetpassword($zpassword, $zusername, $zaid)
   {
       $this->begintrans();
       $this->prepare("UPDATE accounts SET Status = 1, ForChangePassword = 1, Password = ? WHERE UserName = ?");
       $this->bindparameter(1, $zpassword);
       $this->bindparameter(2, $zusername);
       if($this->execute())
       {
           $this->prepare("INSERT INTO passwordcheck(AID, DateChanged) VALUES (?, now_usec())");
           $this->bindparameter(1, $zaid);
           if($this->execute())
           {
               $this->committrans();
               return 1; 
           }
           else
           {
               $this->rollbacktrans();
               return 0;
           }
       }
       else
       {
           $this->rollbacktrans();
           return 0;
       }
   }

   //forgot password: check if email exist
   public function checkemail($zemail){
         $this->prepare("SELECT COUNT(*) count, UserName FROM accounts a INNER JOIN accountdetails b ON a.AID = b.AID where Email = :email");
         $xparams = array(':email' => $zemail);
         $this->executewithparams($xparams);
         return $this->fetchData();
   }

   //check if passkey entered is equal to the passkey saved in table
   public function checkpasskey($zPasskey)
   {
       $this->prepare("SELECT COUNT(*) FROM accounts WHERE Passkey =:passkey");
       $xparams = array(':passkey' =>$zPasskey);
       $this->executewithparams($xparams);
       return $this->hasRows();  
   }
   
   //get path for landing page
   public function getpath($zacctType)
   {
       $this->prepare("SELECT DefaultURl FROM accessrights WHERE AccountTypeID =:acctype order by AccountTypeID, MenuID,OrderID,SubMenuID LIMIT 1");
       $xparams = array(':acctype' =>$zacctType);
       $this->executewithparams($xparams);
       return $this->hasRows();  
   }
   
   //get site id for cashier login added by mtcc 07/06/2011 
   public function getSiteID($zaid)
   {
       $this->prepare("SELECT SiteID FROM siteaccounts s where Status = 1 AND AID = :aid");
       $xparams = array(':aid' => $zaid);
       $this->executewithparams($xparams);
       return $this->fetchData();
   }
   
   //select designation
   public function getDesignation($zaid)
   {
       $stmt = "SELECT a.DesignationID, b.DesignationName FROM accountdetails a 
           INNER JOIN ref_designations b ON a.DesignationID = b.DesignationID WHERE AID = ?";
       $this->prepare($stmt);
       $this->bindparameter(1, $zaid);
       $this->execute();
       return $this->fetchAllData();
   }
   
   //select account group name
   public function getacctypename($zacctype)
   {
       $stmt = "SELECT Name FROM ref_accounttypes WHERE AccountTypeID = ?";
       $this->prepare($stmt);
       $this->bindparameter(1, $zacctype);
       $this->execute();
       return $this->fetchData();
   }
   
   //check computer credential
   public function checkcomputercredential($zmachineid,$zsiteid)
   {
       $stmt = "SELECT COUNT(POSAccountNo) as ctrsite, CashierMachineInfoId_PK FROM cashiermachineinfo WHERE Machine_Id = ?  AND POSAccountNo = ? AND isActive = 1";
       $this->prepare($stmt);
       $this->bindparameter(1, $zmachineid);
       $this->bindparameter(2, $zsiteid);
       $this->execute();
       return $this->fetchData();
   }
   
   //insert computer credential 
   public function addcomputercredential($zcpuid ,$zcpuname,$zbiosid,$zmbid,$zosid ,$zmacid,$zipid,$zguid,$zsiteid,$zmachineid ,$zdate)
   {
      $stmt = "INSERT INTO cashiermachineinfo(ComputerName,CPU_Id,BIOS_SerialNumber,MAC_Address,Motherboard_SerialNumber,OS_Id,Machine_Id,GUID,IPAddress,POSAccountNo,IsActive,RegisteredOn)  
          VALUES (?,?,?,?,?,?,?,?,?,?,?,?)" ; 
      $this->prepare($stmt);
      $this->bindparameter(1, $zcpuname);
      $this->bindparameter(2, $zcpuid);
      $this->bindparameter(3, $zbiosid);
      $this->bindparameter(4, $zmacid);
      $this->bindparameter(5, $zmbid);
      $this->bindparameter(6, $zosid);
      $this->bindparameter(7, $zmachineid);
      $this->bindparameter(8, $zguid);
      $this->bindparameter(9, $zipid);
      $this->bindparameter(10, $zsiteid);
      $this->bindparameter(11, '1');
      $this->bindparameter(12, $zdate);
      $this->execute();
      return  $this->insertedid();      
      //$xparams = array(':cpuname' => $zcpuname, ':cpuid' => $zcpuid,':biosid'=> $zbiosid,':macid' =>$zmacid,':mbid'=>$zmbid,':osid'=>$zosid,':machineid'=>$zmachineid,':guid'=>$zguid,':ipid'=>$zipid,':siteid'=>$zsiteid,':active'=>'1',':regon'=>$zdate);
      //return  $this->executewithparams( $xparams);
   }
   
   //create guid to be used in cashier terminal credential
   public function guid()
   {
        if (function_exists('com_create_guid'))
        {
            return com_create_guid();
        }
        else
       {
             mt_srand((double)microtime()*10000);
            $charid = strtoupper(md5(uniqid(rand(), true)));
            $hyphen = chr(45);
            $uuid = chr(123)
                .substr($charid, 0, 8).$hyphen
                .substr($charid, 8, 4).$hyphen
                .substr($charid,12, 4).$hyphen
                .substr($charid,16, 4).$hyphen
                .substr($charid,20,12)
                .chr(125);
            return $uuid;
        }
   }
   
   //cashier access must be exclusively per site
   function checkcashiersite($zsiteID, $zmacaddress)
   {
       $stmt = "SELECT COUNT(*) AS access FROM cashiermachineinfo cmi WHERE cmi.POSAccountNo = ? AND cmi.MAC_Address = ? AND IsActive = 1";
       $this->prepare($stmt);
       $this->bindparameter(1, $zsiteID);
       $this->bindparameter(2, $zmacaddress);
       $this->execute();
       return $this->fetchData();
   }
   
   //check if site is active
   function checkifactivesite($zsiteid)
   {
       $stmt = "SELECT COUNT(*) AS isactive FROM sites WHERE SiteID = ? and Status = 1";
       $this->prepare($stmt);
       $this->bindparameter(1,$zsiteid);
       $this->execute();
       return $this->fetchData();
   }
   
   /**
    * checks if account password was already updated
    */
   function checkpwdpermission($zaid)
   {
       $stmt = "SELECT COUNT(*) AS ctrpwd FROM accounts WHERE AID = ? AND (Status = 6 OR ForChangePassword = 0)";
       $this->prepare($stmt);
       $this->bindparameter(1, $zaid);
       $this->execute();
       return $this->fetchData();
   }
   
   /**
    * Get Account ID, PAssword based from username
    */
   function getaid($zusername)
   {
       $stmt = "SELECT AID FROM accounts WHERE UserName = ?";
       $this->prepare($stmt);
       $this->bindparameter(1, $zusername);
       $this->execute();
       return $this->fetchData();
   }
   
   /**
    * Check the status of site if active
    */
   function checkstatus($zusername)
   {
       $stmt = "SELECT COUNT(*) as ctrstatus FROM accounts WHERE Status = 1 AND UserName = ?";
       $this->prepare($stmt);
       $this->bindparameter(1, $zusername);
       $this->execute();
       return $this->fetchData();
   }
   
      /**
    * Check the status of site if terminated
    */
   function checktermstatus($zusername)
   {
       $stmt = "SELECT COUNT(*) as ctrstatus FROM accounts WHERE Status = 5 AND UserName = ?";
       $this->prepare($stmt);
       $this->bindparameter(1, $zusername);
       $this->execute();
       return $this->fetchData();
   }
   
   /**
    * get the cashier machine count per site
    */
   function checkcashiermachine($zsiteID)
   {
       $stmt = "SELECT CashierMachineCount FROM cashiermachinecounts WHERE SiteID = ?";
       $this->prepare($stmt);
       $this->bindparameter(1, $zsiteID);
       $this->execute();
       return $this->fetchData();
   }
   
   /**
    * count if machine id is conflicting
    */
   function checkmachineid($zmachineID)
   {
       $stmt = "SELECT COUNT(*) AS ctrmachine, POSAccountNo FROM cashiermachineinfo WHERE Machine_Id = ? AND isActive = 1";
       $this->prepare($stmt);
       $this->bindparameter(1, $zmachineID);
       $this->execute();
       return $this->fetchData();
   }
   
   //count no. of site on cashiermachineinfo
   public function checksitecount($zsiteid)
   {
       $stmt = "SELECT COUNT(POSAccountNo) AS ctrsite  FROM cashiermachineinfo WHERE POSAccountNo = ? AND isActive = 1";
       $this->prepare($stmt);
       $this->bindparameter(1, $zsiteid);
       $this->execute();
       return $this->fetchData();
   }
   
    /**
    * checks if account password was already updated
    */
   function checkpwdexpired($zaid, $zpassword)
   {
       $stmt = "SELECT COUNT(*) AS ctrpwd FROM accounts WHERE AID = ? AND Password = ? AND Status = 6 AND ForChangePassword = 0";
       $this->prepare($stmt);
       $this->bindparameter(1, $zaid);
       $this->bindparameter(2, $zpassword);
       $this->execute();
       return $this->fetchData();
   }
   
   function getStatus($uname)
   {
       $stmt = "SELECT Status FROM accounts WHERE UserName = ? ";
       $this->prepare($stmt);
       $this->bindparameter(1, $uname);
       $this->execute();
       return $this->fetchData();
   }
   /**
    * Get AID and Password for Change Password
    * @param type $username
    * @return type
    * @date January 12, 2015
    */
   private function getAIDAndPassword ($username) 
   {
       $stmt = "SELECT AID, Password FROM accounts WHERE UserName = ?";
       $this->prepare($stmt);
       $this->bindparameter(1, $username);
       $this->execute();
       return $this->fetchData();
   }
   /**
    * Check if new password exists in recent passwords
    * @param type $password
    * @param type $username
    * @return type
    * @author Mark Kenneth Esguerra
    * @date January 12, 2015
    */
   public function checkRecentPasswords ($password, $AID)
   {
       $stmt = "SELECT COUNT(AID) as Count 
                FROM accountsrecentpasswords 
                WHERE AID = ? AND Password = ?";
       $this->prepare($stmt);
       $this->bindparameter(1, $AID);
       $this->bindparameter(2, $password);
       $this->execute();
       
       return $this->fetchData();
   }
   /**
    * Count the Recent Password of a User
    * @param int $AID 
    * @author Mark Kenneth Esguerra
    * @date January 12, 2015
    */
   function countRecentPasswordsByAID ($AID)
   {
       $stmt = "SELECT COUNT(AID) AS Count 
                FROM accountsrecentpasswords 
                WHERE AID = ?";
       $this->prepare($stmt);
       $this->bindparameter(1, $AID);
       $this->execute();
       
       return $this->fetchData();
   }
   /**
    * Get the Recent Old Password
    * @param type $AID
    * @return type
    * @author Mark Kenneth Esguerra
    * @date January 12, 2015
    */
   private function getOldestRecentPassword ($AID) 
   {
       $stmt = "SELECT Password 
                FROM accountsrecentpasswords 
                WHERE AID = ? 
                ORDER BY DateCreated ASC 
                LIMIT 0, 1 ";
       $this->prepare($stmt);
       $this->bindparameter(1, $AID);
       $this->execute();
       
       return $this->fetchData();
   }
}
?>
