<?php

/*
 * Created By: Edson L. Perez
 * Created On: October 28, 2011
 * Purpose: Class for other reports (audit trail)
 */
include 'DbHandler.class.php';
ini_set('display_errors',true);
ini_set('log_errors',true);

class RptOthers extends DBHandler
{
    public function __construct($connectionString) 
    {
        parent::__construct($connectionString);
    }
    
    //count for audit trailfor selected date range
    function countaudittrail($zdatefrom, $zdateto, $zacctypes, $zchildaccs)
    {
        $listacctypes = array();
        foreach($zacctypes as $val)
        {
            array_push($listacctypes, "'".$val."'");
        }
        $acctypes = implode(",",$listacctypes);
        
        $listchildaccs = array();
            foreach($zchildaccs as $val)
            {
                array_push($listchildaccs, "'".$val['AID']."'");
            }
        $childaccs = implode(",",$listchildaccs);
            
        //validate if has child accounts
        if(count($zacctypes) > 1)
        {
            $stmt = "SELECT COUNT(audit.ID) as ctraudit FROM audittrail audit
                    INNER JOIN accounts acc ON audit.AID = acc.AID
                    LEFT JOIN ref_auditfunctions ra ON audit.AuditTrailFunctionID = ra.AuditTrailFunctionID
                    WHERE audit.TransDateTime >= ? AND audit.TransDateTime < ? AND acc.AccountTypeID IN (".$acctypes.") 
                    AND audit.AID IN (".$childaccs.")";
        }
        else
        {
            $stracc = str_replace("'", "", $acctypes);
            //if account type is liason display only its account
            if($stracc == 10)
            {
                $stmt = "SELECT COUNT(audit.ID) as ctraudit FROM audittrail audit
                 INNER JOIN accounts acc ON audit.AID = acc.AID
                 LEFT JOIN ref_auditfunctions ra ON audit.AuditTrailFunctionID = ra.AuditTrailFunctionID
                 WHERE audit.TransDateTime >= ? AND audit.TransDateTime < ? AND acc.AccountTypeID IN (".$acctypes.") AND audit.AID IN (".$childaccs.")";
            }
            else
            {
                $stmt = "SELECT COUNT(audit.ID) as ctraudit FROM audittrail audit
                 INNER JOIN accounts acc ON audit.AID = acc.AID
                 LEFT JOIN ref_auditfunctions ra ON audit.AuditTrailFunctionID = ra.AuditTrailFunctionID
                 WHERE audit.TransDateTime >= ? AND audit.TransDateTime < ? AND acc.AccountTypeID IN (".$acctypes.")";
            }
        }
        $this->prepare($stmt);
        $this->bindparameter(1, $zdatefrom);
        $this->bindparameter(2, $zdateto);
        //$this->bindparameter(3, $zacctype);
        $this->execute();
        var_dump($stmt,$zdatefrom,$zdateto);exit;
        unset($listacctypes);
        unset($listchildaccs);
        return $this->fetchData();
    } 
    //select all audit trail for selected date range
    function viewaudittrail($zdatefrom, $zdateto, $zacctypes, $zchildaccs, $zstart, $zlimit, $zsort, $zdirection)
    {
        $listacctypes = array();
        foreach($zacctypes as $val)
        {
            array_push($listacctypes, "'".$val."'");
        }
        $acctypes = implode(",",$listacctypes);
        
        $listchildaccs = array();
        foreach($zchildaccs as $val)
        {
                    array_push($listchildaccs, "'".$val['AID']."'");
        }
        $childaccs = implode(",",$listchildaccs);
        //validate export to pdf/excel
        if($zstart == null && $zlimit == null)
        {
            if(count($zacctypes) > 1)
            {
                $stmt = "SELECT audit.AID, audit.TransDetails, audit.TransDateTime, audit.RemoteIP, acc.UserName, ra.AuditFunctionName FROM audittrail audit
                     INNER JOIN accounts acc ON audit.AID = acc.AID
                     LEFT JOIN ref_auditfunctions ra ON audit.AuditTrailFunctionID = ra.AuditTrailFunctionID
                     WHERE audit.TransDateTime >= ? AND audit.TransDateTime < ? AND acc.AccountTypeID IN (".$acctypes.") 
                         AND audit.AID IN (".$childaccs.") ORDER BY TransDateTime ASC";
            }   
            else
            {
                $stracc = str_replace("'", "", $acctypes);
                //if account type is liason display only its account
                if($stracc == 10)
                {
                    $stmt = "SELECT audit.AID, audit.TransDetails, audit.TransDateTime, audit.RemoteIP, acc.UserName, ra.AuditFunctionName FROM audittrail audit
                     INNER JOIN accounts acc ON audit.AID = acc.AID
                     LEFT JOIN ref_auditfunctions ra ON audit.AuditTrailFunctionID = ra.AuditTrailFunctionID
                     WHERE audit.TransDateTime >= ? AND audit.TransDateTime < ? AND acc.AccountTypeID IN (".$acctypes.") AND audit.AID IN (".$childaccs.") ORDER BY TransDateTime ASC";
                }
                else
                {
                    $stmt = "SELECT audit.AID, audit.TransDetails, audit.TransDateTime, audit.RemoteIP, acc.UserName, ra.AuditFunctionName FROM audittrail audit
                     INNER JOIN accounts acc ON audit.AID = acc.AID
                     LEFT JOIN ref_auditfunctions ra ON audit.AuditTrailFunctionID = ra.AuditTrailFunctionID
                     WHERE audit.TransDateTime >= ? AND audit.TransDateTime < ? AND acc.AccountTypeID IN (".$acctypes.") ORDER BY TransDateTime ASC";
                }
            }
        }
        else
        {
            if(count($zacctypes) > 1)
            {
                $stmt = "SELECT audit.AID, audit.TransDetails, audit.TransDateTime, audit.RemoteIP, acc.UserName, ra.AuditFunctionName FROM audittrail audit
                     INNER JOIN accounts acc ON audit.AID = acc.AID
                     LEFT JOIN ref_auditfunctions ra ON audit.AuditTrailFunctionID = ra.AuditTrailFunctionID
                     WHERE audit.TransDateTime >= ? AND audit.TransDateTime < ? AND acc.AccountTypeID IN (".$acctypes.") 
                     AND audit.AID IN (".$childaccs.") ORDER BY ".$zsort." ".$zdirection." LIMIT ".$zstart.",".$zlimit."";
            }  
            else
            {
                $stracc = str_replace("'", "", $acctypes);
                //if account type is liason display only its account
                if($stracc == 10)
                {
                    $stmt = "SELECT audit.AID, audit.TransDetails, audit.TransDateTime, audit.RemoteIP, acc.UserName, ra.AuditFunctionName FROM audittrail audit
                     INNER JOIN accounts acc ON audit.AID = acc.AID
                     LEFT JOIN ref_auditfunctions ra ON audit.AuditTrailFunctionID = ra.AuditTrailFunctionID
                     WHERE audit.TransDateTime >= ? AND audit.TransDateTime < ? AND acc.AccountTypeID IN (".$acctypes.") 
                     AND audit.AID IN (".$childaccs.") ORDER BY ".$zsort." ".$zdirection." LIMIT ".$zstart.",".$zlimit."";
                }
                else
                {
                    $stmt = "SELECT audit.AID, audit.TransDetails, audit.TransDateTime, audit.RemoteIP, acc.UserName, ra.AuditFunctionName FROM audittrail audit
                     INNER JOIN accounts acc ON audit.AID = acc.AID
                     LEFT JOIN ref_auditfunctions ra ON audit.AuditTrailFunctionID = ra.AuditTrailFunctionID
                     WHERE audit.TransDateTime >= ? AND audit.TransDateTime < ? AND acc.AccountTypeID IN (".$acctypes.") 
                     ORDER BY ".$zsort." ".$zdirection." LIMIT ".$zstart.",".$zlimit."";
                }
            }
        }
        $this->prepare($stmt);
        $this->bindparameter(1, $zdatefrom);
        $this->bindparameter(2, $zdateto);
        //$this->bindparameter(3, $zacctype);
        $this->execute();
        unset($listacctypes);
        unset($listchildaccs);
        return $this->fetchAllData();
    }
    
    function getchildaccounts($zaid)
    {
        $stmt = "SELECT SiteID FROM siteaccounts WHERE AID = ? AND Status = 1";
        $this->prepare($stmt);
        $this->bindparameter(1, $zaid);
        
        if($this->execute())
        {
            $zarrsites = $this->fetchAllData();
        }
        
        $listsite = array();
        
        foreach($zarrsites as $val)
        {
            array_push($listsite, "'".$val['SiteID']."'");
        }
        
        $siteID = implode(",", $listsite);
        
        $this->prepare("SELECT DISTINCT AID FROM siteaccounts WHERE SiteID IN (".$siteID.") AND Status = 1");
        $this->execute();
        return $this->fetchAllData();
    }
}

?>