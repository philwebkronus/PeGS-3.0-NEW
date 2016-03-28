<?php
/*
 * Created by : Lea Tuazon
 * Date Created : may 30, 2011
 *
 * Modified by: Edson Perez
 * Date Modified: May 31 - June 2, 2011
 *
 * Description: Function is to call PDO sql statements,
 *
 */
class DBHandler
{
    /**
     *
     * @var PDO 
     */
    public $_PDO;
    private $_connectionString;
    
    /**
     *
     * @var PDOStatement 
     */
    private $_stmt;

    function __construct( $connectionString )
    {
        $this->_connectionString = explode( ",", $connectionString );        
    }

    function __destruct()
    {
        gc_enable();
        gc_collect_cycles();
        $this->close();
    }

    public function open()
    {
        $bool = false;
        try
        {
            $oconnectionstring1 = $this->_connectionString[0];
            $oconnectionstring2 = $this->_connectionString[1];
            $oconnectionstring3 = $this->_connectionString[2];
            $this->_PDO = new PDO( $oconnectionstring1, $oconnectionstring2, $oconnectionstring3);
            $bool = true;
        }
        catch ( PDOException $e )
        {
            echo $e->getMessage();
        }

        return $bool;
    }

    public function close()
    {
        if ($this->_PDO)
            $this->_PDO = NULL;
    }

   public function executewithparams($sparams)
    {
        return $this->_stmt->execute($sparams);
    }

    public function execute()
    {
        return $this->_stmt->execute();
    }
    
    public function executeQuery( $stmt )
    {
        if ($this->_PDO)
        {
            $this->_stmt = $this->_PDO->query( $stmt );
        }
       else
           throw new Exception("Database connection not yet opened.");
    }
   
    public function deleterec($stmt)
    {
         $this->_stmt = $this->_PDO->exec($stmt);
    }
    
    public function prepare( $stmt )
    {
         $this->_stmt = $this->_PDO->prepare($stmt);  
    }

    public function rowCount()
    {
        return $this->_stmt->rowCount();
    }
    
    public function bindparameter($zid,$zfield)
    {
        $this->_stmt->bindParam($zid,$zfield);        
    }
    
   public function insertedid()
   {
       return $this->_PDO->lastInsertId();  
   }

   //insert messages
    public function insertmessage($zReturnID,$zReturnMessage,$zisSuccess,$zModule)
    {
       $this->_stmt = $this->_PDO->prepare("INSERT INTO ref_returnmessages(ReturnID,ReturnMessage,isSuccess,Module)
          VALUES(:returnid, :returnmessage, :issuccess, :module)");
       $xparams = array(':returnid'=>$zReturnID,':returnmessage'=>$zReturnMessage,':issuccess'=>$zisSuccess,':module'=>$zModule);
       $this->_stmt->execute($xparams);
    }

    public function begintrans()
    {
        $this->_PDO->beginTransaction();
    }   
   
   public function committrans()
   {
       $this->_PDO->commit();
   }
   
   public function rollbacktrans()
   {
       $this->_PDO->rollback();   
   }
   
   //validation: check if with existing session
   public function checksession($zuserid)
   {
        $this->prepare("Select COUNT(*)  from accountsessions  WHERE AID =  :userid ");
        $checksession= array(':userid'=>$zuserid);
        $this->executewithparams($checksession );
        return $this->hasRows();
   }
   
   //validation: check if with existing session is still valid
   public function checkifsessionexist($zuserid,$zsession)
   {
        $this->prepare("Select COUNT(*)  from accountsessions  WHERE AID =  :userid  AND SessionID =:sessionid ");
        $checksessionifexist= array(':userid'=>$zuserid,':sessionid'=>$zsession);
        $this->executewithparams($checksessionifexist );
        return $this->hasRows();
   }

   //generating date with microseconds
   public function getDate()
   {
        $time =microtime(true);
        $micro_time=sprintf("%06d",($time - floor($time)) * 1000000);
        $rawdate = new DateTime( date('Y-m-d H:i:s.'.$micro_time, $time) );
        $date = $rawdate->format("Y-m-d H:i:s.u");
        return $date;
   }

   public function fetchData()
   {
        return $this->_stmt->fetch(PDO::FETCH_ASSOC);
   }

   public function fetchAllData()
   {
        return $this->_stmt->fetchAll(PDO::FETCH_ASSOC);
   }

   public function hasRows()
   {
      return $this->_stmt->fetchColumn();
   }

   //log to audit trail
   public function logtoaudit($zsessionID, $zaid, $ztransdetails, $zdate, $zipaddress, $zauditfunctionID)
   {
       $this->_stmt = $this->_PDO->prepare("Insert into audittrail(SessionID, AID, TransDetails, TransDateTime, RemoteIP, AuditTrailFunctionID) values(:sessionid, :aid, :transdetails, :date, :ipaddress, :auditfunctionid)");
       $xparams = array(':aid' => $zaid, ':sessionid' => $zsessionID, ':transdetails' => $ztransdetails, ':date' => $zdate, ':ipaddress' => $zipaddress, ':auditfunctionid' => $zauditfunctionID);
       $this->_stmt->execute($xparams);
   } 
   
   //get all sites
  function getallsites()
  {
      $stmt = "SELECT SiteID,SiteName,SiteCode, if(isnull(POSAccountNo), '0000000000', POSAccountNo) as POS from sites WHERE Status = 1 ORDER BY SiteCode ASC";
      $this->executeQuery($stmt);
      return $this->fetchAllData();         
  }
  
   //SELECT SiteID FROM siteaccounts s where AID = 4
   public function getSiteID($zaid){
         $this->prepare("SELECT SiteID FROM siteaccounts s where AID = :aid");
         $xparams = array(':aid' => $zaid);
         $this->executewithparams($xparams);
         return $this->fetchData();
   }
   
  //added 07-05-2011 by mtcc for cashier transaction history
  //for fetch per type
    public function fetchPerType( $type )
    {
        return $this->_stmt->fetch($type);
    }
    //for binding parameters
    public function bindParam($parameter,$variable)
    {
        $this->_stmt->bindParam($parameter,$variable);
    }
    
    //get site name and pos account number to post it as a label
    function getsitename($zsiteID)
    {
        $stmt = "SELECT SiteName, SiteCode, if(isnull(POSAccountNo), '0000000000', POSAccountNo) as POS FROM sites WHERE SiteID = ?";
        $this->prepare($stmt);
        $this->bindparameter(1, $zsiteID);
        $this->execute();
        return $this->fetchAllData();
    }
        function getsiteamt($zsiteID)
    {
        $stmt = "SELECT LoadAmountDivisible FROM siteamountinfo WHERE SiteID=? AND Status=1";
        $this->prepare($stmt);
        $this->bindparameter(1, $zsiteID);
        $this->execute();
        return $this->fetchAllData();
    }
    
    function getsitecode($zsiteID)
    {
        $stmt = "SELECT SiteCode from sites WHERE SiteID = ?";
        $this->prepare($stmt);
        $this->bindparameter(1, $zsiteID);
        $this->execute();
        return $this->fetchData();
    }
    
     //for sorting of arrays
      function arraySortTwoDimByValueKey($array,$by,$type='asc') 
      {
                $sortField=&$by;
                $multArray=&$array;

                $tmpKey='';
                $ResArray=array();
                $maIndex=array_keys($multArray);
                $maSize=count($multArray)-1;
                for($i=0; $i < $maSize ; $i++) {
                        $minElement=$i;
                        $tempMin=$multArray[$maIndex[$i]][$sortField];
                        $tmpKey=$maIndex[$i];
                        for ($j=$i+1; $j <= $maSize; $j++) {
                                if ($multArray[$maIndex[$j]][$sortField] < $tempMin ) {
                                        $minElement=$j;
                                        $tmpKey=$maIndex[$j];
                                        $tempMin=$multArray[$maIndex[$j]][$sortField];
                                }
                        }
                        $maIndex[$minElement]=$maIndex[$i];
                        $maIndex[$i]=$tmpKey;
                }
                if ($type=='asc') {
                        for ($j=0;$j<=$maSize;$j++) {
                                $ResArray[$maIndex[$j]]=$multArray[$maIndex[$j]];
                        }
                } else {
                        for ($j=$maSize;$j>=0;$j--) {
                                $ResArray[$maIndex[$j]]=$multArray[$maIndex[$j]];
                        }
                }

                return $ResArray;
        }
        
        //ajax requests: check if session was expired
        public function isAjaxRequest()
        {
            if(isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH']==='XMLHttpRequest')
                return true;
            return false;
        }
        
        /**
         * return array
         */
        public function debugPDO()
        {
            return $this->_PDO->errorInfo();
        }
        
        /**
         *
         * @return array 
         */
        public function debugStatement()
        {
            return $this->_stmt->errorInfo();
        }
        
        /**
         * Email alert if changes made on ff: (Site Profile, Status, Account Profile, Status, Added Terminals)
         */
        public function emailalerts($ztitle, $zarremail, $zmessage)
        {
            $zcount = 0;
            while($zcount < count($zarremail))
            {
                $to = $zarremail[$zcount];               
                $subject = $ztitle;
                $disclaimer = "<br />
                                    This email and any attachments are confidential and may also be
                                    privileged.  If you are not the addressee, do not disclose, copy,
                                    circulate or in any other way use or rely on the information contained
                                    in this email or any attachments.  If received in error, notify the
                                    sender immediately and delete this email and any attachments from your
                                    system.  Any opinions expressed in this message do not necessarily
                                    represent the official positions of PhilWeb Corporation. Emails cannot
                                    be guaranteed to be secure or error free as the message and any
                                    attachments could be intercepted, corrupted, lost, delayed, incomplete
                                    or amended.  PhilWeb Corporation and its subsidiaries do not accept
                                    liability for damage caused by this email or any attachments and may
                                    monitor email traffic.
                               ";
                $message = $zmessage.$disclaimer;
                $headers="From: poskronusadmin@philweb.com.ph\r\nContent-type:text/html";
                $sentEmail = mail($to, $subject, $message, $headers);
                $zcount++;
            }
        }
        
        /**
         * Get Fullname of a particular AID
         * @param array array of AID's
         */
        public function getfullname($zarraid)
        {
            $zAID = implode(",", $zarraid);
            $stmt = "SELECT Name FROM accountdetails WHERE AID IN (".$zAID.") 
                     ORDER BY field(AID, ".$zAID.")";
            $this->prepare($stmt);
            $this->execute();
            return $this->fetchAllData();
        }
        
        /**
          *for displaying of account status name
          *@param int Status ID
          *@return string Status Name
        */
        function showstatusname($zstatus)
        {
             switch($zstatus)
              {
                  case 0:
                      $zstatname = "Inactive";
                  break;
                  case 1:
                      $zstatname = "Active";
                  break;
                  case 2:
                      $zstatname = "Suspended";
                  break;
                  case 3:
                      $zstatname = "Locked(Attempts)";
                  break;
                  case 4:
                      $zstatname = "Locked(Admin)";
                  break;
                  case 5:
                      $zstatname = "Terminated";
                  break;
                  default:
                      $zstatname = "Invalid Status";
                  break;
              }

             return $zstatname;
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
         * for displaying of membershipcard status name
         * @param int Status ID
         * @return string Status Name
       */
       function membershipcardStatus($zstatus)
       {
            switch ($zstatus)
            {
                case 0:
                    $zstatusname = "Inactive Membership Card";
                break;
                case 1:
                    $zstatusname = "Active Membership Card";
                break;
                case 2: 
                    $zstatusname = "Deactivated Membership Card";
                break;
                case 3:
                    $zstatusname = "Old VIP Reward Card";
                break;
                case 4:
                    $zstatusname = "Migrated VIP Reward Card";
                break;
                case 5:
                    $zstatusname = "Active Temporary Card";
                break;
                case 6:
                    $zstatusname = "InActive - Temporary Card";
                break;
                case 7:
                    $zstatusname = "New Migrated Membership Card";
                break;
                case 8:
                    $zstatusname = "Migrated Temporary Card. Please supply the membership card";
                break;
                case 9:
                    $zstatusname = "Card Is Banned";
                break;
                default:
                    $zstatusname = "Card Not Found";
                break;
            }
            return $zstatusname;
       }
       
       //get terminal name
        function getterminalname($zterminalID)
        {
            $stmt = "SELECT TerminalName FROM terminals WHERE TerminalID = ?";
            $this->prepare($stmt);
            $this->bindparameter(1, $zterminalID);
            $this->execute();
            return $this->fetchData();
        }
        
        /**
         * Get generated password and EncryptedPassword to be pass as casino passwords
         * @param int $zserviceid
         * @param int $zsiteid
         * @param string $zstatus
         * @return object
         */
        function getgeneratedpassword($zgenpwdid, $zservicegrpid){
            $stmt = "SELECT PlainPassword, EncryptedPassword FROM generatedpasswordpool 
                     WHERE GeneratedPasswordBatchID = ? AND ServiceGroupID = ?";
            $this->prepare($stmt);
            $this->bindparameter(1, $zgenpwdid);
            $this->bindparameter(2, $zservicegrpid);
            $this->execute();
            return $this->fetchData();
        }
        
        /**
         * Gets GeneratedPasswordBatchID for new sites 
         * @return type 
         */
        function chkpwdbatch(){
            $stmt = "SELECT GeneratedPasswordBatchID FROM generatedpasswordbatch 
                     WHERE SiteID IS NULL AND DateUsed IS NULL AND Status = 0 
                     LIMIT 1";
            $this->prepare($stmt);
            $this->execute();
            return $this->fetchData();
        }

        /**
         * Return the groupID of casino (RTG, MG, PT)
         * @return object
         */
        function getservicegrp(){
            $stmt = "SELECT ServiceGroupID, ServiceGroupName FROM ref_servicegroups WHERE Status = 1";
            $this->prepare($stmt);
            $this->execute();
            return $this->fetchAllData();
        }
        
        /**
         * Checks if site is existing or prior for deployment
         * @param int $zsiteID
         * @return object
         */
        function chkoldsite($zsiteID){
            $stmt = "SELECT GeneratedPasswordBatchID FROM generatedpasswordbatch 
                     WHERE SiteID = ? AND Status = 1 AND DateUsed IS NOT NULL LIMIT 1";
            $this->prepare($stmt);
            $this->bindparameter(1, $zsiteID);
            $this->execute();
            return $this->fetchData();
        }
        
        /**
         * Checks if account's Login attempts 
         * @param int $zaid
         * @return object 
         */
        function chkLoginAttempts($zaid){
            $stmt = 'SELECT LoginAttempts FROM accounts WHERE AID = ?';
            $this->prepare($stmt);
            $this->bindparameter(1, $zaid);
            $this->execute();
            return $this->fetchData();
        }
        
        //validation: delete sesion if exist when deactivating an account
       function deletesession($aid)
       {
           $stmt = "DELETE FROM accountsessions WHERE AID = ?";
           $this->prepare($stmt);
           $this->bindparameter(1, $aid);
           $this->execute();
       }
}

?>