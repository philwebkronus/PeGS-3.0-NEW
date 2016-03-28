<?php
/*
 * Created by: Sheryl S. Basbas
 * Date Created : March 8, 2012
 */
include __DIR__."/DbHandler.class.php";

class Override extends DBHandler
{
    
    public function __construct($sconectionstring)
    {
        parent::__construct($sconectionstring);
       
    }
    
    function getSites()
    {
        $stmt = "SELECT SiteID,SiteName,SiteCode, if(isnull(POSAccountNo), '0000000000', POSAccountNo) as POS from sites  ORDER BY SiteCode ASC";
        $this->executeQuery($stmt);
        return $this->fetchAllData(); 
    }
    
    function getSitesANDPOSAccountNo()
    {        
          $stmt = "SELECT SiteID, POSAccountNo FROM sites";
          $this->prepare($stmt);
          $this->execute();
          $row = $this->fetchAllData();
          return $row;
    }
   
    function getSiteNameANDAutoTopUp($siteID)
    {
        $stmt = "SELECT sb.AutoTopupEnabled, sb.TopupAmount, s.SiteName, s.SiteCode, if(isnull(s.POSAccountNo), '0000000000', s.POSAccountNo) as POS FROM sites s 
                    LEFT JOIN sitebalance sb ON s.SiteID = sb.SiteID WHERE s.SiteID = ? ORDER BY s.SiteID";
        $this->prepare($stmt);
        $this->bindparameter(1, $siteID);
        $this->execute();        
        return $this->fetchAllData();
    }
    
    function getSiteNamebyPOSAccount($POSAccount)
    {
        $stmt = "SELECT sb.AutoTopupEnabled, sb.TopupAmount, s.SiteName, s.SiteID, s.SiteCode, if(isnull(s.POSAccountNo), '0000000000', s.POSAccountNo) as POS FROM sites s 
                    LEFT JOIN sitebalance sb ON s.SiteID = sb.SiteID WHERE s.POSAccountNo = $POSAccount ORDER BY s.SiteID";
        $this->prepare($stmt);
        $this->execute(); 
        return $this->fetchAllData();
    }
    
    /**
     * Enables / Disables Auto Top-up
     * @param int $SiteID
     * @param bool $Enable
     * @param int $zAutoTopupAmt
     * @return boolean|int
     */
    function updateSitebalanceAutoToUp($SiteID,$Enable, $zAutoTopupAmt)
    {
        if((float)$zAutoTopupAmt <= 0.00){
            try{
                  $this->begintrans();
                  $this->prepare("UPDATE sitebalance SET AutoTopupEnabled = ? WHERE SiteID = ?");
                  $this->bindparameter(1, $Enable);
                  $this->bindparameter(2, $SiteID);
                  $this->execute();
                  $isupdated = $this->rowCount();
                  try{
                    $this->committrans();
                    return $isupdated;   
                  }catch(PDOException $e){
                    $this->rollbacktrans();
                    return 0;
                  }
            }catch(PDOException $e){
                  $this->rollbacktrans();
                  return 0;
            }
        } else {
            try{
                  $this->begintrans();
                  $this->prepare("UPDATE sitebalance SET AutoTopupEnabled = ?, TopupAmount = ? WHERE SiteID = ?");
                  $this->bindparameter(1, $Enable);
                  $this->bindparameter(2, $zAutoTopupAmt);
                  $this->bindparameter(3, $SiteID);
                  $this->execute();
                  $isupdated = $this->rowCount();
                  try{
                    $this->committrans();
                    return $isupdated;
                  }catch(PDOException $e){
                    $this->rollbacktrans();
                    return 0;
                  }
            }catch(PDOException $e){
                  $this->rollbacktrans();
                  return 0;
            }
        }
    }
}
?>
