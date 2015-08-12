<?php

/*
 * Created by : Lea Tuazon
 * Date Created : June 2, 2011
 *
 * Modified By: Edson L. Perez
 */

include "DbHandler.class.php";

class AccountManagement extends DBHandler{
      public function __construct($sconectionstring)
      {
          parent::__construct($sconectionstring);
      }

      //get all account types from ref_accounttypes
      function getallaccounttypes($zAccountType)
      {
          if($zAccountType > 0)
          {
             $stmt = "SELECT AccountTypeID, Name from ref_accounttypes where AccountTypeID = '".$zAccountType."' ORDER BY Name";
             $this->executeQuery($stmt);
          }
          else
          {
             $stmt = "SELECT AccountTypeID, Name from ref_accounttypes ORDER BY Name ";
             $this->executeQuery($stmt);              
          }
          return $this->fetchAllData();
      }

      //get all sites
      function getallsitesname()
      {
          $stmt = "SELECT SiteID,SiteName,SiteCode from sites WHERE Status = 1 AND OwnerAID IS NULL ORDER BY SiteName ";
          $this->executeQuery($stmt);
          return $this->fetchAllData();
      }

      // account creation: insert record in account,accountdetails,siteaccounts table
      function insertaccount($zUserName,$zPassword,$zAccountTypeID,$zPasskey,$zStatus,$zAccountGroupID,$zDateLastLogin,$zLoginAttempts,
            $zSessionNoExpire,$zDateCreated,$zCreatedByAID,$zForChangePassword, $zWithPasskey,$vAID,$vName,$vAddress ,
              $vEmail,$vLandline,$vMobileNumber,$vOption1,$vOption2, $zdesignationID, $zSiteID, $zdateissued, $zdateexpires)
      {
          $this->begintrans();
          $this->prepare("INSERT INTO accounts(UserName,Password,AccountTypeID,Passkey,Status,AccountGroupID,DateLastLogin,LoginAttempts,
              SessionNoExpire,DateCreated,CreatedByAID,ForChangePassword,WithPasskey, DatePasskeyIssued, DatePasskeyExpires) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)");
          $this->bindparameter(1, $zUserName);
          $this->bindparameter(2, $zPassword);
          $this->bindparameter(3, $zAccountTypeID);
          $this->bindparameter(4, $zPasskey);
          $this->bindparameter(5, $zStatus);
          $this->bindparameter(6, $zAccountGroupID);
          $this->bindparameter(7, $zDateLastLogin);
          $this->bindparameter(8, $zLoginAttempts);
          $this->bindparameter(9, $zSessionNoExpire);
          $this->bindparameter(10, $zDateCreated);
          $this->bindparameter(11, $zCreatedByAID);
          $this->bindparameter(12, $zForChangePassword);
          $this->bindparameter(13, $zWithPasskey);
          $this->bindparameter(14, $zdateissued);
          $this->bindparameter(15, $zdateexpires);
          if($this->execute())
          {
              $accountid = $this->insertedid();

              $this->prepare("INSERT INTO accountdetails(AID,Name,Address,Email,Landline,MobileNumber,Option1,Option2, DesignationID)
                  VALUES(?,?,?,?,?,?,?,?,?)");
              $this->bindparameter(1, $accountid);
              $this->bindparameter(2, $vName);
              $this->bindparameter(3, $vAddress);
              $this->bindparameter(4, $vEmail);
              $this->bindparameter(5, $vLandline);
              $this->bindparameter(6, $vMobileNumber);
              $this->bindparameter(7, $vOption1);
              $this->bindparameter(8, $vOption2);
              $this->bindparameter(9, $zdesignationID);
              if($this->execute())
              {
                  $accountdetailsid = $this->insertedid();              
                  $this->prepare("INSERT INTO siteaccounts(SiteID,AID,Status) VALUES (?,?,?)");
                  $this->bindparameter(1,$zSiteID);
                  $this->bindparameter(2,$accountid);
                  $this->bindparameter(3,$zStatus);

                  if($this->execute()) 
                  {
                     if($zAccountTypeID == 2)
                     {
                        $this->prepare("SELECT OwnerAID FROM sites WHERE SiteID = ?");
                        $this->bindparameter(1, $zSiteID);
                        $this->execute();
                        $hasowner = $this->fetchData();
                        
                        //check site if it has already a head operator / owner
                        if($hasowner['OwnerAID'] == null){
                            try{
                                $this->prepare("UPDATE sites SET OwnerAID = ? WHERE SiteID =?");
                                $this->bindparameter(1,$accountid);
                                $this->bindparameter(2,$zSiteID);
                                $this->execute();
                            } catch(PDOException $e) {
                                $this->rollbacktrans ();
                                return 0; 
                            }
                        } 
                     }

                     $this->committrans();
                     return $accountid;     
                  }
                  else
                  {
                     $this->rollbacktrans ();
                     return 0;                  
                  }
              }
              else
              {
                $this->rollbacktrans ();
                return 0;
              }
          }
          else
          {
              $this->rollbacktrans();
              return 0;
          }
      }

      // // account update: update record in account,accountdetails table
      function updateaccountdetails($zAID,$zAccountTypeID,$zName,$zAddress ,$zEmail,$zLandline,$zMobileNumber,$zOption1,$zOption2,$zWPasskey, $zdesignationID )
      {
            $this->begintrans();
            $this->prepare("UPDATE accounts SET AccountTypeID = ? , WithPasskey=? WHERE AID = ?");
            $this->bindparameter(1, $zAccountTypeID);
            $this->bindparameter(2, $zWPasskey);
            $this->bindparameter(3, $zAID);            
            if($this->execute())
            {
               $this->prepare("UPDATE accountdetails SET Name = ?, Address=?, Email = ?,Landline = ?,MobileNumber = ? ,Option1 = ?, Option2 = ?, DesignationID = ?  WHERE AID = ?");
               $this->bindparameter(1, $zName);
               $this->bindparameter(2, $zAddress);
               $this->bindparameter(3, $zEmail);
               $this->bindparameter(4, $zLandline);
               $this->bindparameter(5, $zMobileNumber);
               $this->bindparameter(6, $zOption1);
               $this->bindparameter(7, $zOption2);
               $this->bindparameter(8, $zdesignationID);
               $this->bindparameter(9, $zAID);               
               if($this->execute())
               {   
                   $updated = $this->rowCount();
                   $this->committrans();
                   if($updated == 0)
                     return 0;
                   else
                     return 1;
               }
               else
               {
                  $this->rollbacktrans ();
                  return 0;
               }
           }
           else
           {
             $this->rollbacktrans();
             return 0;
           }
      }

      //account update: update statis in accounts table
      function updatestatus($zAID,$zStatus)
      {
          $this->prepare("UPDATE accounts SET Status = ?  WHERE AID = ?");
          $this->bindparameter(1, $zStatus);
          $this->bindparameter(2, $zAID);
          $this->execute();
          return $this->rowCount();
      }

      //view all accounts --> accountedit(views) page
      function viewallaccounts($zaid)
      {
          $stmt = "Select a.UserName, a.AccountTypeID, a.WithPasskey, a.Status, a.DateCreated, b.Name, b.Address, b.Email, b.LandLine, b.MobileNumber, b.Option1, b.Option2, b.DesignationID, c.SiteID from accounts as a
                   INNER JOIN accountdetails as b ON a.AID = b.AID
                   LEFT JOIN siteaccounts as c ON a.AID = c.AID
                   WHERE a.AID = '".$zaid."' GROUP BY a.UserName ORDER BY a.UserName ";
          $this->executeQuery($stmt);
          return  $this->fetchAllData();
      }

      //view all accounts--> accountview(views) page
      function viewaccounts($zaccID,$zAcctType, $zpegs)
      {
         if($zaccID > 0)
         {
             $stmt = "Select a.AID, a.Status, a.UserName,a.AccountTypeID, b.Name, b.Email, b.Address from accounts as a INNER JOIN accountdetails as b ON a.AID = b.AID AND a.AID = '".$zaccID."'
                  ORDER BY a.UserName ASC";
         }
         else
         { 
            $pegs = null;
            $listpegs = array(); 
             if($zpegs == 0)
            {
                 $stmt = "Select a.AID, a.Status, a.UserName,a.AccountTypeID, b.Name, b.Email, b.Address from accounts as a INNER JOIN accountdetails as b ON a.AID = b.AID
                     WHERE a.AccountTypeID = '".$zAcctType."' ORDER BY a.UserName ASC"; 
            }
            elseif($zAcctType > 0 && count($zpegs) > 0)
            {
                foreach($zpegs as $val1)
                {
                    foreach($val1 as $value)
                    {
                        array_push($listpegs, "'".$value."'");
                    }
                }
                $pegs = implode(',',$listpegs);
                $stmt = "Select a.AID, a.Status, a.UserName,a.AccountTypeID, b.Name, b.Email, b.Address from accounts as a INNER JOIN accountdetails as b ON a.AID = b.AID
                      INNER JOIN siteaccounts c on c.AID = a.AID WHERE a.AccountTypeID = '".$zAcctType."' AND c.SiteID IN (".$pegs.") ORDER BY a.UserName ASC";
            }
           else 
            {
                 $stmt = "Select a.AID, a.Status, a.UserName,a.AccountTypeID, b.Name, b.Email, b.Address from accounts as a INNER JOIN accountdetails as b ON a.AID = b.AID
                     ORDER BY a.UserName ASC";               
            }          
         }

         $this->executeQuery($stmt);
         unset($listpegs);
         return  $this->fetchAllData();
      }

      //check if username is unique
      function checkusername($zUserName)
      {
         $stmt = "Select COUNT(*) FROM accounts WHERE username =?";
         $this->prepare($stmt);
         $this->bindparameter(1,$zUserName);
         $this->execute();
         return $this->hasRows();
      }
      
       //count all accounts for pagination
      function countviewaccounts($zaccID,$zAcctType,$zpegs)
      {
         if($zaccID > 1)
         {
             $stmt = "Select COUNT(*) as count from accounts as a INNER JOIN accountdetails as b ON a.AID = b.AID AND a.AID = '".$zaccID."'";
         }
         else
         {
            $pegs = null;
            $listpegs = array();
            
            if($zpegs == 0)
            {
                $stmt = "Select COUNT(*) as count from accounts as a INNER JOIN accountdetails as b ON a.AID = b.AID WHERE a.AccountTypeID = '".$zAcctType."'";
            }
            elseif($zAcctType > 0  && count($zpegs) > 0)
            {
                foreach($zpegs as $val1)
                {
                    foreach($val1 as $value)
                    {
                        array_push($listpegs, "'".$value."'");
                    }
                }
                $pegs = implode(',',$listpegs);      
                $stmt = "Select COUNT(*) as count from accounts as a INNER JOIN accountdetails as b ON a.AID = b.AID
                      INNER JOIN siteaccounts c on c.AID = a.AID  WHERE a.AccountTypeID = '".$zAcctType."' AND c.SiteID IN (".$pegs.")";
            }
            else
            {
                 $stmt = "Select COUNT(*) as count from accounts as a INNER JOIN accountdetails as b ON a.AID = b.AID";
            }  
         }
         $this->executeQuery($stmt);
         return $this->fetchData();
      }

      //display all accounts based on start and limit (for pagination)
      function viewlimitaccounts($zaccID, $zAcctType, $zStart, $zLimit, $zpegs)
      {
         if($zaccID > 1)
         {
             $stmt = "Select a.AID, a.Status, a.UserName,a.AccountTypeID, b.Name, b.Email, b.Address from accounts as a INNER JOIN accountdetails as b ON a.AID = b.AID AND a.AID = '".$zaccID."'
                  ORDER BY a.UserName ASC LIMIT ".$zStart.", ".$zLimit."";
         }
         else
         {
            $pegs = null;
            $listpegs = array();
            if($zpegs == 0)
            {
               $stmt = "Select a.AID, a.Status, a.UserName,a.AccountTypeID, b.Name, b.Email, b.Address from accounts as a INNER JOIN accountdetails as b ON a.AID = b.AID WHERE a.AccountTypeID = '".$zAcctType."'
                     ORDER BY a.UserName ASC LIMIT ".$zStart.", ".$zLimit."";
            }
            elseif($zAcctType > 0 && count($zpegs) > 0)
            {
                foreach($zpegs as $val1)
                {
                    foreach($val1 as $value)
                    {
                        array_push($listpegs, "'".$value."'");
                    }
                }
                
                $pegs = implode(',',$listpegs);
                $stmt = "Select a.AID, a.Status, a.UserName,a.AccountTypeID, b.Name, b.Email, b.Address from accounts as a INNER JOIN accountdetails as b ON a.AID = b.AID
                      INNER JOIN siteaccounts c on c.AID = a.AID WHERE a.AccountTypeID = '".$zAcctType."' AND c.SiteID IN(".$pegs.") ORDER BY a.UserName ASC LIMIT ".$zStart.", ".$zLimit."";
            }
            else
            {
                 $stmt = "Select a.AID, a.Status, a.UserName,a.AccountTypeID, b.Name, b.Email, b.Address from accounts as a INNER JOIN accountdetails as b ON a.AID = b.AID 
                     ORDER BY a.UserName ASC LIMIT ".$zStart.", ".$zLimit."";
            }         
         }
         $this->executeQuery($stmt);
         $this->_row = $this->fetchAllData();
         unset($listpegs);
         return $this->_row;
      }
      
      //get corporate designations for admin account ype only
      function getdesignations()
      {
          $stmt = "SELECT DesignationID, DesignationName FROM ref_designations";
          $this->prepare($stmt);
          $this->execute();
          return $this->fetchAllData();
      }
      
      //view all sites owned by operator (accountedit.php)
      function viewsitebyowner($zaid)
      {
         $stmt = "Select DISTINCT b.SiteID, c.SiteCode,c.SiteName from accounts as a 
                  INNER JOIN siteaccounts as b ON a.AID = b.AID 
                  INNER JOIN sites as c ON b.SiteID = c.SiteID
                  WHERE a.AID = '".$zaid."' AND b.Status = 1";
         $this->executeQuery($stmt);       
         return $this->fetchAllData();
      }
      
    function getOptrStatus($zAID)
      {
       $this->prepare("SELECT Status FROM accounts WHERE AID = :aid");
       $xparams = array(':aid' => $zAID);
       $this->executewithparams($xparams);
        return $this->fetchData();
      }
      
      //validation: delete sesion if exist when deactivating an account
      public function deletesession($aid)
      {
       $stmt = "DELETE FROM accountsessions WHERE AID = ?"   ;
       $this->prepare($stmt);
       $this->bindparameter(1, $aid);
       $this->execute();
      }
      
      function checkemail($zemail)
      {
          $stmt = "SELECT COUNT(AID) as emailcount FROM accountdetails WHERE Email LIKE '".$zemail."%'";
          $this->prepare($stmt);
          $this->execute();
          return $this->fetchData();
      }

      function checkexactemail($zemail)
      {
          $stmt = "SELECT COUNT(AID) as emailcount FROM accountdetails WHERE Email = '".$zemail."'";
          $this->prepare($stmt);
          $this->execute();
          return $this->fetchData();
      }

      function getmaxemail($zemail)
      {
          $stmt = "SELECT MAX(Email) AS maxEmail FROM accountdetails WHERE Email LIKE '".$zemail."%'";
          $this->prepare($stmt);
          $this->execute();
          return $this->fetchData();
      }

      //this will count accounts with GT 3 login attempts per account type selected
      function countloginattempts($zacctype, $zsites, $zowner)
      {
          $listsite = array();
          foreach($zsites as $val1)
          {
            array_push($listsite, "'".$val1['SiteID']."'");
          }
          $site = implode(',',$listsite);
          if($zowner == 2)
          {
              $stmt = "SELECT COUNT(*) as count FROM accounts as a 
                   INNER JOIN accountdetails as b ON a.AID = b.AID 
                   WHERE AccountTypeID = ? AND LoginAttempts >= 3 AND c.SiteID IN(".$site.")";
          }
          else
          {
              $stmt = "SELECT COUNT(*) as count FROM accounts as a 
                   INNER JOIN accountdetails as b ON a.AID = b.AID 
                   WHERE AccountTypeID = ? AND LoginAttempts >= 3";
          }

          $this->prepare($stmt);
          $this->bindparameter(1, $zacctype);
          $this->execute();
          unset($listsite, $zsites);
          return $this->fetchData();
      }
      
      //to view all accounts with GT 3 login attempts per account type selected
      function viewloginattempts($zacctype, $zsites, $zowner, $zstart, $zlimit)
      {
          $listsite = array();
          foreach($zsites as $val1)
          {
            array_push($listsite, "'".$val1['SiteID']."'");
          }
          $site = implode(',',$listsite);
          if($zowner == 2)
          {
              $stmt = "SELECT DISTINCT a.AID, a.UserName, b.Name from accounts as a
                       INNER JOIN accountdetails as b ON a.AID = b.AID
                       INNER JOIN siteaccounts c on c.AID = a.AID
                       WHERE AccountTypeID = ? and LoginAttempts >= 3 AND c.SiteID IN(".$site.") LIMIT ".$zstart.", ".$zlimit."";
          }
          else
          {
              $stmt = "SELECT DISTINCT a.AID, a.UserName, b.Name from accounts as a INNER JOIN accountdetails as b ON a.AID = b.AID 
                  WHERE AccountTypeID = ? and LoginAttempts >= 3 LIMIT ".$zstart.", ".$zlimit."";
          }
          $this->prepare($stmt);
          $this->bindparameter(1,$zacctype);
          $this->execute();
          unset($listsite, $zsites);
          return $this->fetchAllData();
      }
      
      ////to get all accounts with GT 3 login attempts to populate combo box
      function getloginattempts($zacctype, $zsites, $zowner)
      {
          $listsite = array();
          foreach($zsites as $val1)
          {
            array_push($listsite, "'".$val1['SiteID']."'");
          }
          $site = implode(',',$listsite);
          if($zowner == 2)
          {
              $stmt = "SELECT a.AID, a.UserName from accounts a
                       INNER JOIN siteaccounts c on c.AID = a.AID
                       WHERE AccountTypeID = ? and LoginAttempts >= 3 AND c.SiteID IN(".$site.")";
          }
          else
          {
              $stmt = "SELECT AID, UserName from accounts WHERE AccountTypeID = ? and LoginAttempts >= 3";
          }
          
          $this->prepare($stmt);
          $this->bindparameter(1,$zacctype);
          $this->execute();
          unset($listsite, $zsites);
          return $this->fetchAllData();
      }
      
      //to unlock accounts with GT 3 loginattempts
     function unlockaccount($zaid)
     {
          $this->prepare("UPDATE accounts SET LoginAttempts = 0  WHERE AID = ?");          
          $this->bindparameter(1, $zaid);
          $this->execute();
          return $this->rowCount();
     }
     
     //for pegs operations the sites that will populate the combobox are the unassigned site 
     //while on other acc types, all site will populate the combo box
     function getsitenoowner($zacctype)
     {
         if($zacctype == 8)
         {
           $stmt = "SELECT SiteID, SiteName, SiteCode from sites WHERE Status = 1 AND OwnerAID IS NULL ORDER BY SiteCode ASC";
         }
         else
         {
           $stmt = "SELECT SiteID,SiteName,SiteCode from sites WHERE Status = 1 ORDER BY SiteCode ASC";  
         }
         $this->prepare($stmt);
         $this->execute();
         return $this->fetchAllData();
     }     
     
     /**
      * Termination of a particular operator account only
      * @param int $zsiteID
      * @param int $zaccID
      * @return boolean  
      */
     function terminatechildaccounts( $zstatus, $zaccID)
     {
         $this->begintrans();
         
         try
         {   
             if($zstatus == 5)
                 $accstatus = 2; //status code in siteaccounts if deactivated
             else 
                 $accstatus = 1;
             
             try
             {
                //updates accounts table
                try {
                    $this->prepare("UPDATE accounts SET Status = ? WHERE AID = ?");
                    $this->bindparameter(1, $zstatus);
                    $this->bindparameter(2, $zaccID);
                    $this->execute();  
                    $accupdated = $this->rowCount();
                } catch(PDOException $e){
                    $this->rollbacktrans();
                    return 0;
                }             
                
                $isupdated = 0;
                //check if update of sites and accounts table was successfull
                if($accupdated > 0) {
                    $this->prepare("UPDATE siteaccounts SET Status = ?, DateDeactivated = now_usec()
                                    WHERE AID = ? AND Status = 1");
                    $this->bindparameter(1, $accstatus);
                     $this->bindparameter(2, $zaccID);
                    $this->execute();
                    $isupdated = 1;
                }
                
                try{
                     $this->committrans();
                     return $isupdated;
                } catch(PDOException $e) {
                    $this->rollbacktrans();
                    return 0;
                }
             }
             catch(PDOException $e)
             {
                $this->rollbacktrans();
                return 0;
             }
          }
          catch(PDOException $e)
          {
              $this->rollbacktrans();
              return 0;
          }
     }
     
     //get sites with liason; modified (01-12-12); for standalone terminal monitoring
     function getsiteacct($zaid)
     {
         $stmt = "SELECT * FROM (SELECT st.SiteID FROM siteaccounts st 
                  LEFT JOIN accounts a ON a.AID = st.AID WHERE ISNULL(st.DateDeactivated) 
                  AND a.AccountTypeID = ?) as x";
         $this->prepare($stmt);
         $this->bindparameter(1, $zaid);
         $this->execute();
         return $this->fetchAllData();
     }
     
     //get sites without liason; also used by standalone terminal monitoring
     function getsitenoacct($zarrsites)
     {
         $listsites = array();
         foreach($zarrsites as $row)
         {
             array_push($listsites, "'".$row['SiteID']."'");
         }
         $siteID = implode(",", $listsites);
         $stmt = "SELECT SiteID, SiteCode FROM sites WHERE SiteID NOT IN ('1',".$siteID.") AND Status = 1 ORDER BY SiteCode ASC";
         $this->prepare($stmt);
         $this->execute();
         unset($listsites);
         return $this->fetchAllData();
     }
     
     //for pagcor access; get SiteHO
     function getsiteho($zsiteID)
     {
         $stmt = "SELECT SiteID, SiteCode, SiteName FROM sites WHERE SiteID = ?";
         $this->prepare($stmt);
         $this->bindparameter(1, $zsiteID);
         $this->execute();
         return $this->fetchAllData();
     }
     
     //get active / password expired status of operator/s
     function getActiveOperator($zacctypeid){
         $stmt = "SELECT UserName, AID FROM accounts WHERE AccountTypeID = ? AND Status IN (1,6)";
         $this->prepare($stmt);
         $this->bindparameter(1, $zacctypeid);
         $this->execute();
         return $this->fetchAllData();
     }
     
     /**
      * Removal of assigned site
      * @param int $zaid
      * @param int $zsiteid
      * @return type 
      */
     function deactivateSiteAccount($zaid, $zsiteid){
         $this->begintrans();
         $this->prepare("UPDATE siteaccounts SET Status = 2, DateDeactivated = now_usec() 
                         WHERE AID = ? AND SiteID = ?");
         $this->bindparameter(1, $zaid);
         $this->bindparameter(2, $zsiteid);
         
         if($this->execute()) {
             $this->prepare("SELECT OwnerAID FROM sites WHERE SiteID = ?");
             $this->bindparameter(1, $zsiteid);
             $this->execute();
             $hasowner = $this->fetchData();
             if($hasowner['OwnerAID'] == $zaid){
                 $this->prepare("UPDATE sites SET OwnerAID = null WHERE SiteID = ?");
                 $this->bindparameter(1, $zsiteid);
                 if(!$this->execute()){
                     $this->rollbacktrans();
                     return 0;
                 }
             }
             $this->committrans();
             return 1;
         } else {
             $this->rollbacktrans();
             return 0;
         }
     }
     
     /**
      *for displaying of account status name
      *@param int Status ID
      *@return string Status Name
      */
     function showstatusname($zstatus)
        {
             switch($zstatus)
              {
                  case 0:
                      $zstatname = "Inactive";
                  break;
                  case 1:
                      $zstatname = "Active";
                  break;
                  case 2:
                      $zstatname = "Suspended";
                  break;
                  case 3:
                      $zstatname = "Locked(Attempts)";
                  break;
                  case 4:
                      $zstatname = "Locked(Admin)";
                  break;
                  case 5:
                      $zstatname = "Terminated";
                  break;
                  case 6:
                      $zstatname = "Password Expired";
                  break;
                  default:
                      $zstatname = "Invalid Status";
                  break;
              }

             return $zstatname;
       }
}
?>