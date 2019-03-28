<?php
/*
 * Created By: Edson L. Perez
 * Date created: August 08, 2011
 * Description: DB Calls for Site Groups
 */
include "DbHandler.class.php";

ini_set('display_errors', true);
ini_set('log_errors', true);

class SiteGroupManagement extends DBHandler{
    public function __construct($sconnectionstring)
    {
        parent::__construct($sconnectionstring);
    }
    
    //method for creation of site group
    function createsitegroup($zsitegrpname, $zsitegrpdesc,$zdatecreated, $zaid)
    {
        $this->begintrans();
        $this->prepare("INSERT INTO sitegroups (SiteGroupsName, Description, DateCreated, CreatedByAID) VALUES (?, ?, ?, ?)");
        $this->bindparameter(1, $zsitegrpname);
        $this->bindparameter(2, $zsitegrpdesc);
        $this->bindparameter(3, $zdatecreated);
        $this->bindparameter(4, $zaid);
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
    
    //method for updating site group
    function updatesitegroup($zsitegrpname, $zsitegrpdesc, $zsitegrpID)
    {
        $this->begintrans();
        $this->prepare("UPDATE sitegroups SET SiteGroupsName = ?, Description = ? WHERE SiteGroupID = ?");
        $this->bindparameter(1, $zsitegrpname);
        $this->bindparameter(2, $zsitegrpdesc);
        $this->bindparameter(3, $zsitegrpID);
        $this->execute();
        $grpcount = $this->rowCount();
        if($grpcount > 0)
        {
            $this->committrans();
            return 1;
        }
        else{
            $this->rollbacktrans();
            return 0;
        }
    }
    
    //for pagination and selection of specific sitegroup
    function viewsitegrp($zsitegrpID, $zstart, $zlimit)
    {
        if($zsitegrpID > 0)
        {
          $stmt = "SELECT SiteGroupID, SiteGroupsName, Description FROM sitegroups WHERE SiteGroupID = ?";    
          $this->prepare($stmt);
          $this->bindparameter(1, $zsitegrpID);
        }
        
        else
        {
          $stmt = "SELECT SiteGroupID, SiteGroupsName, Description FROM sitegroups LIMIT ".$zstart.", ".$zlimit."";  
          $this->prepare($stmt);
        }
        
        $this->execute();
        return $this->fetchAllData();
    }
    
    //count number of site groups
    function countsitegroups()
    {
        $stmt = "SELECT COUNT(*) as count FROM sitegroups";
        $this->prepare($stmt);
        $this->execute();
        return $this->fetchData();
    }
    
    //for populating the combo box
    function getsitegrp()
    {
        $stmt= "SELECT SiteGroupID, SiteGroupsName FROM sitegroups ORDER BY SiteGroupsName ASC";
        $this->prepare($stmt);
        $this->execute();
        return $this->fetchAllData();
    }
}

?>
