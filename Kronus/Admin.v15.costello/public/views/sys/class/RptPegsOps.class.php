<?php

/*
 * Author: Edson L. Perez
 * Date Created: August 18, 2011
 * Purpose: For PEGS Operations Reports
 * 
 */
include 'DbHandler.class.php';
ini_set('display_errors', true);
ini_set('log_errors', true);

class RptPegsOps extends DBHandler{
     public function __construct($connectionString) {
         parent::__construct($connectionString);
     }

     /*****FOR Site Demographics Report *****/     
     
     //display all islands to populate comboboxes
     function showislands()
     {
          $stmt = "SELECT IslandID, IslandName FROM ref_islands ";
          $this->executeQuery($stmt);
          return $this->fetchAllData();   
     }
     
          //display all regions based on selected island
      function showregions($zislandID)
      {
          $stmt = "Select RegionID, RegionName from ref_regions where IslandID = '".$zislandID."' 
                   ORDER BY RegionName ASC";
           $this->executeQuery($stmt);
          return  $this->fetchAllData();         
      }

      //display all provinces based on selected regions
      function showprovinces($zregionID)
      {
           $stmt = "Select ProvinceID, ProvinceName from ref_provinces where RegionID = '".$zregionID."' 
                    ORDER BY ProvinceName ASC";
           $this->executeQuery($stmt);
          return $this->fetchAllData();          
      }

      //display all cities based on selected provinces
      function showcities($zprovinceID)
      {
          $stmt = "SELECT CityID, CityName FROM ref_cities where ProvinceID = '".$zprovinceID."' ORDER BY CityName";
          $this->executeQuery($stmt);
          return $this->fetchAllData();         
      }
      
      //get site demographic
      function getsitedemographic($zall, $zislandID, $zregionID, $zprovinceID, $zcityID, $zstart, $zlimit, $zsort, $zdirection)
      {
          if($zall > 0)
          {
            $stmt = "SELECT a.SiteID, a.SiteName, a.SiteCode, if(isnull(a.POSAccountNo), '0000000000', a.POSAccountNo) as POS FROM sites as a 
                     INNER JOIN sitedetails as b ON a.SiteID = b.SiteID 
                     WHERE a.SiteID <> 1 AND (b.IslandID = ? OR b.RegionID = ? OR b.ProvinceID = ? OR b.CityID = ?) 
                     ORDER BY ".$zsort." ".$zdirection." LIMIT ".$zstart.",".$zlimit."";
            $this->prepare($stmt);
            $this->bindparameter(1, $zislandID);
            $this->bindparameter(2, $zregionID);
            $this->bindparameter(3, $zprovinceID);
            $this->bindparameter(4, $zcityID);
         }
         else{
            $stmt = "SELECT a.SiteID, a.SiteName, a.SiteCode, if(isnull(a.POSAccountNo), '0000000000', a.POSAccountNo) as POS FROM sites as a 
                     INNER JOIN sitedetails as b ON a.SiteID = b.SiteID WHERE a.SiteID <> 1 
                     ORDER BY ".$zsort." ".$zdirection." LIMIT ".$zstart.",".$zlimit."";
            $this->prepare($stmt); 
         }
         $this->execute();
         return $this->fetchAllData();
      }
      
      //count site demographic
      function countsitedemographic($zall, $zislandID, $zregionID, $zprovinceID, $zcityID)
      {
          if($zall > 0)
          {
            $stmt = "SELECT COUNT(*) as count FROM sites as a 
              INNER JOIN sitedetails as b ON a.SiteID = b.SiteID WHERE a.SiteID <> 1 AND (b.IslandID = ? OR b.RegionID = ? OR b.ProvinceID = ? OR b.CityID = ?)";
            $this->prepare($stmt);
            $this->bindparameter(1, $zislandID);
            $this->bindparameter(2, $zregionID);
            $this->bindparameter(3, $zprovinceID);
            $this->bindparameter(4, $zcityID);
            
          }
          else
          {
            $stmt = "SELECT COUNT(*) as count FROM sites as a INNER JOIN sitedetails as b ON a.SiteID = b.SiteID WHERE a.SiteID <> 1";
            $this->prepare($stmt);  
          }
          $this->execute();
          return $this->fetchData();
      }
      
      //export data to pdf using tcpdf
      function exportdemographicpdf($zall, $zislandID, $zregionID, $zprovinceID, $zcityID)
      {
          if($zall > 0)
          {
            $stmt = "SELECT a.SiteID, a.SiteName, a.SiteCode, if(isnull(a.POSAccountNo), '0000000000', a.POSAccountNo) as POS FROM sites as a 
              INNER JOIN sitedetails as b ON a.SiteID = b.SiteID WHERE a.SiteID <> 1 AND (b.IslandID = ? OR b.RegionID = ? OR b.ProvinceID = ? OR b.CityID = ?) ORDER BY a.SiteName ASC";
            $this->prepare($stmt);
            $this->bindparameter(1, $zislandID);
            $this->bindparameter(2, $zregionID);
            $this->bindparameter(3, $zprovinceID);
            $this->bindparameter(4, $zcityID);
         }
         else
         {
            $stmt = "SELECT a.SiteID, a.SiteName, a.SiteCode, if(isnull(a.POSAccountNo), '0000000000', a.POSAccountNo) as POS FROM sites as a 
              INNER JOIN sitedetails as b ON a.SiteID = b.SiteID WHERE a.SiteID <> 1 ORDER BY a.SiteName ASC";
            $this->prepare($stmt); 
         }
         $this->execute();
         return $this->fetchAllData();
      }
      
      /*****END Site Demographics Report *****/     
      
      /***** For Site Listing Report *****/
      
      //count site listing (rptsitelisting.php)
      function countsitelisting($zall, $zislandID, $zregionID, $zprovinceID, $zcityID)
      {
          if($zall > 0)
          {
              $stmt = "SELECT COUNT(*) as count FROM sites a INNER JOIN sitedetails b on a.SiteID = b.SiteID 
                   INNER JOIN ref_islands c on b.IslandID = c.IslandID 
                   INNER JOIN ref_regions d on b.RegionID = d.RegionID 
                   INNER JOIN ref_provinces e on b.ProvinceID = e.ProvinceID 
                   INNER JOIN ref_cities f on b.CityID = f.CityID 
                   INNER JOIN ref_barangay g on b.BarangayID = g.BarangayID 
                   WHERE a.SiteID <> 1 AND (b.IslandID = ? OR b.RegionID = ? OR b.ProvinceID = ? OR b.CityID = ?)";
              $this->prepare($stmt);
              $this->bindparameter(1, $zislandID);
              $this->bindparameter(2, $zregionID);
              $this->bindparameter(3, $zprovinceID);
              $this->bindparameter(4, $zcityID);
          }
          else
          {
              $stmt = "SELECT COUNT(*) as count FROM sites a INNER JOIN sitedetails b on a.SiteID = b.SiteID 
                   INNER JOIN ref_islands c on b.IslandID = c.IslandID 
                   INNER JOIN ref_regions d on b.RegionID = d.RegionID 
                   INNER JOIN ref_provinces e on b.ProvinceID = e.ProvinceID 
                   INNER JOIN ref_cities f on b.CityID = f.CityID 
                   INNER JOIN ref_barangay g on b.BarangayID = g.BarangayID 
                   WHERE a.SiteID <> 1";
              $this->prepare($stmt);
          }
          
          $this->execute();
          return $this->fetchData();
      }
      
      //view site listing (rptsitelisting.php)
      function viewsitelisting($zall, $zislandID, $zregionID, $zprovinceID, $zcityID, $zstart, $zlimit, $zsort, $zdirection)
      {
          //exporting (Excel / PDF)
          if($zstart == null && $zlimit == null)
          {
              if($zall > 0)
              {
                  $stmt = "SELECT a.SiteID, a.SiteName, a.SiteCode, a.Status, if(isnull(a.POSAccountNo), '0000000000', a.POSAccountNo) as POS, b.SiteAddress, 
                       b.ContactNumber, c.IslandName,d.RegionName,e.ProvinceName,f.CityName,g.BarangayName FROM sites a
                       INNER JOIN sitedetails b  on a.SiteID = b.SiteID 
                       INNER JOIN ref_islands c on b.IslandID = c.IslandID 
                       INNER JOIN ref_regions d on b.RegionID = d.RegionID 
                       INNER JOIN ref_provinces e on b.ProvinceID = e.ProvinceID 
                       INNER JOIN ref_cities f on b.CityID = f.CityID 
                       INNER JOIN ref_barangay g on b.BarangayID = g.BarangayID 
                       WHERE a.SiteID <> 1 AND (b.IslandID = ? OR b.RegionID = ? OR b.ProvinceID = ? OR b.CityID = ?)
                       ORDER BY SiteID ASC";
                  $this->prepare($stmt);
                  $this->bindparameter(1, $zislandID);
                  $this->bindparameter(2, $zregionID);
                  $this->bindparameter(3, $zprovinceID);
                  $this->bindparameter(4, $zcityID);
              }
              else
              {
                  $stmt = "SELECT a.SiteID, a.SiteName, a.SiteCode, a.Status, if(isnull(a.POSAccountNo), '0000000000', a.POSAccountNo) as POS, b.SiteAddress, 
                       b.ContactNumber, c.IslandName,d.RegionName,e.ProvinceName,f.CityName,g.BarangayName FROM sites a
                       INNER JOIN sitedetails b  on a.SiteID = b.SiteID 
                       INNER JOIN ref_islands c on b.IslandID = c.IslandID 
                       INNER JOIN ref_regions d on b.RegionID = d.RegionID 
                       INNER JOIN ref_provinces e on b.ProvinceID = e.ProvinceID 
                       INNER JOIN ref_cities f on b.CityID = f.CityID 
                       INNER JOIN ref_barangay g on b.BarangayID = g.BarangayID 
                       WHERE a.SiteID <> 1
                       ORDER BY SiteID ASC";
                  $this->prepare($stmt);
              }
          }
          //jqGrid pagination
          else
          {
              if($zall > 0)
              {
                  $stmt = "SELECT a.SiteID, a.SiteName, a.SiteCode, a.Status, if(isnull(a.POSAccountNo), '0000000000', a.POSAccountNo) as POS, b.SiteAddress, 
                           b.ContactNumber, c.IslandName,d.RegionName,e.ProvinceName,f.CityName,g.BarangayName FROM sites a
                           INNER JOIN sitedetails b  on a.SiteID = b.SiteID 
                           INNER JOIN ref_islands c on b.IslandID = c.IslandID 
                           INNER JOIN ref_regions d on b.RegionID = d.RegionID 
                           INNER JOIN ref_provinces e on b.ProvinceID = e.ProvinceID 
                           INNER JOIN ref_cities f on b.CityID = f.CityID 
                           INNER JOIN ref_barangay g on b.BarangayID = g.BarangayID 
                           WHERE a.SiteID <> 1 AND (b.IslandID = ? OR b.RegionID = ? OR b.ProvinceID = ? OR b.CityID = ?)
                           ORDER BY ".$zsort." ".$zdirection."
                           LIMIT ".$zstart.",".$zlimit."";
                  $this->prepare($stmt);
                  $this->bindparameter(1, $zislandID);
                  $this->bindparameter(2, $zregionID);
                  $this->bindparameter(3, $zprovinceID);
                  $this->bindparameter(4, $zcityID);
              }
              else
              {
                  $stmt = "SELECT a.SiteID, a.SiteName, a.SiteCode, a.Status, if(isnull(a.POSAccountNo), '0000000000', a.POSAccountNo) as POS, b.SiteAddress, 
                           b.ContactNumber, c.IslandName,d.RegionName,e.ProvinceName,f.CityName,g.BarangayName FROM sites a
                           INNER JOIN sitedetails b  on a.SiteID = b.SiteID 
                           INNER JOIN ref_islands c on b.IslandID = c.IslandID 
                           INNER JOIN ref_regions d on b.RegionID = d.RegionID 
                           INNER JOIN ref_provinces e on b.ProvinceID = e.ProvinceID 
                           INNER JOIN ref_cities f on b.CityID = f.CityID 
                           INNER JOIN ref_barangay g on b.BarangayID = g.BarangayID 
                           WHERE a.SiteID <> 1
                           ORDER BY ".$zsort." ".$zdirection."
                           LIMIT ".$zstart.",".$zlimit."";
                  $this->prepare($stmt);
              }
              
          }
          $this->execute();
          return $this->fetchAllData();
      }
     
    /********** FOR TERMINAL LISTING *********/ 
      //get operator per site selected
      function getownerbysite($zsiteID)
      {
          $stmt = "SELECT ad.Name FROM sites a
                    INNER JOIN siteaccounts as b ON a.SiteID = b.SiteID 
                    INNER JOIN accounts c ON b.AID = c.AID
                    INNER JOIN accountdetails ad ON c.AID = ad.AID
                    WHERE a.SiteID = ? AND AccountTypeID = 2";
          $this->prepare($stmt);
          $this->bindparameter(1, $zsiteID);
          $this->execute();
          return $this->fetchAllData();
      }
      
      function countterminalbysite($zsiteID)
      {
          $stmt = "SELECT count(*) as ctrterminal FROM terminals WHERE SiteID = ? AND isVIP = 0";
          $this->prepare($stmt);
          $this->bindparameter(1, $zsiteID);
          $this->execute();
          return $this->fetchData();
      }
      function countterminallisting($zsiteID)
      {
          $stmt = "select count(*) as ctrterminal from (select distinct tid,tname,tcode,tsiteid,Service,ServiceTerminalID,ServiceTerminalAcct from
                   (((select TerminalID as tid, TerminalName as tname,TerminalCode as tcode,SiteID as tsiteid from terminals) as t 
                   inner join terminalservices ts on t.tid = ts.TerminalID ) 
                   left join (select ts.TerminalId as termid,ts.ServiceID as Service,ts.Status as stat,IF(ts.ServiceID = 9,tm.ServiceTerminalID,null) as ServiceTerminalID,IF(ts.ServiceID = 9,st.ServiceTerminalAccount,null) as ServiceTerminalAcct from terminalservices ts 
                   left join terminalmapping tm on ts.TerminalID = tm.TerminalID 
                   left join serviceterminals st on tm.ServiceTerminalID= st.ServiceTerminalID ) as mg on tid = termid ) where  stat = 1 and tsiteid = ? order by tid ) as ctr";
          $this->prepare($stmt);
          $this->bindparameter(1, $zsiteID);
          $this->execute();
          return $this->fetchData();
      }
      
      //get terminal code, status, provider and MG OC Account
      function getterminalbysite($zsiteID, $zstart, $zlimit, $zsort, $zdirection)
      {
          //check if exporting (excel / pdf)
          if($zstart == null && $zlimit == null)
          {
              $stmt = "select distinct tid, tcode, tstat, c.ServiceName, ServiceTerminalAcct,TerminalType from
                    (((select TerminalID as tid,TerminalName as tname,TerminalCode as tcode,SiteID as tsiteid, Status as tstat, TerminalType as TerminalType from terminals) as t 
                    inner join terminalservices ts on t.tid = ts.TerminalID ) 
                    left join (select ts.TerminalId as termid,ts.ServiceID as Service,ts.Status as stat,IF(ts.ServiceID = 9,tm.ServiceTerminalID,null) as ServiceTerminalID,IF(ts.ServiceID = 9,st.ServiceTerminalAccount,null) as ServiceTerminalAcct from terminalservices ts 
                    left join terminalmapping tm on ts.TerminalID = tm.TerminalID 
                    left join serviceterminals st on tm.ServiceTerminalID= st.ServiceTerminalID ) as mg on tid = termid ) 
                    left join ref_services c ON Service= c.ServiceID
                    where  stat = 1 and tsiteid = ? ORDER BY TerminalID ASC";
          }
          //jqgrid pagination
          else
          {
              $stmt = "select distinct tid, tcode, tstat, c.ServiceName, ServiceTerminalAcct, TerminalType from
                    (((select TerminalID as tid,TerminalName as tname,TerminalCode as tcode,SiteID as tsiteid, Status as tstat, TerminalType as TerminalType from terminals) as t 
                    inner join terminalservices ts on t.tid = ts.TerminalID ) 
                    left join (select ts.TerminalId as termid,ts.ServiceID as Service,ts.Status as stat,IF(ts.ServiceID = 9,tm.ServiceTerminalID,null) as ServiceTerminalID,IF(ts.ServiceID = 9,st.ServiceTerminalAccount,null) as ServiceTerminalAcct from terminalservices ts 
                    left join terminalmapping tm on ts.TerminalID = tm.TerminalID 
                    left join serviceterminals st on tm.ServiceTerminalID= st.ServiceTerminalID ) as mg on tid = termid ) 
                    left join ref_services c ON Service= c.ServiceID
                    where  stat = 1 and tsiteid = ? ORDER BY ".$zsort." ".$zdirection."
                    LIMIT ".$zstart.",".$zlimit."";
          }
          
          $this->prepare($stmt);
          $this->bindparameter(1, $zsiteID);
          $this->execute();
          return $this->fetchAllData();
      }
      
      /***** END Site Listing Report *****/
      /***** User Listing Report *******/
      
      //function for user listing report (jqgrid, excel)
      function viewuserlist($zsiteID, $zstart, $zlimit, $zsort, $zdirection)
      {
          //check if excel/pdf
          if($zstart == null && $zlimit == null)
          {
              //if choses site
              if($zsiteID > 0)
              {
                 $stmt = "SELECT DISTINCT a.AID, s.SiteName as 'Site Name',s.SiteCode as 'Site Code',
                          s.POSAccountNo as 'POS Account No.',a.UserName as 'User Name',at.Name as 'User Group', ad.Name,
                          a.DateCreated as  'Date Created',a.Status,sa.SiteID FROM siteaccounts sa 
                          INNER JOIN accounts a ON a.AID = sa.AID
                          INNER JOIN accountdetails ad ON ad.AID = sa.AID
                          INNER JOIN sites s ON s.SiteID = sa.SiteID
                          INNER JOIN ref_accounttypes at ON at.AccountTypeID = a.AccountTypeID   
                          WHERE sa.Status = 1 AND s.SiteID = ? AND s.SiteID <>1 AND a.AccountTypeID NOT IN (15,17)"; 
                 $this->prepare($stmt);
                 $this->bindparameter(1, $zsiteID);
              }
              else
              {
                 $stmt = "SELECT DISTINCT s.SiteName as 'Site Name',s.SiteCode as 'Site Code',
                          s.POSAccountNo as 'POS Account No.',a.UserName as 'User Name',at.Name as 'User Group', ad.Name,
                          a.DateCreated as  'Date Created',a.Status,sa.SiteID FROM siteaccounts sa 
                          INNER JOIN accounts a ON a.AID = sa.AID
                          INNER JOIN accountdetails ad ON ad.AID = sa.AID
                          INNER JOIN sites s ON s.SiteID = sa.SiteID
                          INNER JOIN ref_accounttypes at ON at.AccountTypeID = a.AccountTypeID   
                          WHERE sa.Status = 1 AND s.SiteID <>1 AND a.AccountTypeID NOT IN (15,17)"; 
                 $this->prepare($stmt); 
              }
          }
          else
          {
              if($zsiteID > 0)
              {
                 $stmt = "SELECT DISTINCT s.SiteName as 'Site Name',s.SiteCode as 'Site Code',
                          s.POSAccountNo as 'POS Account No.',a.UserName as 'User Name',at.Name as 'User Group', ad.Name,
                          a.DateCreated as  'Date Created',a.Status,sa.SiteID FROM siteaccounts sa 
                          INNER JOIN accounts a ON a.AID = sa.AID
                          INNER JOIN accountdetails ad ON ad.AID = sa.AID
                          INNER JOIN sites s ON s.SiteID = sa.SiteID
                          INNER JOIN ref_accounttypes at ON at.AccountTypeID = a.AccountTypeID   
                          WHERE sa.Status = 1 AND s.SiteID = ? AND s.SiteID <>1 AND a.AccountTypeID NOT IN (15,17) 
                          ORDER BY ".$zsort." ".$zdirection." LIMIT ".$zstart.", ".$zlimit.""; 
                 $this->prepare($stmt);
                 $this->bindparameter(1, $zsiteID); 
              }
              else
              {
                 $stmt = "SELECT DISTINCT s.SiteName as 'Site Name',s.SiteCode as 'Site Code',
                          s.POSAccountNo as 'POS Account No.',a.UserName as 'User Name',at.Name as 'User Group', ad.Name,
                          a.DateCreated as  'Date Created',a.Status,sa.SiteID FROM siteaccounts sa 
                          INNER JOIN accounts a ON a.AID = sa.AID
                          INNER JOIN accountdetails ad ON ad.AID = sa.AID
                          INNER JOIN sites s ON s.SiteID = sa.SiteID
                          INNER JOIN ref_accounttypes at ON at.AccountTypeID = a.AccountTypeID   
                          WHERE sa.Status = 1 AND s.SiteID <>1 AND a.AccountTypeID NOT IN (15,17)
                          ORDER BY ".$zsort." ".$zdirection." LIMIT ".$zstart.", ".$zlimit.""; 
                 $this->prepare($stmt);
              }
          }
          
          $this->execute();
          return $this->fetchAllData();
      }
      
      //counts user account list 
      function countuserlist($zsiteID)
      {
          if($zsiteID > 0)
          {
              $stmt = "SELECT COUNT(DISTINCT sa.AID, sa.SiteID) as ctruser FROM siteaccounts sa
                       INNER JOIN accounts a ON sa.AID = a.AID
                       INNER JOIN accountdetails ad ON ad.AID = sa.AID
                       WHERE sa.Status = 1 AND sa.SiteID <> 1 AND sa.SiteID = ? AND a.AccountTypeID NOT IN (15,17) ";
              $this->prepare($stmt);
              $this->bindparameter(1, $zsiteID);
          }
          else
          {
              $stmt = "SELECT COUNT(DISTINCT sa.AID, sa.SiteID) as ctruser FROM siteaccounts sa
                       INNER JOIN accounts a ON sa.AID = a.AID
                       INNER JOIN accountdetails ad ON ad.AID = sa.AID
                       WHERE sa.Status = 1 AND sa.SiteID <> 1 AND a.AccountTypeID NOT IN (15,17) ";
              $this->prepare($stmt);
          }
          
          $this->execute();
          return $this->fetchData();
      }
      
      
}

?>