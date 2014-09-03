<?php

include "PDOLibrary.php";

class CreateVCashier extends PDOLibrary{
    
    function __construct($sconectionstring) 
    {
        parent::__construct($sconectionstring);          
    }   
      
      // account creation: virtual cashier to be used in KAPI
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
      
      function getAllSites(){
          $stmt = "SELECT SiteID FROM sites ORDER BY SiteID ASC";
          $this->executeQuery($stmt);
          return  $this->fetchAllData();
      }
      
      function checkVirtualCashier($siteid){
          $stmt = "SELECT COUNT(sa.SiteID) AS Count FROM siteaccounts sa 
              INNER JOIN accounts a ON a.AID = sa.AID WHERE a.AccountTypeID = 15 AND SiteID = ?";
          $this->prepare($stmt);
          $this->bindparameter(1, $siteid);
          $this->execute();
          return $this->fetchData();
      }
      
      function getSiteCount(){
          $stmt = "SELECT SiteID FROM sites";
          $this->executeQuery($stmt);
          return $this->rowCount();
      }
      
      function getCountSiteaccounts(){
          $stmt = "SELECT DISTINCT(sa.SiteID) AS Count FROM siteaccounts sa 
              INNER JOIN accounts a ON a.AID = sa.AID INNER JOIN sites s 
              ON sa.SiteID = s.SiteID WHERE a.AccountTypeID = 15";
          $this->executeQuery($stmt);
          return $this->rowCount();
      }
      
      function getNoneVCashierSitesinSA(){
          $stmt = "SELECT DISTINCT(s.SiteID) FROM sites s LEFT JOIN siteaccounts sa 
              ON s.SiteID = sa.SiteID INNER JOIN accounts a ON a.AID = sa.AID 
              WHERE a.AccountTypeID != 15 ORDER BY s.SiteID ASC";
          $this->executeQuery($stmt);
          return  $this->fetchAllData();
      }
      
      function getNoneVCashierSitesinS(){
          $stmt = "SELECT DISTINCT(s.SiteID) FROM sites s LEFT JOIN siteaccounts sa 
              ON s.SiteID = sa.SiteID WHERE sa.SiteID IS NULL ORDER BY SiteID ASC";
          $this->executeQuery($stmt);
          return  $this->fetchAllData();
      }
      
      function getSiteCode($siteid){
           $stmt = "SELECT SiteCode from sites WHERE SiteID = ?";
            $this->prepare($stmt);
            $this->bindparameter(1, $siteid);
            $this->execute();
            return $this->fetchData();
      }
}
?>
