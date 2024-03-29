<?php

/*
 *  Created by : Lea Tuazon
 *  Date : June 1, 2011
 *  Holds all process for Site Maintenance such as add site, update site details,
 *  Activation and Deactivation of Sites
 *  Modified By: Edson L. Perez
 */

include "DbHandler.class.php";

class SiteManagement extends DBHandler{
     function __construct($sconectionstring)
      {
          parent::__construct($sconectionstring);
      }

      // site creation: insert record in sites table
      function insertinsite($zSiteName,$zSiteCode,$zOwnerAID,$zStatus,
              $zSiteDescription,$zSiteAlias,$zIslandId,$zRegionID,$zProvinceID,
              $zCityID,$zBarangayID,$zSiteAddress,$zCTO, $zpasscode, $zdatecreated,
              $zaid, $rdenomdefaults, $zistestsite, $zcontactno, $ztmtab, $zsrrtab, $zesafetab, $zsiteClassification)
      {
          //INSERT RECORD IN SITE  TABLE; default value for SiteGroupID = 1; INSERT RECORD IN SITEDETAILS TABLE
               $this->begintrans();
               //$zSiteGroupID = 1;
               $this->prepare("INSERT INTO sites(SiteName,SiteCode,OwnerAID,Status,isTestSite, TMTab, SRRTab, ESafeTab, SiteClassificationID) VALUES(?,?,?,?,?,?,?,?,?)");
               $this->bindparameter(1, $zSiteName);
               $this->bindparameter(2, $zSiteCode);
               $this->bindparameter(3, $zOwnerAID);
               $this->bindparameter(4, $zStatus);
               $this->bindparameter(5, $zistestsite);
               $this->bindparameter(6, $ztmtab);
               $this->bindparameter(7, $zsrrtab);
               $this->bindparameter(8, $zesafetab);
               $this->bindparameter(9, $zsiteClassification);
               //$this->bindparameter(5, $zSiteGroupID);
               $this->execute();
               $siteid = $this->insertedid();
               $this->prepare("INSERT INTO sitedetails(SiteID,SiteDescription,SiteAlias,IslandId,RegionID,ProvinceID,CityID,BarangayID,SiteAddress,CTO, PassCode, ContactNumber)
                               VALUES(?,?,?,?,?,?,?,?,?,?,?,?)");
               $this->bindparameter(1, $siteid);
               $this->bindparameter(2, $zSiteDescription);
               $this->bindparameter(3, $zSiteAlias);
               $this->bindparameter(4, $zIslandId);
               $this->bindparameter(5, $zRegionID);
               $this->bindparameter(6, $zProvinceID);
               $this->bindparameter(7, $zCityID);
               $this->bindparameter(8, $zBarangayID);
               $this->bindparameter(9, $zSiteAddress);
               $this->bindparameter(10,$zCTO);
               $this->bindparameter(11, $zpasscode.$siteid);
               $this->bindparameter(12, $zcontactno);
               if($this->execute())
               {
                     $sitedetilsid = $this->insertedid();
                     $this->prepare("INSERT INTO cashiermachinecounts(SiteID, CashierMachineCount, DateCreated, CreatedByAID) VALUES (?,1,now_usec(),?)");
                     $this->bindparameter(1, $siteid);
                     $this->bindparameter(2, $zaid);
                     if($this->execute())
                     {
                         try
                         {
                           $this->prepare("INSERT INTO sitedenomination(SiteID, DenominationName, MinDenominationValue, MaxDenominationValue, DenominationType, DateCreated, CreatedByAID) VALUES(?,?,?,?,?,?,?)");
                           foreach ($rdenomdefaults as $results)
                           {
                             $zdenomname = $results['DenominationName'];
                             $zdenommin = $results['MinDenominationValue'];
                             $rdenommax = $results['MaxDenominationValue'];
                             $zdenomtype = $results['DenominationType'];

                             $this->bindparameter(1, $siteid);
                             $this->bindparameter(2, $zdenomname);
                             $this->bindparameter(3, $zdenommin);
                             $this->bindparameter(4, $rdenommax);
                             $this->bindparameter(5, $zdenomtype);
                             $this->bindparameter(6, $zdatecreated);
                             $this->bindparameter(7, $zaid);
                             try
                             {
                               $this->execute();
                             }
                             catch (PDOException $e)
                             {
                                 $this->rollbacktrans();
                             }
                           }

                           $this->committrans();

                         }catch(PDOException $e) {
                             $this->rollbacktrans();
                             var_dump($e->getMessage()); exit;
                         }
                         return $siteid;
                     }
                     else
                     {
                         $this->rollbacktrans();
                         return 0;
                     }
               }
               else
                  $this->rollbacktrans();
                  return 0;
      }
//        function checksiteclassification($SiteID)
//      {
//          $stmt = "SELECT SiteClassificationID as SiteClassification from sites where SiteID = ?";
//        $this->prepare($stmt);
//        $this->bindparameter(1, $SiteID);
//        $this->execute();
//        return $this->fetchData();
//      }
        function checkifterminalsessionexist($SiteID)
      {
          $stmt = "SELECT COUNT(ts.TerminalID) as count FROM terminalsessions ts 
                            INNER JOIN terminals t 
                            ON ts.TerminalID=t.TerminalID 
                    WHERE t.SiteID = ?";
        $this->prepare($stmt);
        $this->bindparameter(1, $SiteID);
        $this->execute();
        return $this->fetchData();
      }
         function updatesiteclassification($zsiteclassification, $zsiteID)
      {
          $this->prepare("UPDATE sites SET SiteClassificationID = ?  WHERE SiteID = ?");
          $this->bindparameter(1, $zsiteclassification);
          $this->bindparameter(2, $zsiteID);
          $this->execute();
          return $this->rowCount();
      }
        function selectsiteclassification($zsiteID)
      {
        $stmt = "SELECT SiteClassificationID FROM sites WHERE SiteID=?" ;
        $this->prepare($stmt);
        $this->bindparameter(1, $zsiteID);
        $this->execute();
        return $this->fetchData();
      }
       function updatesitedetails2($zSiteID,$zSiteName,$zSiteCode,$zOwnerAID,
              $zSiteGroupID, $zSiteDescription, $zSiteAlias,$zIslandId,$zRegionID,
              $zProvinceID,$zCityID,$zBarangayID,$zSiteAddress,$zCTO, $zpasscode, $zstatus, $zoldownerAID, $zistestsite, $zcontactno, $ztmtab, $zsrrtab, $zesafetab, $zSiteClassification)
      {
         //update site table, sitedetails and insert in siteaccounts
         if($zOwnerAID > 0)
         {
              $zAID = $zOwnerAID;
         }
         else
              $zAID = null;

         $this->begintrans();
         $this->prepare("UPDATE sites SET SiteName = ?, SiteCode = ?,OwnerAID = ? ,SiteGroupID = ?, isTestSite = ?, TMTab = ?, SRRTab = ?, ESafeTab = ?, SiteClassificationID=? WHERE SiteID = ?");
         $this->bindparameter(1, $zSiteName);
         $this->bindparameter(2, $zSiteCode);
         $this->bindparameter(3, $zAID);
         $this->bindparameter(4, $zSiteGroupID);
         $this->bindparameter(5, $zistestsite);
         $this->bindparameter(6, $ztmtab);
         $this->bindparameter(7, $zsrrtab);
         $this->bindparameter(8, $zesafetab);
         $this->bindparameter(9, $zSiteClassification);
         $this->bindparameter(10, $zSiteID);
         $this->execute();
         $isexecute =$this->rowCount();
         if($zAID > 1)
         {
             //count if operator has assigned site and is active
             $this->prepare("SELECT COUNT(*) FROM siteaccounts WHERE SiteID = ? AND AID = ? AND Status = 1");
             $this->bindparameter(1, $zSiteID);
             $this->bindparameter(2, $zAID);
             $this->execute();

             //check if site has already assigned to its operator
             if($this->hasRows() == 0)
             {
                    $this->prepare("INSERT INTO siteaccounts(SiteID,AID,Status) VALUES (?,?,?)");
                    $this->bindparameter(1, $zSiteID);
                    $this->bindparameter(2, $zAID);
                    $this->bindparameter(3, $zstatus);

                    try {
                        $this->execute();
                    } catch (PDOException $e) {
                        $this->rollbacktrans();
                        return 0;
                    }
             }

             $this->prepare("UPDATE sitedetails SET SiteDescription = ?,
                   SiteAlias = ?,IslandID =? ,RegionID =? ,ProvinceID=?,CityID=?,
                   BarangayID=?,SiteAddress=?,CTO=?, PassCode = ?, ContactNumber = ? WHERE SiteID = ?");
             $this->bindparameter(1, $zSiteDescription);
             $this->bindparameter(2, $zSiteAlias);
             $this->bindparameter(3, $zIslandId);
             $this->bindparameter(4, $zRegionID);
             $this->bindparameter(5, $zProvinceID);
             $this->bindparameter(6, $zCityID);
             $this->bindparameter(7, $zBarangayID);
             $this->bindparameter(8, $zSiteAddress);
             $this->bindparameter(9, $zCTO);
             $this->bindparameter(10, $zpasscode);
             $this->bindparameter(11, $zcontactno);
             $this->bindparameter(12,$zSiteID);

             $this->execute();
             $ifdetexecute = $this->rowCount();
             if(($isexecute > 0) or ( $ifdetexecute > 0))
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
      
      
      //site update : update site and sitedetails and insert into siteaccounts
      function updatesitedetails($zSiteID,$zSiteName,$zSiteCode,$zOwnerAID,
              $zSiteGroupID, $zSiteDescription, $zSiteAlias,$zIslandId,$zRegionID,
              $zProvinceID,$zCityID,$zBarangayID,$zSiteAddress,$zCTO, $zpasscode, $zstatus, $zoldownerAID, $zistestsite, $zcontactno, $ztmtab, $zsrrtab, $zesafetab)
      {
         //update site table, sitedetails and insert in siteaccounts
         if($zOwnerAID > 0)
         {
              $zAID = $zOwnerAID;
         }
         else
              $zAID = null;

         $this->begintrans();
         $this->prepare("UPDATE sites SET SiteName = ?, SiteCode = ?,OwnerAID = ? ,SiteGroupID = ?, isTestSite = ?, TMTab = ?, SRRTab = ?, ESafeTab = ? WHERE SiteID = ?");
         $this->bindparameter(1, $zSiteName);
         $this->bindparameter(2, $zSiteCode);
         $this->bindparameter(3, $zAID);
         $this->bindparameter(4, $zSiteGroupID);
         $this->bindparameter(5, $zistestsite);
         $this->bindparameter(6, $ztmtab);
         $this->bindparameter(7, $zsrrtab);
         $this->bindparameter(8, $zesafetab);
         $this->bindparameter(9, $zSiteID);
         $this->execute();
         $isexecute =$this->rowCount();
         if($zAID > 1)
         {
             //count if operator has assigned site and is active
             $this->prepare("SELECT COUNT(AID) FROM siteaccounts WHERE SiteID = ? AND AID = ? AND Status = 1");
             $this->bindparameter(1, $zSiteID);
             $this->bindparameter(2, $zAID);
             $this->execute();

             //check if site has already assigned to its operator
             if($this->hasRows() == 0)
             {
                    $this->prepare("INSERT INTO siteaccounts(SiteID,AID,Status) VALUES (?,?,?)");
                    $this->bindparameter(1, $zSiteID);
                    $this->bindparameter(2, $zAID);
                    $this->bindparameter(3, $zstatus);

                    try {
                        $this->execute();
                    } catch (PDOException $e) {
                        $this->rollbacktrans();
                        return 0;
                    }
             }

             $this->prepare("UPDATE sitedetails SET SiteDescription = ?,
                   SiteAlias = ?,IslandID =? ,RegionID =? ,ProvinceID=?,CityID=?,
                   BarangayID=?,SiteAddress=?,CTO=?, PassCode = ?, ContactNumber = ? WHERE SiteID = ?");
             $this->bindparameter(1, $zSiteDescription);
             $this->bindparameter(2, $zSiteAlias);
             $this->bindparameter(3, $zIslandId);
             $this->bindparameter(4, $zRegionID);
             $this->bindparameter(5, $zProvinceID);
             $this->bindparameter(6, $zCityID);
             $this->bindparameter(7, $zBarangayID);
             $this->bindparameter(8, $zSiteAddress);
             $this->bindparameter(9, $zCTO);
             $this->bindparameter(10, $zpasscode);
             $this->bindparameter(11, $zcontactno);
             $this->bindparameter(12,$zSiteID);

             $this->execute();
             $ifdetexecute = $this->rowCount();
             if(($isexecute > 0) or ( $ifdetexecute > 0))
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

      //site update: update site status
      function updatestatus($zSiteID,$zStatus)
      {
          $this->prepare("UPDATE sites SET Status = ?  WHERE SiteID = ?");
          $this->bindparameter(1, $zStatus);
          $this->bindparameter(2, $zSiteID);
          $this->execute();
          return $this->rowCount();
      }

      //display all islands
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
           $stmt = "Select ProvinceID, ProvinceName from ref_provinces where RegionID = '".$zregionID."' ORDER BY ProvinceName";
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

      //display all barangay's based on selected city
      function showbrgy($zcityID)
      {
          $stmt = "SELECT BarangayID, BarangayName FROM ref_barangay where CityID = '".$zcityID."' ORDER BY BarangayName";
          $this->executeQuery($stmt);
          return $this->fetchAllData();
      }

      //display all site class
      function showsiteclass()
      {
          $stmt = "SELECT SiteClassificationID, Description FROM ref_siteclassification ORDER BY Description" ;
          $this->executeQuery($stmt);
          return $this->fetchAllData();
      }

      //display all site groups
      function showsitegroups()
      {
          $stmt = "SELECT SiteGroupID, SiteGroupsName FROM sitegroups ORDER BY SiteGroupsName" ;
          $this->executeQuery($stmt);
          return $this->fetchAllData();
      }

      //getallaccounts
      function selectallaccounts($zAcctTypeID)
      {
       $stmt = "SELECT AID,UserName,Password,AccountTypeID,Status,LoginAttempts,SessionNoExpire,ForChangePassword,WithPasskey FROM accounts
           WHERE Status IN(1,6)  AND  AccountTypeID = '".$zAcctTypeID."' ORDER BY UserName";
       $this->executeQuery($stmt);
       return $this->fetchAllData();
      }

      function getOwnerStatus($zAID)
      {
       $this->prepare("SELECT Status FROM accounts WHERE AID = :aid");
       $xparams = array(':aid' => $zAID);
       $this->executewithparams($xparams);
        return $this->fetchData();
      }

      //display all sites
      function viewsitedetails($zsiteID)
      {
        if($zsiteID > 0)
        {
              $stmt = "SELECT a.SiteID,a.SiteName,a.SiteCode,a.OwnerAID,a.Status, if(isnull(a.POSAccountNo), '0000000000', a.POSAccountNo) as POS, a.isTestSite, b.SiteDescription,b.SiteAlias,b.SiteAddress,b.IslandID,b.RegionID,b.ProvinceID,b.CityID,
                   b.BarangayID,b.CTO, b.PassCode, b.ContactNumber, c.IslandName,d.RegionName,e.ProvinceName,f.CityName,g.BarangayName,i.UserName, a.TMTab, a.SRRTab, a.ESafeTab, a.SiteClassificationID FROM sites a
                   INNER JOIN sitedetails b  on a.SiteID = b.SiteID
                   INNER JOIN ref_islands c on b.IslandID = c.IslandID
                   INNER JOIN ref_regions d on b.RegionID = d.RegionID
                   INNER JOIN ref_provinces e on b.ProvinceID = e.ProvinceID
                   INNER JOIN ref_cities f on b.CityID = f.CityID
                   INNER JOIN ref_barangay g on b.BarangayID = g.BarangayID
                   INNER JOIN accounts i on a.OwnerAID = i.AID 
                   WHERE a.SiteID =  '".$zsiteID."'";
              $this->executeQuery($stmt);
              $this->_row = $this->fetchAllData();
              if(count($this->_row == 0))
              {
                 $stmt = "SELECT a.SiteID,a.SiteName,a.SiteCode,a.OwnerAID,a.Status, a.isTestSite, if(isnull(a.POSAccountNo), '0000000000', a.POSAccountNo) as POS,b.SiteDescription,b.SiteAlias,b.SiteAddress,b.IslandID,b.RegionID,b.ProvinceID,b.CityID,
                   b.BarangayID,b.CTO, b.PassCode,  b.ContactNumber, c.IslandName,d.RegionName,e.ProvinceName,f.CityName,g.BarangayName, a.TMTab, a.SRRTab, a.ESafeTab, a.SiteClassificationID FROM sites a
                   INNER JOIN sitedetails b  on a.SiteID = b.SiteID
                   INNER JOIN ref_islands c on b.IslandID = c.IslandID
                   INNER JOIN ref_regions d on b.RegionID = d.RegionID
                   INNER JOIN ref_provinces e on b.ProvinceID = e.ProvinceID
                   INNER JOIN ref_cities f on b.CityID = f.CityID
                   INNER JOIN ref_barangay g on b.BarangayID = g.BarangayID
                   WHERE a.SiteID =  '".$zsiteID."'";
                 $this->executeQuery($stmt);
                 $this->_row = $this->fetchAllData();
              }
              //--> must return array results for displaying data through json_encode
        }
        else
        {
            $stmt = "SELECT a.SiteID,a.SiteName,a.SiteCode,a.OwnerAID,a.Status, a.isTestSite,
                     if(isnull(a.POSAccountNo), '0000000000', a.POSAccountNo) as POS,b.SiteDescription,
                     b.SiteAlias,b.SiteAddress,b.IslandID,b.RegionID,b.ProvinceID,b.CityID,b.BarangayID, b.ContactNumber, a.TMTab, a.SRRTab, a.ESafeTab, a.SiteClassificationID
                     FROM sites a INNER JOIN sitedetails b WHERE a.SiteID = b.SiteID ORDER BY a.SiteCode" ;
            $this->executeQuery($stmt);
            $this->_row = $this->fetchAllData();
        }
        return $this->_row;
     }
//count all site records for pagination
      function countsitedetails()
      {
        $stmt = "SELECT COUNT(a.SiteID) as count FROM sites a
                 INNER JOIN sitedetails b WHERE a.SiteID = b.SiteID AND a.SiteID <> 1";
        $this->executeQuery($stmt);
        $this->_row = $this->fetchData();
        return $this->_row;
     }

      //display all sites based on start and limit (for pagination)
      function viewsitepage($zsiteID, $zstart, $zlimit, $zdirection, $zsort)
      {
        if($zsiteID > 0)
        {
            $stmt = "SELECT a.SiteID,a.SiteName,a.SiteCode,a.OwnerAID,a.Status,b.SiteDescription,b.SiteAlias,b.SiteAddress,b.IslandID,b.RegionID,b.ProvinceID,b.CityID,
                   b.BarangayID,c.IslandName,d.RegionName,e.ProvinceName,f.CityName,g.BarangayName,h.SiteGroupID,i.UserName, a.SiteClassificationID FROM sites a
                   INNER JOIN sitedetails b  on a.SiteID = b.SiteID INNER JOIN ref_islands c on b.IslandID = c.IslandID INNER JOIN ref_regions d on b.RegionID = d.RegionID
                   INNER JOIN ref_provinces e on b.ProvinceID = e.ProvinceID INNER JOIN ref_cities f on b.CityID = f.CityID INNER JOIN ref_barangay g on b.BarangayID = g.BarangayID
                   INNER JOIN sitegroups h on a.SiteGroupID = h.SiteGroupID INNER JOIN accounts i on a.OwnerAID = i.AID 
                   WHERE a.SiteID =  '".$zsiteID."'";
        }
        else
        {
            $stmt = "SELECT a.SiteID,a.SiteName,a.SiteCode,a.OwnerAID,a.Status, if(isnull(a.POSAccountNo), '0000000000', a.POSAccountNo) as POS, b.SiteDescription,b.SiteAlias,b.SiteAddress,b.IslandID,b.RegionID,b.ProvinceID,b.CityID,b.BarangayID, a.SiteClassificationID 
                FROM sites a INNER JOIN sitedetails b ON a.SiteID = b.SiteID WHERE a.SiteID <> 1 ORDER BY ".$zsort." ".$zdirection." LIMIT ".$zstart.", ".$zlimit."";
        }
        $this->executeQuery($stmt);
        $this->_row = $this->fetchAllData();
        return $this->_row;
     }

    /* For Site DEnominations */

    //get all default denominations
    function getdefaultdenoms()
    {
        $stmt = "SELECT DenominationID, DenominationName, MinDenominationValue, MaxDenominationValue, DenominationType FROM ref_denominationdefaults";
        $this->prepare($stmt);
        $this->execute();
        return $this->fetchAllData();
    }

    //get all default denomination amounts
    function getdenomamounts()
    {
        $stmt = "SELECT DenominationID, Amount FROM ref_denominations";
        $this->prepare($stmt);
        $this->execute();
        return $this->fetchAllData();
    }
     //get all default denomination amounts
    function chksiteamount($zsiteID)
    {
        $stmt = "SELECT COUNT(SiteAmountID) as siteCount, SiteAmountID, LoadAmountDivisible FROM siteamountinfo WHERE SiteID = ? AND Status = 1";
        $this->prepare($stmt);
        $this->bindparameter(1, $zsiteID);
        $this->execute();
        return $this->fetchAllData();
    }
         //get all default denomination amounts
    function insertsiteamount($zsiteID,$zloadamt,$zCreatedByAID)
    {
        $stmt = "INSERT INTO siteamountinfo (SiteID, LoadAmountDivisible,Status,DateCreated,CreatedByAID) VALUES (?,?,1,NOW(6),?)";
        $this->prepare($stmt);
        $this->bindparameter(1, $zsiteID);
        $this->bindparameter(2, $zloadamt);
        $this->bindparameter(3, $zCreatedByAID);
        $this->execute();
        return $this->fetchAllData();
    }
        function updatesiteamount($zsiteID,$zloadamt,$zUpdatedByAID,$siteAmtID)
    {
        $stmt = "UPDATE siteamountinfo SET SiteID = ?, LoadAmountDivisible = ? ,Status = 1 ,DateUpdated = NOW(6) , UpdatedByAID = ? WHERE SiteAmountID = ?";
        $this->prepare($stmt);
        $this->bindparameter(1, $zsiteID);
        $this->bindparameter(2, $zloadamt);
        $this->bindparameter(3, $zUpdatedByAID);
        $this->bindparameter(4, $siteAmtID);
        $this->execute();
        return $this->fetchAllData();
    }

    //get site denominations
    function getsitedenoms($zsiteID)
    {
        $stmt = "SELECT DenominationName, MinDenominationValue, MaxDenominationValue, DenominationType FROM sitedenomination WHERE SiteID = ?";
        $this->prepare($stmt);
        $this->bindparameter(1, $zsiteID);
        $this->execute();
        return $this->fetchAllData();
    }

    //update denomination
    function updatedenomination($zdenominationvalues, $zsiteID, $zaid)
    {
        $this->begintrans();
        try
        {
          $this->prepare("UPDATE sitedenomination SET MinDenominationValue = ?, MaxDenominationValue = ?, UpdatedByAID = ?, DateUpdated = now_usec() WHERE DenominationName = ? AND SiteID = ?");
          foreach($zdenominationvalues as $results)
          {
            $denomname = $results['DenominationName'];
            $mininitial = $results['MinInitialValue'];
            $maxinitial = $results['MaxInitialValue'];

            $this->bindparameter(1, $mininitial);
            $this->bindparameter(2, $maxinitial);
            $this->bindparameter(3, $zaid);
            $this->bindparameter(4, $denomname);
            $this->bindparameter(5, $zsiteID);
            $this->execute();
            $xcount = $xcount + $this->rowCount();
          }
          if($xcount > 0)
          {
            $this->committrans();
            $xsaved = 1;
            return $xsaved;
          }
          else
          {
            $this->rollbacktrans();
            return 0;
          }

        }
        catch (PDOException $e)
        {
          $this->rollbacktrans();
          return 0;
        }
    }

    function getsitecode($zsiteID)
    {
        $stmt = "SELECT SiteCode from sites WHERE SiteID = ?";
        $this->prepare($stmt);
        $this->bindparameter(1, $zsiteID);
        $this->execute();
        return $this->fetchData();
    }


    function checkVirtualCashier($zsiteID)
    {
        $stmt = "SELECT DISTINCT(a.AccountTypeID) FROM siteaccounts sa
            INNER JOIN accounts a ON a.AID = sa.AID
            WHERE a.AccountTypeID IN (15,17) AND sa.SiteID = ? AND sa.Status=1 and a.Status=1";
        $this->prepare($stmt);
        $this->bindparameter(1, $zsiteID);
        $this->execute();
        $result = $this->fetchAllData();
        return $result;
    }

    //check if sitecode is exist
    function checksitecode($zsitecode)
    {
        $stmt = "SELECT COUNT(SiteID) as count FROM sites WHERE SiteCode = ?";
        $this->prepare($stmt);
        $this->bindparameter(1, $zsitecode);
        $this->execute();
        return $this->fetchData();
    }

    //update POSAccountNo upon successful of site creation
//    function insertposaccno($zposaccountno, $zsiteID)
//    {
//        $this->begintrans();
//        $this->prepare("UPDATE sites SET POSAccountNo = ? WHERE SiteID = ?");
//        $this->bindparameter(1, $zposaccountno);
//        $this->bindparameter(2, $zsiteID);
//        if($this->execute())
//        {
//            $this->committrans();
//            return 1;
//        }
//        else
//        {
//            $this->rollbacktrans();
//            return 0;
//        }
//    }

     //get all sites
    function getsites()
    {
      $stmt = "SELECT SiteID,SiteName,SiteCode, if(isnull(POSAccountNo), '0000000000', POSAccountNo) as POS, SiteClassificationID from sites ORDER BY SiteCode ASC";
      $this->executeQuery($stmt);
      return $this->fetchAllData();
    }

    /**
     * for displaying of account status name
     * @param int Status ID
     * @return string Status Name
     */
    function refsitestatusname($zstatus)
    {
        switch ($zstatus)
        {
            case 0:
                $zstatusname = "Inactive";
            break;
            case 1:
                $zstatusname = "Active";
            break;
            case 2:
                $zstatusname = "Suspended";
            break;
            case 3:
                $zstatusname = "Deactivated";
            break;
            default:
                $zstatusname = "Invalid Status";
            break;
        }
        return $zstatusname;
    }

    /**
     * Get locations name
     */
    function getlocationname($zarrisland, $zarrregion, $zarrprovince, $zarrcity, $zarrbrgy)
    {
        $islandID = implode(",", $zarrisland);
        $regionID = implode(",", $zarrregion);
        $provinceID = implode(",", $zarrprovince);
        $cityID = implode(",", $zarrcity);
        $barangayID = implode(",", $zarrbrgy);

        $stmt = "SELECT i.IslandName, r.RegionName, p.ProvinceName, c.CityName, b.BarangayName FROM ref_islands i
                 INNER JOIN ref_regions r ON r.IslandID = i.IslandID
                 INNER JOIN ref_provinces p ON p.RegionID = r.RegionID
                 INNER JOIN ref_cities c ON c.ProvinceID = p.ProvinceID
                 INNER JOIN ref_barangay b ON b.CityID = c.CityID
                 WHERE i.IslandID IN (".$islandID.") AND r.RegionID IN (".$regionID.")
                 AND p.ProvinceID IN(".$provinceID.") AND c.CityID IN (".$cityID.") AND b.BarangayID IN (".$barangayID.")
                 ORDER BY field(b.BarangayID, ".$barangayID.")";
        $this->prepare($stmt);
        $this->execute();
        return $this->fetchAllData();
    }

    //view all accounts --> for sending email notifications for operator
      function viewallaccounts($zaid)
      {
          $stmt = "Select a.UserName, b.Name, b.Email, b.LandLine, b.MobileNumber from accounts as a
                   INNER JOIN accountdetails as b ON a.AID = b.AID
                   WHERE a.AID = '".$zaid."'";
          $this->executeQuery($stmt);
          return  $this->fetchAllData();
      }

     /**
      * Updates status in sites and siteaccounts
      * @param array $zsiteID
      * @param int $zstatus
      * @return boolean
      */
     function changestatus($zsiteID, $zstatus)
     {
         $this->begintrans();
         $listsite = array();
         foreach($zsiteID as $val1)
         {
           array_push($listsite, "'".$val1."'");
         }
         $site = implode(',',$listsite);

         try
         {
             $stmt = "SELECT sa.AID FROM siteaccounts sa INNER JOIN accounts a ON sa.AID = a.AID WHERE sa.SiteID IN (".$site.")";
             $this->prepare($stmt);
             $this->execute();
             $rresult = $this->fetchAllData();
             $listacct = array();

             if(empty($rresult)){
                 $aid = '';
             }
             else{
                 foreach ($rresult as $row){
                    array_push($listacct, "'".$row['AID']."'");
                }

                $aid = implode(',', $listacct);
             }


             unset($listsite, $listacct);

             if($zstatus <> 1)
                 $accstatus = 2; //status code in siteaccounts if deactivated
             else
                 $accstatus = 1;

             try
             {
                $this->prepare("UPDATE sites SET Status = ? WHERE SiteID IN (".$site.")");
                $this->bindparameter(1, $zstatus);
                $this->execute();
                $isupdated = $this->rowCount();

                if($aid != ''){

                    //check if update was successsfull
                    if($isupdated > 0) {
                        $this->prepare("UPDATE siteaccounts SET Status = ? WHERE SiteID IN (".$site.") AND AID IN (".$aid.")");
                        $this->bindparameter(1, $accstatus);
                        $this->execute();
                        $isupdated2 = $this->rowCount();
                    }

                    if($accstatus <> 1)
                        $accstatuz = 0; //status code in accounts if terminated
                    else
                        $accstatuz = 1;

                    //check if update siteaccounts was successsfull
                    if($isupdated2 > 0) {
                        $this->prepare("UPDATE accounts SET Status = ? WHERE AID IN (".$aid.")");
                        $this->bindparameter(1, $accstatuz);
                        $this->execute();
                        $isupdated2 = $this->rowCount();
                    }
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
}
?>