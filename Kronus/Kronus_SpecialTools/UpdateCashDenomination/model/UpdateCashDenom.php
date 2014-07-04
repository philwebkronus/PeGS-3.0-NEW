<?php

include "PDOLibrary.php";

class UpdateCashDenom extends PDOLibrary{
    
    function __construct($sconectionstring) 
    {
        parent::__construct($sconectionstring);          
    }   
      
      // account creation: virtual cashier to be used in KAPI
      function updatesitedenom($siteid,$siteid2)
      {
          $this->begintrans();
          $this->prepare("UPDATE sitedenomination SET MinDenominationValue = '500.0000', DateUpdated = NOW(6) WHERE DenominationName = 'Regular' AND SiteID = ? AND Status = 1");
          $this->bindparameter(1, $siteid);
          if($this->execute())
          {
              $this->prepare("UPDATE sitedenomination SET MinDenominationValue = '500.0000', DateUpdated = NOW(6) WHERE DenominationName = 'Regular VIP' AND SiteID = ? AND Status = 1");
              $this->bindparameter(1, $siteid2);
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
      
      // account creation: virtual cashier to be used in KAPI
      function revertsitedenom($siteid,$siteid2)
      {
          $this->begintrans();
          $this->prepare("UPDATE sitedenomination SET MinDenominationValue = '500.0000', DateUpdated = NOW(6) WHERE DenominationName = 'Regular' AND SiteID = ? AND Status = 1");
          $this->bindparameter(1, $siteid);
          if($this->execute())
          {
              $this->prepare("UPDATE sitedenomination SET MinDenominationValue = '5000.0000', DateUpdated = NOW(6) WHERE DenominationName = 'Regular VIP' AND SiteID = ? AND Status = 1");
              $this->bindparameter(1, $siteid2);
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
      
      function getAllSites(){
          $stmt = "SELECT SiteID FROM sites ORDER BY SiteID ASC";
          $this->executeQuery($stmt);
          return  $this->fetchAllData();
      }
      
      
      function getSiteDenomCount($siteid){
          $stmt = "SELECT COUNT(SiteID) AS Count FROM sitedenomination WHERE SiteID = ?";
          $this->prepare($stmt);
          $this->bindparameter(1, $siteid);
          $this->execute();
          return $this->fetchData();
      }
      
}
?>
