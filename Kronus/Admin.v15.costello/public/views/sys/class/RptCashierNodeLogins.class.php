<?php

/**
 * Created By: Mark Nicolas Atangan
 * Created On: September 2, 2015
 * Purpose: process for other requested reports (Cashier Node Logins)
 */
include 'DbHandler.class.php';
ini_set('display_errors',true);
ini_set('log_errors',true);

class RptCashierNodeLogins extends DBHandler
{
    public function __construct($connectionString) 
    {
        parent::__construct($connectionString);
    }
    
    //count for audit trailfor selected date range
    function countCashierNodeLogins($zdatefrom, $zdateto)
    {
     $stmt = "SELECT Count(DISTINCT (adt.TransDetails), (s.SiteID)) as cashierLoginCount
                    FROM npos.audittrail adt INNER JOIN npos.siteaccounts sa ON adt.AID = sa.AID
                        INNER JOIN npos.sites s ON sa.SiteID = s.SiteID
                    WHERE adt.TransDateTime >= '".$zdatefrom."' AND adt.TransDateTime <= '".$zdateto."' AND sa.Status
                        AND adt.AID IN (SELECT AID FROM npos.accounts WHERE AccountTypeID = 4) 
                        AND adt.AuditTrailFunctionID IN (1)"; 
        $this->prepare($stmt);
        $this->bindparameter(1, $zdatefrom);
        $this->bindparameter(2, $zdateto);
        //$this->bindparameter(3, $zacctype);
        $this->execute();
        unset($listacctypes);
        unset($listchildaccs);
        return $this->fetchData();
    } 
    //select all audit trail for selected date range
    function viewCashierNodeLogins($zdatefrom, $zdateto, $zstart, $zlimit, $zsort ,$zdirection)
    {
         if($zstart == null && $zlimit == null)
        {
         $stmt =    "SELECT s.SiteCode, adt.TransDetails
                    FROM npos.audittrail adt INNER JOIN npos.siteaccounts sa ON adt.AID = sa.AID
                        INNER JOIN npos.sites s ON sa.SiteID = s.SiteID
                    WHERE adt.TransDateTime >= '".$zdatefrom."' AND adt.TransDateTime <= '".$zdateto."' AND sa.Status
                        AND adt.AID IN (SELECT AID FROM npos.accounts WHERE AccountTypeID = 4) 
                        AND adt.AuditTrailFunctionID IN (1) GROUP By adt.TransDetails, s.SiteCode";   
        }
        else
        {
        //query for viewing Cashier node login
        $stmt = "SELECT s.SiteCode, adt.TransDetails
                    FROM npos.audittrail adt INNER JOIN npos.siteaccounts sa ON adt.AID = sa.AID
                        INNER JOIN npos.sites s ON sa.SiteID = s.SiteID
                    WHERE adt.TransDateTime >= '".$zdatefrom."' AND adt.TransDateTime <= '".$zdateto."' 
                        AND sa.Status AND adt.AID IN 
                        (SELECT AID FROM npos.accounts WHERE AccountTypeID = 4) AND adt.AuditTrailFunctionID IN (1)
                    GROUP By adt.TransDetails, s.SiteCode
                    ORDER BY ".$zsort." ".$zdirection." "
                    . "LIMIT ".$zstart.",".$zlimit."";
        }
        //validate export to pdf/excel      
        $this->prepare($stmt);
        $this->bindparameter(1, $zdatefrom);
        $this->bindparameter(2, $zdateto);
        //$this->bindparameter(3, $zacctype);
        $this->execute();
        unset($listacctypes);
        unset($listchildaccs);
        return $this->fetchAllData();
        
    }
}

?>