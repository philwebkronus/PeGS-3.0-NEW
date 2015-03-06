<?php

/*
 * Author: Edson L. Perez
 * Date Created: September 08, 2011
 * Purpose: For batch creation of terminals
 * 
 */

include "DbHandler.class.php";

class BatchTerminalMgmt extends DBHandler{
    public function __construct($connectionString) 
    {
        parent::__construct($connectionString);
    }
    
    //this will insert on terminals, terminalervices
    function createbatchterminals($zproviderstatus,  
            $zterminalname, $zterminalcode, $zsiteID, $zstatus, 
            $zcreatedbyAID, $zisvip, $zserviceID, $ziscreated, $servicepassword, $hashedpwd)
    {
        $this->begintrans();
        try
        {
            $stmt = "SELECT TerminalID FROM terminals WHERE TerminalCode = ? AND SiteID = ?";
            $this->prepare($stmt);
            $this->bindparameter(1, $zterminalcode);
            $this->bindparameter(2, $zsiteID);
            $this->execute();
            $result = $this->fetchData();
            
            //Check if existing in terminals table
            if(isset($result['TerminalID']) && $result['TerminalID'] > 0){
                $terminalID = $result['TerminalID'];
            }
            else
            {
                $this->prepare("INSERT INTO terminals(TerminalName,TerminalCode,SiteID,DateCreated,CreatedByAID,Status,isVIP) 
                            VALUES(?,?,?,now_usec(),?,?,?)");
                $this->bindparameter(1, $zterminalname);
                $this->bindparameter(2, $zterminalcode);
                $this->bindparameter(3, $zsiteID);
                $this->bindparameter(4, $zcreatedbyAID);
                $this->bindparameter(5, $zstatus);
                $this->bindparameter(6, $zisvip);
                $this->execute();
                $terminalID = $this->insertedid(); 
            }
            
            try
            {
                $this->prepare("INSERT INTO terminalservices(TerminalID, ServiceID, Status, isCreated, ServicePassword, HashedServicePassword) 
                                VALUES (?,?,?,?,?,?)");
                $this->bindparameter(1, $terminalID);
                $this->bindparameter(2, $zserviceID);
                $this->bindparameter(3, $zproviderstatus); // status of creation to RTG
                $this->bindparameter(4, $ziscreated); // status if created on DB
                $this->bindparameter(5, $servicepassword);
                $this->bindparameter(6, $hashedpwd);
                $this->execute();
                
                try
                {
                    $this->committrans();
                    return $terminalID;
                }
                catch(PDOException $e) {
                    $this->rollbacktrans();
                    return 0;
                }
            } catch(PDOException $e) {
                 $this->rollbacktrans();
                 return 0;
            }
        } catch(PDOException $e) {
            $this->rollbacktrans();
            return 0;
        }
    }

    
    //logs for batch terminal creation
    function logbatchterminals($zsiteID, $zterminalcode, $zproviderstatus, $zaid, $zserviceID)
    {
        $this->begintrans();
        
        $this->prepare("INSERT INTO terminalbatchcreation(SiteID, ServiceProviderID, TerminalCode, 
            ServiceStatus, CreatedByAID, DateCreated) VALUES(?,?,?,?,?,now_usec())");
        $this->bindparameter(1, $zsiteID);
        $this->bindparameter(2, $zserviceID);
        $this->bindparameter(3, $zterminalcode);
        $this->bindparameter(4, $zproviderstatus);
        $this->bindparameter(5, $zaid);
        
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
    
    //select all sites id and name only
    function getallsiteswithid()
    {
        $stmt = "SELECT a.SiteID,a.SiteName, a.SiteCode  FROM sites a 
            INNER JOIN sitedetails b WHERE b.SiteID = a.SiteID  and a.Status = 1 ORDER BY a.SiteCode ASC";
        $this->executeQuery($stmt);
        return $this->fetchAllData();
    }
    
    //get all services
    function getallservices()
    {
        $stmt = "SELECT ServiceID, ServiceName, ServiceGroupID FROM ref_services WHERE Status = 1 ORDER BY ServiceName";
        $this->executeQuery($stmt);
        return $this->fetchAllData();
    }
    
    //get passcode from sitedetails to pass as password on RTG Player API
    function getpasscode($zsiteID)
    {
        $stmt = "SELECT PassCode from sitedetails where SiteID = ?";
        $this->prepare($stmt);
        $this->bindparameter(1, $zsiteID);
        $this->execute();
        return $this->fetchData();
    }
    
    //select Agent ID per site
    function getagentID($zsiteID)
    {
        $stmt = "SELECT ServiceAgentID FROM serviceagents WHERE SiteID = ?";
        $this->prepare($stmt);
        $this->bindparameter(1, $zsiteID);
        $this->execute();
        return $this->fetchData();
    }
    
    //select agentsession per agentID, that will be use on creating MG accounts
    function getagentsession($zagentID)
    {
        $stmt = "SELECT ServiceAgentSessionID FROM serviceagentsessions WHERE ServiceAgentID = ?";
        $this->prepare($stmt);
        $this->bindparameter(1, $zagentID);
        $this->execute();
        return $this->fetchData();
    }
    
    //get the last row of terminal table
    function getlastID($zsiteID, $zcodelen)
    {
        $stmt = "SELECT MAX(CAST(SUBSTR(TerminalCode, ".$zcodelen.") AS UNSIGNED )) AS tc  FROM terminals WHERE SiteID = ? AND isVIP = 0";  
        $this->prepare($stmt);
        $this->bindparameter(1, $zsiteID);
        $this->execute();
        return $this->fetchData();
    }
    
    /**
     * Update the status of new site into active
     * @param type $zsiteID
     * @param type $zgenpwdid
     * @return type 
     */
    function updateGenPwdBatch($zsiteID, $zgenpwdid)
    {
        $this->begintrans();
        try{
            $stmt = "UPDATE generatedpasswordbatch SET Status = 1, DateUsed = now_usec(), SiteID = ? 
                         WHERE Status = 0 AND SiteID IS NULL AND DateUsed IS NULL AND GeneratedPasswordBatchID = ?";
            $this->prepare($stmt);
            $this->bindparameter(1, $zsiteID);
            $this->bindparameter(2, $zgenpwdid);
            $this->execute();
            $isupdated = $this->rowCount();
            try{
                if($isupdated > 0)
                {
                    $this->committrans();
                    return 1;
                }                           
                else 
                   return 0;
            }catch(PDOException $e){
                $this->rollbacktrans();
                return 0;
            }
        }catch(PDOException $e) {
            $this->rollbacktrans();
            return 0;
        }
    }
    
    
       function getServiceUserMode($serviceID)
     {
           $stmt = "SELECT UserMode FROM ref_services 
                WHERE ServiceID = ?";
           $this->prepare($stmt);
           $this->bindparameter(1, $serviceID);
           $this->execute($stmt);
           $result =  $this->fetchData();
           return $result['UserMode'];
     }
     
     
     public function getServiceGrpNameById($service_id){
        $sql = 'SELECT rsg.ServiceGroupName FROM ref_services rs
                INNER JOIN ref_servicegroups rsg ON rs.ServiceGroupID = rsg.ServiceGroupID
                WHERE rs.ServiceID = ?';
        $this->prepare($sql);
        $this->bindparameter(1, $service_id);
        $this->execute($sql);
        $result =  $this->fetchData();
        if(!isset($result['ServiceGroupName']))
            return false;
        return $result['ServiceGroupName'];
    }
}

?>
