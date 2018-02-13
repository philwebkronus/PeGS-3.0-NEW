<?php

/**
 * Created By: Gerardo V. Jagolino Jr.
 * Purpose: CLASS For PegsOps Casino Service Profile Management
 * Created On: February 27, 2013
 */


include "DbHandler.class.php";
ini_set('display_errors',true);
ini_set('log_errors',true);

class CasinoManagement extends DBHandler
{ 
    public $page;
    public $total;
    public $records;
    public $rows = array();
   
    public function __construct($connectionString) 
    {
        parent::__construct($connectionString);
    }
    
    /**
      * @author Gerardo V. Jagolino Jr.
      * @param $serviceid
      * @return array
      * for counting the number of services by a specific serviceid in terminalsessions 
      */
    function checkterminalsession($serviceid){
        $sql = ("SELECT COUNT(TerminalID) as count FROM terminalsessions WHERE ServiceID = $serviceid");
        $this->executeQuery($sql);
        $this->_row = $this->fetchData();
        return $this->_row;
    }
    
    /**
      * @author Gerardo V. Jagolino Jr.
      * @param $id
      * @return array
      * for counting the number of services per selected service group
      */
      function countservicedetails($id)
      {
          if($id > 0){
                $stmt = "SELECT COUNT(a.ServiceGroupID) as count FROM ref_servicegroups a 
                        INNER JOIN ref_services b ON a.ServiceGroupID = b.ServiceGroupID
                        WHERE b.ServiceGroupID = $id";
                $this->executeQuery($stmt);
                $this->_row = $this->fetchData();
                return $this->_row;
          }
          //count all services per groups
          else{
                $stmt = "SELECT COUNT(a.ServiceGroupID) as count FROM ref_servicegroups a
                        INNER JOIN ref_services b ON a.ServiceGroupID = b.ServiceGroupID";
                $this->executeQuery($stmt);
                $this->_row = $this->fetchData();
                return $this->_row;
          }
        
     }
     
     /**
      * @author Gerardo V. Jagolino Jr.
      * @param $zsiteID, $zstart, $zlimit, $zdirection, $zsort
      * @return array
      * view selected services per service group
      */
     function viewservicepage($zsiteID, $zstart, $zlimit, $zdirection, $zsort)
      {
        if($zsiteID > 0)
        {
            $stmt = "SELECT b.ServiceID, a.ServiceGroupName, b.ServiceName, b.Alias, b.Code, b.ServiceDescription, b.Status, b.UserMode 
                FROM ref_servicegroups a INNER JOIN ref_services b ON a.ServiceGroupID = b.ServiceGroupID 
                WHERE b.ServiceGroupID = $zsiteID ORDER BY ".$zsort." ".$zdirection." LIMIT ".$zstart.", ".$zlimit."";
        }
        //select all services
        else
        {
            $stmt = "SELECT b.ServiceID, a.ServiceGroupName, b.ServiceName, b.Alias, b.Code, b.ServiceDescription, b.Status, b.UserMode 
                FROM ref_servicegroups a INNER JOIN ref_services b ON a.ServiceGroupID = b.ServiceGroupID 
                ORDER BY ".$zsort." ".$zdirection." LIMIT ".$zstart.", ".$zlimit."";
        }
        $this->executeQuery($stmt);
        $this->_row = $this->fetchAllData();
        return $this->_row;
     }
     
     /**
      * @author Gerardo V. Jagolino Jr.
      * @param type $serviceid
      * @return array
      * getting service name via serviceid
      */
     function getServiceName($serviceid){
         $sql = ("SELECT ServiceName FROM ref_services WHERE ServiceID = $serviceid");
         $this->prepare($sql);
         $this->execute();
         return $this->fetchAllData();
     }


     /**
      * @author Gerardo V. Jagolino Jr. 
      * @param type $zstatus
      * @return $zstatusname Status
      */
     function refservicestatusname($zstatus)
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
      * @author Gerardo V. Jagolino Jr.
      * @param $zmode
      * @return string
      * for selecting service user mode
      */
    function serviceusermode($zmode)
    {
        switch ($zmode)
        {
            case 0:
                $zusermode = "Terminal based";
            break;
            case 1:
                $zusermode = "User Based";
            break;
            case 2:
                $zusermode = "Terminal based";
            break;
            // CCT ADDED 01/23/2018 BEGIN
            // e-Bingo
            case 4:
                $zusermode = "Terminal based";
            break;
            // CCT ADDED 01/23/2018 END
            default:
                $zusermode = "Invalid User Mode";
            break;
            
        }
        return $zusermode;
    }
    
    /**
      * @author Gerardo V. Jagolino Jr.
      * @param $zsiteID, $vID
      * @return array
      * for selecting casino service via service group id
      */
    function viewservicedetails($zsiteID, $vID)
      {
        $vID = $vID + 0;
        if($zsiteID > 0 && $vID == -1)
        {
              $stmt = "SELECT b.ServiceID, b.ServiceGroupID, a.ServiceGroupName, b.ServiceName, b.Alias, b.Code, b.ServiceDescription, b.Status, b.UserMode 
                    FROM ref_servicegroups a INNER JOIN ref_services b ON a.ServiceGroupID = b.ServiceGroupID ORDER BY b.ServiceID ASC";
              $this->executeQuery($stmt);
              $this->_row = $this->fetchAllData();
              if(count($this->_row == 0))
              {
                 $stmt = "SELECT b.ServiceID, b.ServiceGroupID, a.ServiceGroupName, b.ServiceName, b.Alias, b.Code, b.ServiceDescription, b.Status, b.UserMode 
                    FROM ref_servicegroups a INNER JOIN ref_services b ON a.ServiceGroupID = b.ServiceGroupID
                    WHERE a.ServiceGroupID =  $zsiteID ORDER BY b.ServiceID ASC";
                 $this->executeQuery($stmt);
                 $this->_row = $this->fetchAllData();
              }
              //--> must return array results for displaying data through json_encode
        }
        //if both dropdown fields are selected
        elseif ($zsiteID > 0 && $vID > 0) {
            if($vID == ""){
               $aw = " ";  
            }
            else if($vID != ""){
               $aw = " AND b.ServiceID = $vID ";
               
            }
              $stmt = "SELECT b.ServiceID, b.ServiceGroupID, a.ServiceGroupName, b.ServiceName, b.Alias, b.Code, b.ServiceDescription, b.Status, b.UserMode 
                    FROM ref_servicegroups a INNER JOIN ref_services b ON a.ServiceGroupID = b.ServiceGroupID ORDER BY b.ServiceID ASC";
              $this->executeQuery($stmt);
              $this->_row = $this->fetchAllData();
              if(count($this->_row == 0))
              {
                 $stmt = "SELECT b.ServiceID, b.ServiceGroupID, a.ServiceGroupName, b.ServiceName, b.Alias, b.Code, b.ServiceDescription, b.Status, b.UserMode 
                    FROM ref_servicegroups a INNER JOIN ref_services b ON a.ServiceGroupID = b.ServiceGroupID
                    WHERE a.ServiceGroupID =  $zsiteID"."$aw"."ORDER BY b.ServiceID ASC";
                 $this->executeQuery($stmt);
                 $this->_row = $this->fetchAllData();
              }
              //--> must return array results for displaying data through json_encode
        }
        //select all
        else
        {
            $stmt = "SELECT b.ServiceID, b.ServiceGroupID, a.ServiceGroupName, b.ServiceName, b.Alias, b.Code, b.ServiceDescription, b.Status, b.UserMode 
                    FROM ref_servicegroups a INNER JOIN ref_services b ON a.ServiceGroupID = b.ServiceGroupID ORDER BY b.ServiceID ASC" ;
            $this->executeQuery($stmt);
            $this->_row = $this->fetchAllData();  
        }                 
        return $this->_row;
        unset($zsiteID);
            unset($vID);
     }
     
     /**
      * @author Gerardo V. Jagolino Jr.
      * @param $zsiteID
      * @return array
      * for selecting casino service via service id
      */
     function viewservicedetails2($zsiteID)
      {
        if($zsiteID > 0)
        {
              $stmt =   "SELECT b.ServiceID, b.ServiceGroupID, a.ServiceGroupName, b.ServiceName, b.Alias, b.Code, b.ServiceDescription, b.Status, b.UserMode,
                        (CASE b.UserMode
                            WHEN '0' THEN 'Terminal Based'
                            WHEN '1' THEN 'User Based'
                            WHEN '2' THEN 'Terminal Based'
                            -- CCT ADDED 01/23/2018 BEGIN
                            -- e-Bingo
                            WHEN '4' THEN 'Terminal Based'
                            -- CCT ADDED 01/23/2018 END
                         END) as UserModeName
                        FROM ref_servicegroups a INNER JOIN ref_services b ON a.ServiceGroupID = b.ServiceGroupID 
                        WHERE b.ServiceID =  '".$zsiteID."' ORDER BY b.ServiceID ASC";
              $this->executeQuery($stmt);
              $this->_row = $this->fetchAllData();
              if(count($this->_row == 0))
              {
                 $stmt = "SELECT b.ServiceID, b.ServiceGroupID, a.ServiceGroupName, b.ServiceName, b.Alias, b.Code, b.ServiceDescription, b.Status, b.UserMode, 
                    (CASE b.UserMode
                            WHEN '0' THEN 'Terminal Based'
                            WHEN '1' THEN 'User Based'
                            WHEN '2' THEN 'Terminal Based'
                            -- CCT ADDED 01/23/2018 BEGIN
                            -- e-Bingo
                            WHEN '4' THEN 'Terminal Based'
                            -- CCT ADDED 01/23/2018 END                            
                         END) as UserModeName
                    FROM ref_servicegroups a INNER JOIN ref_services b ON a.ServiceGroupID = b.ServiceGroupID
                    WHERE b.ServiceID =  '".$zsiteID."' ORDER BY b.ServiceID ASC";
                 $this->executeQuery($stmt);
                 $this->_row = $this->fetchAllData();
              }
              //--> must return array results for displaying data through json_encode
        }
        //select all details of service
        else
        {
            $stmt = "SELECT b.ServiceID, b.ServiceGroupID, a.ServiceGroupName, b.ServiceName, b.Alias, b.Code, b.ServiceDescription, b.Status, b.UserMode 
                    FROM ref_servicegroups a INNER JOIN ref_services b ON a.ServiceGroupID = b.ServiceGroupID ORDER BY b.ServiceID ASC" ;
            $this->executeQuery($stmt);
            $this->_row = $this->fetchAllData();  
        }                 
        return $this->_row;
     }
    
     /**
      * @author Gerardo V. Jagolino Jr.
      * @param $zServiceName, $zServiceAlias, $zServiceCode, $zServiceDesc, $zServiceGrp, $zServiceMode, $zServiceID
      * @return integer
      * for updating casino service details
      */
     function updateService($zServiceName, $zServiceAlias, $zServiceCode, $zServiceDesc, $zServiceGrp, $zServiceMode, $zServiceID){
         $this->begintrans();
         $this->prepare("UPDATE ref_services SET ServiceName = ?, Alias = ?, Code = ?,ServiceDescription = ? ,ServiceGroupID = ?, UserMode = ? WHERE ServiceID = ?");
         $this->bindparameter(1, $zServiceName);
         $this->bindparameter(2, $zServiceAlias);
         $this->bindparameter(3, $zServiceCode);         
         $this->bindparameter(4, $zServiceDesc);     
         $this->bindparameter(5, $zServiceGrp);
         $this->bindparameter(6, $zServiceMode);
         $this->bindparameter(7, $zServiceID);
         $this->execute();
         
         $isexecute = $this->rowCount();
         
         if($isexecute > 0){
            $this->committrans();
            return 1;    
         }
         //rollback update transaction
         else {
            $this->rollbacktrans();
            return 0;    
         }
         
     }
     
     
     /**
      * @author Gerardo V. Jagolino Jr.
      * @param $zstatus, $zserviceID
      * @return integer
      * for updating casino service status
      */
     function changestatus($zstatus, $zserviceID)
     {
         $this->begintrans();
         $this->prepare("UPDATE ref_services SET Status = ? WHERE ServiceID = ?");
         $this->bindparameter(1, $zstatus);
         $this->bindparameter(2, $zserviceID);
         $this->execute();
         $this->committrans();
          
         return 1;
     }

     /**
      * @author Gerardo V. Jagolino Jr.
      * @param $zserviceID
      * @return array
      * for selecting casino service by service id
      */
      function selectservice($zserviceID)
      {
          $stmt = "SELECT ServiceID, ServiceName FROM ref_services 
                   WHERE ServiceGroupID = '".$zserviceID."' ORDER BY ServiceName ASC";
          $this->executeQuery($stmt);
          return $this->fetchAllData();
      }
//----------------------- FOR REMOVAL -----------------------//      
      /**
      * @author Gerardo V. Jagolino Jr.
      * @param $zsiteID
      * @return array
      * for selecting casino services for jqgrid
      */
//     function viewservicess($zsiteID)
//    {
//        if($zsiteID > 0)
//        {
//            $stmt = "SELECT b.ServiceID, a.ServiceGroupName, b.ServiceName, 
//                b.Alias, b.Code, b.ServiceDescription, b.UserMode, b.Status FROM ref_servicegroups a 
//                INNER JOIN ref_services b ON a.ServiceGroupID = b.ServiceGroupID 
//                WHERE a.ServiceGroupID = $zsiteID ORDER BY ServiceID ASC";
//        }
//        $this->executeQuery($stmt);
//        return $this->fetchAllData();
//    }
    
    /**
      * @author Gerardo V. Jagolino Jr.
      * @param $zsiteID
      * @return array
      * for selecting total count of casino services selected by service groupid, used for paging
      */
//    function countservices($zsiteID)
//      {
//          $stmt = "SELECT COUNT(*) AS count FROM ref_services WHERE ServiceGroupID = '".$zsiteID."'";
//          $this->executeQuery($stmt);
//          $this->_row = $this->fetchData();
//          return $this->_row;
//      }
      
      /**
      * @author Gerardo V. Jagolino Jr.
      * @param $zsiteID, $zstart, $zlimit
      * @return array
      * view terminal details (for pagination)
      */
     function viewterminalspage($zsiteID, $zstart, $zlimit)
      {
         if($zsiteID > 0)
         {
          $stmt = "SELECT b.ServiceID, a.ServiceGroupName, b.ServiceName, 
                b.Alias, b.Code, b.ServiceDescription, b.UserMode, b.Status FROM ref_servicegroups a 
                INNER JOIN ref_services b ON a.ServiceGroupID = b.ServiceGroupID 
              WHERE a.ServiceGroupID = $zsiteID ORDER BY b.ServiceID ASC LIMIT $zstart, $zlimit";
         }
         else
         {
          $stmt = "SELECT ServiceID, ServiceName, Alias, Code,ServiceDescription, Status, ServiceGroupID, UserMode FROM ref_services  ORDER BY ServiceID ASC LIMIT $zstart, $zlimit";
         }
          $this->executeQuery($stmt);
          return $this->fetchAllData();
          //return $this->_row;
      }
    
      /**
      * @author Gerardo V. Jagolino Jr.
      * @param 
      * @return array
      * get service group list 
      */
        function getrefservicegrpwithid()
      {
         $stmt = "SELECT ServiceGroupID, ServiceGroupName FROM ref_servicegroups ORDER BY ServiceGroupName ASC";
         $this->executeQuery($stmt);
         return $this->fetchAllData();
      }
      /**
      * @author Mark Nicolas C. Atangan
      * @param 
      * @return array
      * get service group list 
      */
        function getusermodewithid()
      {
         $stmt = "SELECT DISTINCT (UserMode),
                    (CASE UserMode
                        WHEN '0' THEN 'Terminal Based'
                        WHEN '1' THEN 'User Based'
                        WHEN '2' THEN 'Terminal Based'
                        -- CCT ADDED 01/23/2018 BEGIN
                        -- e-Bingo
                        WHEN '4' THEN 'Terminal Based'
                        -- CCT ADDED 01/23/2018 END
                    END) as UserModeName
                FROM ref_services GROUP BY UserModeName ASC";
         $this->executeQuery($stmt);
         return $this->fetchAllData();
      }
 

    
}
?>
