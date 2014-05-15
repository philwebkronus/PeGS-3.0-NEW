<?php

/* Created by : Lea Tuazon
 * Date Created: June 3, 2011
 * Description: Handles all terminal maintenance such as Terminal creation,Terminal Details update, activation
 *  and deactivation fo services, terminal mapping
 */
include "DbHandler.class.php";

class TerminalManagement extends DBHandler
{
      public function __construct($sconectionstring)
      {
          parent::__construct($sconectionstring);
      }

      //get all services
      function getallservices()
      {
          $stmt = "SELECT * FROM ref_services WHERE Status = 1 ORDER BY ServiceName";
          $this->executeQuery($stmt);
          return $this->fetchAllData();
      }

      //select all terminal accounts
      function getallterminals()
      {
          $stmt = "SELECT TerminalID,TerminalName,TerminalCode,SiteID,Status,isVIP FROM terminals ORDER BY TerminalID";
          $this->executeQuery($stmt);
          return $this->fetchAllData();
      }

      //select all sites id and name only
      function getallsiteswithid()
      {
         $stmt = "SELECT a.SiteID,a.SiteName, a.SiteCode  FROM sites a INNER JOIN sitedetails b WHERE b.SiteID = a.SiteID  AND a.Status = 1 ORDER BY a.SiteCode ASC";
         $this->executeQuery($stmt);
         return $this->fetchAllData();
      }

      //terminal account creation : insert record  in terminals; Status = default is 0
      function createterminalaccount($zTerminalName,$zTerminalCode,$zSiteID,$zDateCreated,$zCreatedByAID,$zStatus,$zisVIP)
      {
         $this->begintrans();
         $this->prepare("INSERT INTO terminals(TerminalName,TerminalCode,SiteID,DateCreated,CreatedByAID,Status,isVIP)VALUES(?,?,?,?,?,?,?)");
         $this->bindparameter(1, $zTerminalName);
         $this->bindparameter(2, $zTerminalCode);
         $this->bindparameter(3, $zSiteID);
         $this->bindparameter(4, $zDateCreated);
         $this->bindparameter(5, $zCreatedByAID);
         $this->bindparameter(6, $zStatus);
         $this->bindparameter(7, $zisVIP);
         if($this->execute())
         {
             $terminalID = $this->insertedid();
             $this->prepare("SELECT COUNT(*) AS count FROM terminalservices WHERE TerminalID = ? AND isCreated = 1");
             $this->bindparameter(1, $terminalID);
             $this->execute();
             if(count == 0)
             {
                 $this->committrans();
                 return $terminalID;
             }
             else
             {
                 $this->rollbacktrans();
                 return 0;
             }
         }
         else{
             $this->rollbacktrans();
             return 0;
         }
      }

      //terminal account update: update record in terminals;
      function updateterminalaccount($zTerminalName,$zTerminalCode,$zSiteID,$zTerminalID)
      {
        // $this->prepare("UPDATE terminals SET TerminalName =?,TerminalCode=?,SiteID=?, isVIP=? WHERE TerminalID = ?  ");
         $this->prepare("UPDATE terminals SET TerminalName =?,TerminalCode=?,SiteID=? WHERE TerminalID = ?  ");
         $this->bindparameter(1, $zTerminalName);
         $this->bindparameter(2, $zTerminalCode);
         $this->bindparameter(3, $zSiteID);
         //$this->bindparameter(4, $zisVIP);
         $this->bindparameter(4, $zTerminalID);
         $this->execute();
         return $this->rowCount();
      }

      //activation or de-activation of terminal account;When a terminal will be deactivated, all services assigned to it will also be deactivated
      function updateterminalstatus($zTerminalID,$zStatus)
      {
         
         $listerminal = array();
         foreach ($zTerminalID as $row)
         {
             array_push($listerminal, "'".$row."'");
         }
         $terminalID = implode(',', $listerminal);
         
         /**
          *Added on July 4, 2012
          * Check available sessions for the given terminals and prevent status
          * update on terminals with active sessions.
          *  
          */
         $this->prepare("SELECT TerminalID FROM terminalsessions WHERE TerminalID IN (".$terminalID.")");
         $this->execute();
         $record = $this->fetchAllData();
         
         if(sizeof($record) <= 0) {
         
            $this->prepare("UPDATE terminals SET Status = ?  WHERE TerminalID IN (".$terminalID.")");
            $this->bindparameter(1, $zStatus);
            //$this->bindparameter(2, $zTerminalID);
            $this->execute();
            unset($listerminal);
            return $this->rowCount();
         
         }
         else {
             
             return -1;
         }
         
      }

      //activation or de-activation of  services per terminal
      function updateterminalservicestatus($zStatus,$zTerminalID,$zServiceID, $zservicepwd, $zhashedpwd)
      {
         $this->prepare("UPDATE terminalservices SET Status = ?, ServicePassword = ?, HashedServicePassword = ? 
                         WHERE TerminalID = ? AND ServiceID = ?");
         $this->bindparameter(1, $zStatus);
         $this->bindparameter(2, $zservicepwd);
         $this->bindparameter(3, $zhashedpwd);
         $this->bindparameter(4, $zTerminalID);
         $this->bindparameter(5, $zServiceID);
         $this->execute();
         return $this->rowCount();
      }

      //assigning of services per terminal
      function assignservices($zTerminalID,$zServiceID,$zStatus, $ziscreated, $zpassword, $zhashedpwd)
      {
          $this->begintrans();
          
          try{
            $this->prepare('INSERT INTO terminalservices(TerminalID, ServiceID, Status, isCreated, ServicePassword, HashedServicePassword) VALUES (?,?,?,?,?,?)');
            $this->bindparameter(1, $zTerminalID);
            $this->bindparameter(2, $zServiceID);
            $this->bindparameter(3, $zStatus);
            $this->bindparameter(4, $ziscreated);
            $this->bindparameter(5, $zpassword);
            $this->bindparameter(6, $zhashedpwd);
            $this->execute();
            $this->committrans();
            return 1;
          }
          catch(PDOException $e){
            $this->rollbacktrans();  
            return 0;
          }
      }

      //check terminal if mapped in terminalmapping table
      function checkterminalifmapped($zTerminalID,$zServiceTerminalID)
      {
          $stmt = "SELECT COUNT(*) AS count FROM terminalmapping WHERE
              TerminalID = '".$zTerminalID."' AND  ServiceTerminalID = '".$zServiceTerminalID."' ";
          $this->executeQuery($stmt);
          return $this->fetchData();
      }

      //insert record in serviceagents table
      function createserviceagents($zUserName,$zPassword,$zStatus, $zsiteID)
      {
          $this->prepare('INSERT INTO serviceagents(Username,Password,Status,SiteID) VALUES (?,?,?,?)');
          $this->bindparameter(1, $zUserName);
          $this->bindparameter(2, $zPassword);
          $this->bindparameter(3, $zStatus);
          $this->bindparameter(4, $zsiteID);
          $this->execute();
          return $this->insertedid();
      }

      //get all serviceagents
      function selectallserviceagents()
      {
          $stmt = "SELECT ServiceAgentID,Username,Password FROM serviceagents where Status = 1 ORDER BY Username";
          $this->executeQuery($stmt);
          return $this->fetchAllData();
      }

      //create serviceterminal(OCAccounts) per serviceagentid
      function createserviceterminal($zServiceTerminalAccount,$zPassword,$zStatus,$zServiceAgentID)
      {
          $this->prepare('INSERT INTO serviceterminals(ServiceTerminalAccount,Password,Status,ServiceAgentID) VALUES (?,?,?,?)');
          $this->bindparameter(1, $zServiceTerminalAccount);
          $this->bindparameter(2, $zPassword);
          $this->bindparameter(3, $zStatus);
          $this->bindparameter(4, $zServiceAgentID);
          $this->execute();
          return $this->insertedid(); //return OC ID
      }

      //terminal mapping; one is to one relationship;
      function terminalmapping($zTerminalID,$zServiceTerminalID, $zserviceID)
      {
         $this->begintrans();
         $this->prepare('INSERT INTO terminalmapping(TerminalID,ServiceTerminalID) VALUES (?,?)');
         $this->bindparameter(1, $zTerminalID);
         $this->bindparameter(2, $zServiceTerminalID);
         if($this->execute())
         {
             $this->prepare("UPDATE terminalservices SET isCreated = 1 WHERE TerminalID = ? AND ServiceID = ? AND Status =1");
             $this->bindparameter(1, $zTerminalID);
             $this->bindparameter(2, $zserviceID);
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

      //view all terminal services
      function viewterminalservices($zterminalID,$zserviceid)
      {
        if($zserviceid > 0 && $zterminalID > 0)
        {
              $stmt = "SELECT a.ServiceGroupID, a.ServiceName, b.ServiceID, b.Status FROM ref_services a 
                       INNER JOIN terminalservices b ON a.ServiceID = b. ServiceID 
                       WHERE TerminalID = ? AND b.ServiceID = ? ORDER BY a.ServiceName";
              $this->prepare($stmt);
              $this->bindparameter(1, $zterminalID);
              $this->bindparameter(2, $zserviceid);
        }
        elseif($zterminalID > 0){
              $stmt = "SELECT a.ServiceGroupID, a.ServiceName, b.ServiceID, b.Status FROM ref_services a 
                       INNER JOIN terminalservices b ON a.ServiceID = b. ServiceID 
                       WHERE TerminalID = ? ORDER BY a.ServiceName";
              $this->prepare($stmt);
              $this->bindparameter(1, $zterminalID);
        }
        else
        {
              $stmt = "SELECT a.ServiceGroupID, a.ServiceName FROM ref_services a WHERE a.ServiceID = ?";
              $this->prepare($stmt);
              $this->bindparameter(1, $zserviceid);
        }
        
        $this->execute();
        return $this->fetchAllData();
      }

      //view all service agents created --> serviceagentview.php
      function viewterminalagents()
      {
          $stmt = "SELECT ServiceAgentID, Username FROM serviceagents ORDER BY Username";
          $this->executeQuery($stmt);
          return $this->fetchAllData();
      }

      //view all service terminal created --> serviceterminalview.php
      function viewserviceterminals()
      {
          $stmt = "SELECT a.ServiceTerminalID, a.ServiceTerminalAccount, a.ServiceAgentID, a.Status,
              b.Username FROM serviceterminals as a 
              INNER JOIN serviceagents AS b
              ON a.ServiceAgentID = b.ServiceAgentID ORDER BY a.ServiceTerminalAccount ASC";
          $this->executeQuery($stmt);
          return $this->fetchAllData();
      }

      //this selects all terminals by site id
      function selectterminals($zsiteID)
      {
          $stmt = "SELECT TerminalID, TerminalName, TerminalCode FROM terminals WHERE SiteID = '".$zsiteID."' ORDER BY TerminalID ASC";
          $this->executeQuery($stmt);
          return $this->fetchAllData();
      }

     //view terminal details
     function viewterminals($zterminalid)
      {
          if($zterminalid > 0)
          {
              $stmt = "SELECT TerminalID, TerminalName, TerminalCode, SiteID, Status, isVIP FROM terminals WHERE TerminalID = '".$zterminalid."' ORDER BY TerminalID ASC";
              $this->executeQuery($stmt);
              $this->_row = $this->fetchAllData();
          }
          else
          {
              $stmt = "SELECT TerminalID, TerminalName, TerminalCode, SiteID,Status FROM terminals ORDER BY TerminalID ASC";
              $this->executeQuery($stmt);
              $this->_row = $this->fetchAllData();
          }
          return $this->_row;
      }
      
      /* Created by : gvjagolino
      * Date Created: jan 3, 2013
      * Description: Get TerminalID using TerminalCode
      */
      function viewterminalsbyTerminalCode($zterminalname)
      {
          
              $stmt = "SELECT TerminalID FROM terminals WHERE TerminalCode = '".$zterminalname."' ORDER BY TerminalID ASC";
              $this->executeQuery($stmt);
              $this->_row = $this->fetchAllData();
          
          return $this->_row;
      }
      
     /* Created by : gvjagolino
      * Date Created: jan 3, 2013
      * Description: View Terminal Details
      */
     function viewterminaltype($zterminalid)
      {
              $stmt = "SELECT TerminalType FROM terminals WHERE TerminalID = '".$zterminalid."' ORDER BY TerminalID ASC";
              $this->executeQuery($stmt);
              $this->_row = $this->fetchAllData();
          
          return $this->_row;
      }
      
      /* Created by : gvjagolino
      * Date Created: jan 3, 2013
      * Description: Update Terminal Type
      */
      function updateterminaltype($terminaltype, $zterminalid, $zterminalid2)
      {
        $this->prepare("UPDATE terminals SET TerminalType=? WHERE TerminalID IN ($zterminalid, $zterminalid2)");
        $this->bindparameter(1, $terminaltype);
        $this->execute();
        return $this->rowCount();
      }

      //update username or password
      function agentupdate($zagentuname,$zagentpass,$zagentid, $zsiteID)
      {
       $this->prepare("UPDATE serviceagents SET Username=?, Password=?, SiteID = ? WHERE ServiceAgentID = ?");
       $this->bindparameter(1, $zagentuname);
       $this->bindparameter(2, $zagentpass);
       $this->bindparameter(3, $zsiteID);
       $this->bindparameter(4, $zagentid);
       $this->execute();
       return $this->rowCount();
      }

      //update service terminal status only
      function servicetermstatupd($zstatus,$zstsuid)
      {
       $this->prepare("UPDATE serviceterminals SET Status=? WHERE ServiceTerminalID=? ");
       $this->bindparameter(1, $zstatus);
       $this->bindparameter(2, $zstsuid);
       $this->execute();
       return $this->rowCount();
      }

      //update status for terminal mapped
      function termmapstat($zterminalmappedid,$zterminalmappedstat)
      {
       $this->prepare("UPDATE terminalmapping SET Status=? WHERE TerminalID=? ");
       $this->bindparameter(1, $zterminalmappedstat);
       $this->bindparameter(2, $zterminalmappedid);
       $this->execute();
       return $this->rowCount();
      }

      //view service terminal status --> serviceterminaledit.php, also use in viewing service terminals
      // per selection of ServiceTerminalAccount combobox
      function editserviceterminals($zserviceTermID)
      {
          $stmt = "SELECT a.ServiceTerminalID, a.ServiceTerminalAccount, a.ServiceAgentID, a.Status,
              b.Username FROM serviceterminals AS a INNER JOIN serviceagents AS b
              ON a.ServiceAgentID = b.ServiceAgentID WHERE ServiceTerminalID = '".$zserviceTermID."' ORDER BY a.ServiceTerminalAccount ASC";
          $this->executeQuery($stmt);
          return $this->fetchAllData();
      }

      //view service agent info --> serviceagentedit.php
      function editagent($zagentID)
      {
          $stmt = "SELECT ServiceAgentID, Username, Password, SiteID FROM serviceagents WHERE ServiceAgentID = '".$zagentID."'";
          $this->executeQuery($stmt);
          return $this->fetchAllData();
      }
      
      /**** Part For Pagination **/
      //count terminal details (for pagination)
      function countterminals($zsiteID)
      {
          $stmt = "SELECT COUNT(*) AS count FROM terminals WHERE SiteID = '".$zsiteID."'";
          $this->executeQuery($stmt);
          $this->_row = $this->fetchData();
          return $this->_row;
      }

     //view terminal details (for pagination)
     function viewterminalspage($zsiteID, $zstart, $zlimit)
      {
         if($zsiteID > 0)
         {
          $stmt = "SELECT TerminalID, TerminalName, TerminalCode, SiteID,Status FROM terminals 
              WHERE SiteID = '".$zsiteID."' ORDER BY TerminalID ASC LIMIT $zstart, $zlimit";
         }
         else
         {
          $stmt = "SELECT TerminalID, TerminalName, TerminalCode, SiteID,Status FROM terminals ORDER BY TerminalID ASC LIMIT $zstart, $zlimit";
         }
          $this->executeQuery($stmt);
          return $this->fetchAllData();
          //return $this->_row;
      }

      //count info on serviceterminals for pagination
      function countserviceterminals()
      {
          $stmt = "SELECT COUNT(*) as count FROM serviceterminals st
                   INNER JOIN serviceagents AS sa ON st.ServiceAgentID = sa.ServiceAgentID";
          $this->executeQuery($stmt);
          return $this->fetchData();
      }

      //count info on serviceagentsfor pagination
      function countterminalagents()
      {
          $stmt = "SELECT COUNT(*) AS count FROM serviceagents";
          $this->executeQuery($stmt);
          return $this->fetchData();
      }

      //view all service agents created --> serviceagentview.php
      function viewagentspage($zstart, $zlimit)
      {
          $stmt = "SELECT ServiceAgentID, Username FROM serviceagents ORDER BY Username LIMIT $zstart, $zlimit";
          $this->executeQuery($stmt);
          return $this->fetchAllData();
      }

      //view all service terminal created --> serviceterminalview.php
      function viewservicespage($zstart, $zlimit)
      {
          $stmt = "SELECT a.ServiceTerminalID, a.ServiceTerminalAccount, a.ServiceAgentID, a.Status,
                   b.Username FROM serviceterminals as a 
                   INNER JOIN serviceagents as b ON a.ServiceAgentID = b.ServiceAgentID 
                   ORDER BY a.ServiceTerminalAccount ASC LIMIT $zstart, $zlimit";
          $this->executeQuery($stmt);
          return $this->fetchAllData();
      }
      /**** End For Pagination **/
      
      //get passcode from sitedetails to pass as password on RTG Player API
      function getpasscode($zsiteID)
      {
          $stmt = "SELECT PassCode FROM sitedetails WHERE SiteID = ?";
          $this->prepare($stmt);
          $this->bindparameter(1, $zsiteID);
          $this->execute();
          return $this->fetchData();
      }
      
      //check if terminalcode exist --> terminalcreation.php
      function checkterminalexist($zterminalcode)
      {
          $stmt = "SELECT COUNT(*) AS count FROM terminalservices WHERE TerminalID = ? AND Status = 1";
          $this->prepare($stmt);
          $this->bindparameter(1, $zterminalcode);
          $this->execute();
          return $this->fetchData();
      }
      
      //displays agent per selection of site
      function viewagentbysite($zsiteID)
      {
          $stmt = "SELECT ServiceAgentID, Username FROM serviceagents WHERE SiteID = ?";
          $this->prepare($stmt);
          $this->bindparameter(1, $zsiteID);
          $this->execute();
          return $this->fetchAllData();
      }
      
      function getterminalname($zterminalID)
      {
          $stmt = "SELECT TerminalName FROM terminals WHERE TerminalID = ?";
          $this->prepare($stmt);
          $this->bindparameter(1, $zterminalID);
          $this->execute();
          return $this->fetchData();
      }
      
      //get the last row of terminal table
      function getlastID($zsiteID,$zsitecode)
      {

        $zsitelength = strlen($zsitecode['SiteCode']) + 1;
        $stmt = "SELECT MAX(CAST(SUBSTR(TerminalCode,?) AS UNSIGNED )) AS tc  FROM terminals WHERE SiteID = ? AND isVIP = 0";
        $this->prepare($stmt);
        $this->bindparameter(1, $zsitelength);
        $this->bindparameter(2, $zsiteID);
        $this->execute();
        return $this->fetchData();
      }
      
      //show only OC Terminal that are not assigned (terminal mapping)
      function octerminalassigned()
      {
          $stmt = "SELECT a.ServiceTerminalID,a.ServiceTerminalAccount,b.TerminalID FROM serviceterminals a 
              LEFT JOIN terminalmapping b ON a.ServiceTerminalID = b.ServiceTerminalID 
              WHERE a.ServiceTerminalAccount <> '' ORDER BY a.ServiceTerminalAccount ASC";
          $this->prepare($stmt);
          $this->execute();
          return $this->fetchAllData();
      }
      
       //get terminalID of regular and vip by terminalcode
       function getterminalID($zterminalcode, $zsiteID)
       {
          //$stmt = "SELECT TerminalID FROM terminals WHERE TerminalCode LIKE '".$zterminalcode."%' AND SiteID = ?";
          $zvipterminal = $zterminalcode."VIP";
          $stmt = "SELECT TerminalID FROM terminals WHERE TerminalCode IN('$zterminalcode','$zvipterminal') AND SiteID = ?";
          $this->prepare($stmt);
          $this->bindparameter(1, $zsiteID);
          $this->execute();
          return $this->fetchAllData();
       }
       
       //get terminals that has an MG but not yet mapped(terminalmapping.php)
       function getmappedterminals($zsiteID)
       {
          $stmt = "SELECT ts.TerminalID, ts.ServiceID, ts.Status, t.TerminalCode FROM terminalservices ts
                   INNER JOIN terminals t ON t.TerminalID = ts.TerminalID 
                   WHERE t.SiteID = ? AND ts.Status = 1 AND ts.isCreated <> 1"; 
          $this->prepare($stmt);
          $this->bindparameter(1, $zsiteID);
          $this->execute();
          return $this->fetchAllData();
       }
       
       //check from providers with a provider name of MG
       function checkprovidername($zprovidername)
       {
           $stmt = "SELECT ServiceID FROM ref_services WHERE ServiceName LIKE '".$zprovidername."%' AND Status = 1";
           $this->prepare($stmt);
           $this->execute();
           return $this->fetchData();
       }
       
       //check if Terminal is already assigned to MG
       function checkproviderassigned($zterminalID, $zserviceID)
       {
           $stmt = "SELECT COUNT(*) AS ctrmg FROM terminalservices WHERE TerminalID = ? AND ServiceID = ?";
           $this->prepare($stmt);
           $this->bindparameter(1, $zterminalID);
           $this->bindparameter(2, $zserviceID);
           $this->execute();
           return $this->fetchData();
       }
       
       //check if agent name exist
       function checkagentexist($zagentname)
       {
           $stmt = "SELECT COUNT(*) AS ctragent FROM serviceagents WHERE Username = ?";
           $this->prepare($stmt);
           $this->bindparameter(1, $zagentname);
           $this->execute();
           return $this->fetchData();
       }
       
       //check if service terminal account name exists
       function checkocifexist($zocusername)
       {
           $stmt = "SELECT COUNT(*) AS ctroc FROM serviceterminals WHERE ServiceTerminalAccount = ?";
           $this->prepare($stmt);
           $this->bindparameter(1, $zocusername);
           $this->execute();
           return $this->fetchData();
       }
       
       //check if terminalcode exist --> re-assigning of casino server
        function checkterminalifexist($zterminalid,$zserviceid)
        {
            $stmt = "SELECT ServicePassword, HashedServicePassword,Status,isCreated FROM terminalservices WHERE TerminalID = ? AND ServiceID = ? ORDER BY TerminalID ";
            $this->prepare($stmt);
            $this->bindparameter(1, $zterminalid);
            $this->bindparameter(2, $zserviceid);
            $this->execute();
            return $this->fetchData();
        }
        
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
        
        
    /**
    * @author Gerardo V. Jagolino Jr.
    * @param $terminalID
    * @return int
    * check the number of cashier sessions enable in a certain site
    */ 
     function checkTerminalSessions($terminalID)
     {
           $stmt = "SELECT COUNT(TerminalID) count FROM terminalsessions 
                WHERE TerminalID = ?";
           $this->prepare($stmt);
           $this->bindparameter(1, $terminalID);
           $this->execute($stmt);
           $count =  $this->fetchData();
           return $count['count'];
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
     
     
     function getTerminalServicePassword($terminalid, $serviceID)
     {
           $stmt = "SELECT ServicePassword FROM terminalservices 
                WHERE TerminalID = ? AND ServiceID = ?";
           $this->prepare($stmt);
           $this->bindparameter(1, $terminalid);
           $this->bindparameter(2, $serviceID);
           $this->execute($stmt);
           $result =  $this->fetchData();
           return $result['ServicePassword'];
     }
}
?>
