<?php

/* Created by : Gerardo V. Jagolino Jr.
 * Date Created : Apr 11, 2013
 */

include "DbHandler.class.php";

class ManualAPIFulfillment extends DBHandler
{
      public function __construct($sconectionstring)
      {
          parent::__construct($sconectionstring);
      }
      
     /**
     * @author Gerardo V. Jagolino Jr.
     * @param int $terminal
     * @return object count
     * Description: count number of pending user based transaction
     */
      function checkpendingusertransactions($terminal){
            $stmt = "SELECT COUNT(LoyaltyCardNumber) chckpndustrans from pendingusertransactions WHERE TerminalID = ?";
            $this->prepare($stmt);
            $this->bindparameter(1,$terminal);
            $this->execute();
            return $this->fetchData();      
      }
      
     /**
     * @author Gerardo V. Jagolino Jr.
     * @param int $terminal
     * @return object count
     * Description: count number of pending terminal based transaction
     */
      function checkpendingterminaltransactions($terminal){
            $stmt = "SELECT COUNT(TerminalID) chckpndtertrans from pendingterminaltransactions 
                WHERE TerminalID = ?";
            $this->prepare($stmt);
            $this->bindparameter(1,$terminal);
            $this->execute();
            return $this->fetchData();      
      }

     /**
     * @author Gerardo V. Jagolino Jr.
     * @param int $terminal
     * @return array
     * Description: get referenceid, loyaltycardnumber, RequestSource
     */
      function getTransactionReferenceIDterminal($terminal){
            $stmt = "SELECT TransactionReferenceID, LoyaltyCardNumber, RequestSource, ServiceID 
                FROM pendingterminaltransactions WHERE TerminalID = ?";
            $this->prepare($stmt);
            $this->bindparameter(1,$terminal);
            $this->execute();
            return $this->fetchAllData();      
      }
      
      /**
     * @author Gerardo V. Jagolino Jr.
     * @param int $terminal
     * @return array
     * Description: get referenceid, loyaltycardnumber, RequestSource
     */
      function getTransactionReferenceIDuser($terminal){
            $stmt = "SELECT TransactionReferenceID, LoyaltyCardNumber, RequestSource, ServiceID 
                FROM pendingusertransactions WHERE TerminalID = ?";
            $this->prepare($stmt);
            $this->bindparameter(1,$terminal);
            $this->execute();
            return $this->fetchAllData();      
      }
      
    /**
    * @author Gerardo V. Jagolino Jr.
    * @param int $terminal
    * @return object count
    * Description: count number of pending user based transaction
    */
      function gettransactionRequestlogs($trefid){
            $stmt = "SELECT Amount, TransactionReferenceID, TransactionType, ServiceID, UserMode 
                FROM transactionrequestlogs WHERE TransactionReferenceID = ?";
            $this->prepare($stmt);
            $this->bindparameter(1,$trefid);
            $this->execute();
            return $this->fetchAllData();      
      }
      
      /**
     * @author Gerardo V. Jagolino Jr.
     * @param int $trefid
     * @return array
     * Description: get the amount, referenceid, transatction type, serviceid usermode, and transactionsummaryid
     */
      function gettransactionRequestlogslp($trefid){
            $stmt = "SELECT Amount, TransactionReferenceID, TransactionType, ServiceID, 
                UserMode, TransactionSummaryID FROM transactionrequestlogslp WHERE TransactionReferenceID = ?";
            $this->prepare($stmt);
            $this->bindparameter(1,$trefid);
            $this->execute();
            return $this->fetchAllData();      
      }
      
      /**
     * @author Gerardo V. Jagolino Jr.
     * @param int $serviceid
     * @return array
     * Description: get the service name
     */
      function getServiceName($serviceid){
            $stmt = "SELECT ServiceName FROM ref_services WHERE ServiceID = ?";
            $this->prepare($stmt);
            $this->bindparameter(1,$serviceid);
            $this->execute();
            return $this->fetchAllData();      
      }
      

     /**
     * @author Gerardo V. Jagolino Jr.
     * @param int $zsiteID
     * @return array
     * Description: select all terminal based from sites to populate combo box
     */
      function viewterminals($zsiteID)
      {
          if($zsiteID > 0)
          {
              $stmt = "SELECT DISTINCT a.TerminalID, b.TerminalCode FROM transactiondetails
                  a INNER JOIN terminals b ON a.TerminalID = b.TerminalID
                  WHERE a.SiteID = '".$zsiteID."' ORDER BY TerminalID ASC";
          }
          else
          {
              $stmt = "SELECT DISTINCT a.TerminalID, b.TerminalCode FROM transactiondetails a 
                  INNER JOIN terminals b ON a.TerminalID = b.TerminalID ORDER BY TerminalID ASC";
          }
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
      
      
      /**
     * @author Gerardo V. Jagolino Jr.
     * @param int $status, int $transrefid, int $servicetransid, int $sevicestatus
     * @return array
     * Description: update transaction request logs status for cashier source
     */
      function uptransactionreqlogs($status, $transrefid, $servicetransid, $sevicestatus)
      {
        $stmt = "UPDATE transactionrequestlogs SET Status = ?, EndDate = now_usec(), ServiceTransactionID = ?, ServiceStatus = ? WHERE TransactionReferenceID = ?";
        $this->prepare($stmt);
        $this->bindparameter(1,$status);
        $this->bindparameter(2,$servicetransid);
        $this->bindparameter(3,$sevicestatus);
        $this->bindparameter(4,$transrefid);
        $this->execute();
        return $this->rowCount();
      }
      
      
      /**
     * @author Gerardo V. Jagolino Jr.
     * @param $terminalID
     * @return array
     * Description: count terminal sessions of a certain terminal
     */
      function countSessions($terminalID)
      {
        $stmt = "SELECT COUNT(TerminalID) termsess FROM terminalsessions WHERE TerminalID = ?";
        $this->prepare($stmt);
        $this->bindparameter(1,$terminalID);
        $this->execute();
        return $this->fetchData();      
      }
      
      
      /**
     * @author Gerardo V. Jagolino Jr.
     * @param $terminalid
     * @return array
     * Description: get terminal code for a specific terminal 
     */
      function getTerminalCode($terminalid)
      {
        $stmt = "SELECT TerminalCode FROM terminals WHERE TerminalID = ?";
        $this->prepare($stmt);
        $this->bindparameter(1,$terminalid);
        $this->execute();
        return $this->fetchData();
      }
      
      
     /**
     * @author Gerardo V. Jagolino Jr.
     * @param $serviceid, $term
     * @return array
     * Description: get ServiceTransactionID for transctionid in transSerachInfo
     */
      function getTransactionID($serviceid, $term)
      {
        $stmt = "SELECT MAX(TransactionRequestLogID) TransactionRequestLogID FROM transactionrequestlogs WHERE ServiceID = ? AND TerminalID = ?";
        $this->prepare($stmt);
        $this->bindparameter(1,$serviceid);
        $this->bindparameter(2,$term);
        $this->execute();
        return $this->fetchData();
      }
      
        /**
        * @author Gerardo V. Jagolino Jr.
        * @param $zterminalID
        * @return array
        * Description: get terminal name
        */
        function getterminalname($zterminalID)
        {
            $stmt = "SELECT TerminalName FROM terminals WHERE TerminalID = ?";
            $this->prepare($stmt);
            $this->bindparameter(1, $zterminalID);
            $this->execute();
            return $this->fetchData();
        }
      
     /**
     * @author Gerardo V. Jagolino Jr.
     * @param $serviceid, $term
     * @return array
     * Description: get ServiceTransactionID for transctionid in transSerachInfo
     */
      function getTransactionIDLP($serviceid, $term)
      {
        $stmt = "SELECT MAX(TransactionRequestLogLPID) TransactionRequestLogLPID FROM transactionrequestlogslp WHERE ServiceID = ? AND TerminalID = ?";
        $this->prepare($stmt);
        $this->bindparameter(1,$serviceid);
        $this->bindparameter(2,$term);
        $this->execute();
        return $this->fetchData();
      }
      
     /**
     * @author Gerardo V. Jagolino Jr.
     * @param $transrefid
     * @return array
     * Description: get ServiceTransactionID for transctionid in transSerachInfo in cashier
     */
      function getServiceTransactionID($transrefid)
      {
        $stmt = "SELECT ServiceTransactionID FROM transactionrequestlogs WHERE TransactionReferenceID = ?";
        $this->prepare($stmt);
        $this->bindparameter(1,$transrefid);
        $this->execute();
        return $this->fetchData();
      }
      
      
      /**
     * @author Gerardo V. Jagolino Jr.
     * @param $transrefid
     * @return array
     * Description: get ServiceTransactionID for transctionid in transSerachInfo in launchpad
     */
      function getServiceTransactionIDLP($transrefid)
      {
        $stmt = "SELECT ServiceTransactionID FROM transactionrequestlogslp WHERE TransactionReferenceID = ?";
        $this->prepare($stmt);
        $this->bindparameter(1,$transrefid);
        $this->execute();
        return $this->fetchData();
      }
      
      
      /**
     * @author Gerardo V. Jagolino Jr.
     * @param $status, $transrefid
     * @return array
     * Description: update transaction request logs lp status for launchpad source
     * Used in Launchpad Disapproved Withdraw and Deposit/Redeposit Approved 
     */
      function uptransactionreqlogslp($status, $servciestatus, $transrefid)
      {
        $stmt = "UPDATE transactionrequestlogslp SET Status = ?, ServiceStatus = ?, EndDate = now_usec() WHERE TransactionReferenceID = ?";
        $this->prepare($stmt);
        $this->bindparameter(1,$status);
        $this->bindparameter(2,$servciestatus);
        $this->bindparameter(3,$transrefid);
        $this->execute();
        return $this->rowCount();
      }
      
       /**
     * @author Gerardo V. Jagolino Jr.
     * @param $status, $transrefid
     * @return array
     * Description: update transaction request logs lp status for launchpad source
     * Used in Launchpad Approved Withdraw and Deposit/Redeposit Disaproved 
     */
      function uptransactionreqlogslp2($status, $servciestatus, $transrefid, $servicetransid)
      {
        $stmt = "UPDATE transactionrequestlogslp SET Status = ?, ServiceStatus = ?, 
            EndDate = now_usec(), ServiceTransactionID= ? WHERE TransactionReferenceID = ?";
        $this->prepare($stmt);
        $this->bindparameter(1,$status);
        $this->bindparameter(2,$servciestatus);
        $this->bindparameter(3,$servicetransid);
        $this->bindparameter(4,$transrefid);
        $this->execute();
        return $this->rowCount();
      }
      
      
        /**
     * @author Gerardo V. Jagolino Jr.
     * @param $transrefid, $amount, $transtype, $terminalid, $status, $site,
              $servicetransid, $servicestatus, $serivceid, $servicetransferhisid, $loyaltycard, $mid, $usermode, 
              $transsummaryid, $paymenttype
     * @return array
     * Description: insert in transactionrequestlogslp
     */
      function insertTransactionreqlogslp($transrefid, $amount, $transtype, $terminalid, $status, $site,
              $servicetransid, $servicestatus, $serivceid, $servicetransferhisid, $loyaltycard, $mid, $usermode, 
              $transsummaryid, $paymenttype){
          
          $this->begintrans();
        try{
            $stmt = "INSERT INTO transactionrequestlogslp(TransactionReferenceID,
                Amount,StartDate,EndDate,TransactionType,TerminalID,Status,SiteID, 
                ServiceTransactionID,ServiceStatus,ServiceID, ServiceTransferHistoryID, 
                LoyaltyCardNumber, MID,UserMode,TransactionSummaryID, PaymentType) 
                VALUES (?, ?, now_usec(), now_usec(), ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $this->prepare($stmt);
            $this->bindparameter(1,$transrefid);
            $this->bindparameter(2,$amount);
            $this->bindparameter(3,$transtype);
            $this->bindparameter(4,$terminalid);
            $this->bindparameter(5,$status);
            $this->bindparameter(6,$site);
            $this->bindparameter(7,$servicetransid);
            $this->bindparameter(8,$servicestatus);
            $this->bindparameter(9,$serivceid);
            $this->bindparameter(10,$servicetransferhisid);
            $this->bindparameter(11,$loyaltycard);
            $this->bindparameter(12,$mid);
            $this->bindparameter(13,$usermode);
            $this->bindparameter(14,$transsummaryid);
            $this->bindparameter(15,$paymenttype);

            if($this->execute()) {
                $trans_req_log_lpid = $this->insertedid();
                $statusz = 3;
                $stmt = "UPDATE transactionrequestlogslp SET Status = ? WHERE TransactionRequestLogLPID = ? AND TransactionType = ?";
                $this->prepare($stmt);
                $this->bindparameter(1,$statusz);
                $this->bindparameter(2,$trans_req_log_lpid);
                $this->bindparameter(3,$transtype);
            
                if($this->execute()) {
                    $this->committrans();
                    return true;
                } else {
                    $this->rollbacktrans();
                    return false;
                }
            } else {
                $this->rollbacktrans();
                return false;
            }

            } catch (Exception $e) {
                        $this->rollbacktrans();
                        return false;
                    }
          
          
          
      }
      
       /**
     * @author Gerardo V. Jagolino Jr.
     * @param $transtype, $terminal, $service, $transloglpid
     * @return array
     * Description: get transactionrequestlogslp ServiceHistory and TransactionSummary ID's
     */
      function gettranslogslpdetails($transtype, $terminal, $service, $transloglpid){
          $stmt = "SELECT ServiceTransferHistoryID, TransactionSummaryID 
              FROM transactionrequestlogslp WHERE TransactionType = ? AND TerminalID = ? 
              AND ServiceID = ? AND TransactionRequestLogLPID = ?";
            $this->prepare($stmt);
            $this->bindparameter(1,$transtype);
            $this->bindparameter(2,$terminal);
            $this->bindparameter(3,$service);
            $this->bindparameter(4,$transloglpid);
            $this->execute();
            return $this->fetchAllData();
      }
      
     /**
     * @author Gerardo V. Jagolino Jr.
     * @param $format, $utimestamp
     * @return array
     * Description: get date with a certain date time format
     */
        public static function udate($format, $utimestamp = null)
        {
            if (is_null($utimestamp))
                $utimestamp = microtime(true);

            $timestamp = floor($utimestamp);
            $milliseconds = round(($utimestamp - $timestamp) * 1000000);

            return date(preg_replace('`(?<!\\\\)u`', $milliseconds, $format), $timestamp);
        }
      
      
      /**
     * @author Gerardo V. Jagolino Jr.
     * @param $transtype, $terminal, $service
     * @return array
     * Description: get max transactionrequestlogsID
     */
      function getmaxtranslogs($transtype, $terminal, $service){
          $stmt = "SELECT MAX(TransactionRequestLogLPID) TransactionRequestLogLPID FROM transactionrequestlogslp 
              WHERE TransactionType = ? AND TerminalID = ? AND ServiceID = ?";
            $this->prepare($stmt);
            $this->bindparameter(1,$transtype);
            $this->bindparameter(2,$terminal);
            $this->bindparameter(3,$service);
            $this->execute();
            $data =  $this->fetchData();
            return $data['TransactionRequestLogLPID'];
      }
      
     
      
      /**
     * 
     * Description: This will insert records in transactionsummary, transactiondetails and terminalsessions
     * @version Kronus UB
     * @author Gerardo V. Jagolino Jr.
     * @param int $site_id
     * @param int $terminal_id
     * @param int $amount
     * @param int $acctid
     * @param int $trans_ref_id
     * @param string $trans_type
     * @param int $service_id
     * @param int $trans_status
     * @return int | boolean  $trans_summary_max_id
     */
    public function startTransaction($site_id,$terminal_id,$amount,$acctid, $trans_ref_id,
                                      $trans_type,$service_id,$trans_status, $loyalty_card, $mid)
    {
        
            $this->begintrans();
        
        try{
            
     
            $stmt = "INSERT INTO transactionsummary 
                                        (SiteID, TerminalID, Deposit, DateStarted, DateEnded, CreatedByAID, LoyaltyCardNumber, MID) 
                                        VALUES (?, ?, ?, now_usec(), 0, ?, ?, ?)";
            $this->prepare($stmt);
            $this->bindparameter(1, $site_id);
            $this->bindparameter(2, $terminal_id);
            $this->bindparameter(3, $amount);
            $this->bindparameter(4, $acctid);
            $this->bindparameter(5, $loyalty_card);
            $this->bindparameter(6, $mid);
            
            
            if($this->execute()) {
                $trans_summary_max_id = $this->insertedid();
                
                
                $stmt2 = "INSERT INTO transactiondetails (TransactionReferenceID, 
                        TransactionSummaryID, SiteID, TerminalID, TransactionType, Amount, DateCreated, 
                        ServiceID, CreatedByAID, Status, LoyaltyCardNumber, MID, PaymentType) VALUES 
                        (?, ?, ?, ?, ?, ?, now_usec(), ?, ?, ?, ?, ?, 1)";
                $this->prepare($stmt2);
                
                $this->bindparameter(1, $trans_ref_id);
                $this->bindparameter(2, $trans_summary_max_id);
                $this->bindparameter(3, $site_id);
                $this->bindparameter(4, $terminal_id);
                $this->bindparameter(5, $trans_type);
                $this->bindparameter(6, $amount);
                $this->bindparameter(7, $service_id);
                $this->bindparameter(8, $acctid);
                $this->bindparameter(9, $trans_status);
                $this->bindparameter(10, $loyalty_card);
                $this->bindparameter(11, $mid);
                
                if($this->execute()) {
                    
                    $trans_details_id = $this->insertedid();
                    
                    $stmt3 = "UPDATE terminalsessions SET TransactionSummaryID = ? 
                                                 WHERE TerminalID = ?";
                    $this->prepare($stmt3);
                    $this->bindparameter(1, $trans_summary_max_id);
                    $this->bindparameter(2, $terminal_id);
                    
                    
                    if($this->execute()) {
                        $this->committrans();
                        return $trans_summary_max_id;
                    } else {
                        $this->rollbacktrans();
                        return false;
                    }
                } else {
                    $this->rollbacktrans();
                    return false;
                }
            } else {
                $this->rollbacktrans();
                return false;
            }
        } catch (Exception $e) {
            $this->rollbacktrans();
            return false;
        }
    }
    
    /**
     * 
     * Description: This will update records in transactionsummary, terminalsessions and insert in transactiondetails
     * @version Kronus UB
     * @author Gerardo V. Jagolino Jr.
     * @param int $amount
     * @param int $trans_summary_id
     * @param int $trans_ref_id
     * @param int $site_id
     * @param int $terminal_id
     * @param string $trans_type
     * @param int $service_id
     * @param int $acctid
     * @param int $trans_status
     * @param int $terminal_balance
     * @return boolean 
     */
    public function reloadTransaction($amount, $trans_summary_id, $trans_ref_id, $site_id,
                                      $terminal_id, $trans_type, $service_id, $acctid, 
                                      $trans_status, $terminal_balance, $total_terminal_balance, 
                                      $loyalty_card, $mid)    
            
    {
        $this->begintrans();
        try{
            
            $stmt1 = "UPDATE transactionsummary SET Reload = ? WHERE TransactionsSummaryID = ?";
            $this->prepare($stmt1);
            $this->bindparameter(1, $terminal_balance);
            $this->bindparameter(2, $trans_summary_id);
            
            if($this->execute()) {
                $stmt2 = "INSERT INTO transactiondetails (TransactionReferenceID, 
                                             TransactionSummaryID, SiteID, TerminalID, TransactionType, 
                                             Amount, DateCreated, ServiceID, CreatedByAID, Status, LoyaltyCardNumber, MID, PaymentType) 
                                             VALUES (?, ?, ?, ?, ?, 
                                             ?, now_usec(), ?, ?, ?, ?, ?, 1)";
                $this->prepare($stmt2);
                
                $this->bindparameter(1,$trans_ref_id);
                $this->bindparameter(2, $trans_summary_id);
                $this->bindparameter(3, $site_id);
                $this->bindparameter(4, $terminal_id);
                $this->bindparameter(5, $trans_type);
                $this->bindparameter(6, $amount);
                $this->bindparameter(7, $service_id);
                $this->bindparameter(8, $acctid);
                $this->bindparameter(9, $trans_status);
                $this->bindparameter(10, $loyalty_card);
                $this->bindparameter(11, $mid);
                
                
                if($this->execute()) {
                    
                    $trans_details_id = $this->insertedid();
                    $stmt = "UPDATE terminalsessions SET ServiceID = ?, 
                                                 LastBalance = ?, LastTransactionDate = now_usec() 
                                                 WHERE TerminalID = ?";
                    $this->prepare($stmt);
                    $this->bindparameter(1, $service_id);
                    $this->bindparameter(2, $total_terminal_balance);
                    $this->bindparameter(3, $terminal_id);
                   
                    
                    $isupdated = $this->rowCount();
                    
                    if($this->execute()) {
                       $this->committrans();
                        return $trans_details_id;
                    } else {
                        $this->rollbacktrans();
                        return false;
                    }
                    
                } else {
                    $this->rollbacktrans();
                    return false;
                }
            } else {
                $this->rollbacktrans();
                return false;
            }
        } catch (Exception $e) {
            $this->rollbacktrans();
            return false;
        }
    }
    
    
    /**
     * 
     * Description: This will update records in transactionsummary, terminalsessions and insert in transactiondetails
     * @version Kronus UB
     * @author Gerardo V. Jagolino Jr.
     * @param int $amount
     * @param int $trans_summary_id
     * @param int $trans_ref_id
     * @param int $site_id
     * @param int $terminal_id
     * @param string $trans_type
     * @param int $service_id
     * @param int $acctid
     * @param int $trans_status
     * @param int $terminal_balance
     * @param $loyalty_card
     * @param $mid
     * @return boolean 
     */
    public function redeemTransaction($amount, $trans_summary_id, $trans_ref_id, $site_id,
                                      $terminal_id, $trans_type, $service_id, $acctid,
                                      $trans_status, $loyalty_card, $mid)
    {
        $this->begintrans();
        try {
            $stmt1 = "UPDATE transactionsummary SET Withdrawal = ?, DateEnded = now_usec()  
                                         WHERE TransactionsSummaryID = ?";
            $this->prepare($stmt1);
            $this->bindparameter(1, $amount);
            $this->bindparameter(2, $trans_summary_id);
            
            
            if($this->execute())
            {
                $stmt2 = "INSERT INTO transactiondetails (TransactionReferenceID, TransactionSummaryID, SiteID, 
                                             TerminalID, TransactionType, Amount, DateCreated, ServiceID, CreatedByAID, Status, 
                                             LoyaltyCardNumber, MID, PaymentType) VALUES (?, ?, ?, ?, 
                                             ?, ?, now_usec(), ?, ?, ?, ?, ?, 1)";
                
                $this->prepare($stmt2);
                $this->bindparameter(1,$trans_ref_id);
                $this->bindparameter(2, $trans_summary_id);
                $this->bindparameter(3, $site_id);
                $this->bindparameter(4, $terminal_id);
                $this->bindparameter(5, $trans_type);
                $this->bindparameter(6, $amount);
                $this->bindparameter(7, $service_id);
                $this->bindparameter(8, $acctid);
                $this->bindparameter(9, $trans_status);
                $this->bindparameter(10, $loyalty_card);
                $this->bindparameter(11, $mid);
                
                
                if($this->execute()) {
                    $trans_details_id = $this->insertedid();
                    
                    $stmt = "DELETE FROM terminalsessions WHERE TerminalID = ?";
                    $this->prepare($stmt);
                    $this->bindparameter(1, $terminal_id);
                    
                    if($this->execute()) {
                        $this->committrans();
                        return $trans_details_id;
                    } else {
                        $this->rollbacktrans();
                        return false;
                    }
                } else {
                    $this->rollbacktrans();
                    return false;
                }
            } else {
                $this->rollbacktrans();
                return false;
            }
        } catch(Exception $e) {
            $this->rollbacktrans();
            return false;
        }   
    }
    
    /**
     * Description: Get Last Transaction Summary ID in terminal sessions table
     * instead of getting the max summary ID in transaction summary table
     * @author Gerardo V. Jagolino Jr.
     * @param type $site_id,$terminal_id
     * @return type array
     */
    public function getLastSessSummaryID($terminalID){
        $sql = 'SELECT TransactionSummaryID FROM terminalsessions WHERE TerminalID = ?';
        $this->prepare($sql);
        $this->bindparameter(1,$terminalID);
        $this->execute();
        $result = $this->fetchData();
        if(!isset($result['TransactionSummaryID']))
            return false;
        return $result['TransactionSummaryID'];
    }
    
    
    /**
     * @author Gerardo V. Jagolino Jr.
     * @param type $site_id,$terminal_id
     * @return type array
     * Description: get transaction summary details
     */
    public function getTransactionSummaryDetail($site_id,$terminal_id) {
        $sql = 'SELECT TransactionsSummaryID, Reload, Withdrawal FROM transactionsummary WHERE SiteID = ? AND TerminalID = ? AND DateEnded = 0 ORDER BY TransactionsSummaryID DESC LIMIT 1';
        $this->prepare($sql);
        $this->bindparameter(1, $site_id);
        $this->bindparameter(2, $terminal_id);
        $this->execute();
        return $this->fetchData();
    }
    
    /**
     * @author Gerardo V. Jagolino Jr.
     * @param $newbal, $site_id, $transdtl
     * @return boolean
     * Description: update BCF by deducting the new balance
     */
    public function updateBcf($newbal, $site_id, $transdtl) {
        $sql = 'UPDATE sitebalance SET Balance = ?, LastTransactionDate = now_usec(), ' . 
                'LastTransactionDescription = ? WHERE SiteID = ?';
        $this->prepare($sql);
        $this->bindparameter(1, $newbal);
        $this->bindparameter(2, $transdtl);
        $this->bindparameter(3, $site_id);
        return $this->execute();
    }
    
    /**
     * @author Gerardo V. Jagolino Jr.
     * @param $site_id
     * @return array Balance
     * Description: get total balance in a site
     */
    public function getSiteBalance($site_id) {
        $sql = 'SELECT Balance FROM sitebalance WHERE SiteID = ?';
        $this->prepare($sql);
        $this->bindparameter(1, $site_id);
        $this->execute();
        $result = $this->fetchData();
        return $result['Balance'];
        
    }
    
    
    /**
     * @author Gerardo V. Jagolino Jr.
     * @param $site_id
     * @return UserBased Password 
     * Description: get total balance in a site
     */
    public function getUBLogin($terminalid) {
        $sql = 'SELECT UBServiceLogin FROM terminalsessions WHERE TerminalID = ?';
        $this->prepare($sql);
        $this->bindparameter(1, $terminalid);
        $this->execute();
        $result = $this->fetchData();
        return $result['UBServiceLogin'];
        
    }
    
     /**
     * @author Gerardo V. Jagolino Jr.
     * @param $array, $index, $search
     * @return array 
     * Description: get Find a certain service in Casino Array
     */
      function loopAndFindCasinoService($array, $index, $search){
        $returnArray = array();
            foreach($array as $k=>$v){
                  if($v[$index] == $search){   
                       $returnArray[] = $v;
                  }
            }
      return $returnArray;
      }
    
    /**
     * @author Gerardo V. Jagolino Jr.
     * @param $site_id
     * @return UserBased Password 
     * Description: get UserBased password
     */
    public function getUBPassword($terminalid) {
        $sql = 'SELECT UBServicePassword FROM terminalsessions WHERE TerminalID = ?';
        $this->prepare($sql);
        $this->bindparameter(1, $terminalid);
        $this->execute();
        $result = $this->fetchData();
        return $result['UBServicePassword'];
        
    }
    
    
    /**
     * @author Gerardo V. Jagolino Jr.
     * @param $serviceid, $terminalid
     * @return boolean 
     * Description: update terminal sessions casino service
     */
    public function upTerminalSessServcID($serviceid, $amount, $terminalid) {
        
        $this->begintrans();
            try {
                
                $stmt = "UPDATE terminalsessions SET ServiceID = ?, LastBalance = ? WHERE TerminalID = ?";
                
                $this->prepare($stmt);

                $this->bindparameter(1, $serviceid);
                $this->bindparameter(2, $amount);
                $this->bindparameter(3, $terminalid);
            
                if($this->execute()){
                    try {
                        $this->committrans();
                        return true;
                    } catch(Exception $e) {
                        $this->rollbacktrans();
                        return false;
                    }
                } else {
                    $this->rollbacktrans();
                    return false;
                }
            } catch (Exception $e) {
                $this->rollbacktrans();
                return false;
            }
        
    }
    
    
    /**
     * @author Gerardo V. Jagolino Jr.
     * @param $serviceid, $originid
     * @return last inserted id 
     * Description: insert Service Transaction Reference ID
     */
    public function insertServiceTransRef($serviceid, $originid) {
        
        $this->begintrans();
            try {
                
                $stmt = "INSERT INTO servicetransactionref (ServiceID, TransactionOrigin, DateCreated) 
                    VALUES (?, ?, now_usec())";
                
                $this->prepare($stmt);

                $this->bindparameter(1, $serviceid);
                $this->bindparameter(2, $originid);
            
                if($this->execute()){
                    try {
                        $id = $this->insertedid();
                        $this->committrans();
                        return $id;
                    } catch(Exception $e) {
                        $this->rollbacktrans();
                        return false;
                    }
                } else {
                    $this->rollbacktrans();
                    return false;
                }
            } catch (Exception $e) {
                $this->rollbacktrans();
                return false;
            }
        
    }
      
   

    
    /**
     * @author Gerardo V. Jagolino Jr.
     * @param $terminal_id, $service_id
     * @return array 
     * Description: get terminal password
     */    
    public function getTerminalPassword($terminal_id, $service_id){
        $sql = "SELECT t.ServicePassword FROM terminalservices t WHERE t.TerminalID = ? AND ServiceID = ? AND t.Status = 1";
        $this->prepare($sql);
        $this->bindparameter(1, $terminal_id);
        $this->bindparameter(2, $service_id);
        $this->execute();
        
        return $this->fetchData();
    }
    
    
    
    /**
     * @author Gerardo V. Jagolino Jr.
     * @param $terminal_id,$service_id,$amount,$trans_summary_id, $loyalty_card, $mid, $user_mode, $casino_login, $casino_pwd
     * @return boolean
     * Description: insert in terminal sessions
     */
    public function insert($terminal_id,$service_id,$amount,$trans_summary_id, 
                           $loyalty_card, $mid, $user_mode, $casino_login, $casino_pwd, $hashedcasino_pwd) {
     
        $this->begintrans();
            try {
                
                $stmt = "INSERT INTO terminalsessions (TerminalID, ServiceID, DateStarted, 
                    LastBalance, LastTransactionDate, TransactionSummaryID, LoyaltyCardNumber, 
                    MID, UserMode, UBServiceLogin, UBServicePassword, UBHashedServicePassword) 
                    VALUES (?, ?, now_usec(), ?, now_usec(), ?, ?, ?, ?, ?, ?, ?)";
                
                $this->prepare($stmt);

                $this->bindparameter(1, $terminal_id);
                $this->bindparameter(2, $service_id);
                $this->bindparameter(3, $amount);
                $this->bindparameter(4, $trans_summary_id);
                $this->bindparameter(5, $loyalty_card);
                $this->bindparameter(6, $mid);
                $this->bindparameter(7, $user_mode);
                $this->bindparameter(8, $casino_login);
                $this->bindparameter(9, $casino_pwd);
                $this->bindparameter(10, $hashedcasino_pwd);
            
                if($this->execute()){
                    try {
                        $this->committrans();
                        return true;
                    } catch(Exception $e) {
                        $this->rollbacktrans();
                        return false;
                    }
                } else {
                    $this->rollbacktrans();
                    return false;
                }
            } catch (Exception $e) {
                $this->rollbacktrans();
                return false;
            }
    }
    
    public function getServiceGrpNameByName($servicename){
        $sql = 'SELECT rsg.ServiceGroupName FROM ref_services rs
                INNER JOIN ref_servicegroups rsg ON rs.ServiceGroupID = rsg.ServiceGroupID
                WHERE rs.ServiceName = ?';
        $this->prepare($sql);
        $this->bindparameter(1, $servicename);
        $this->execute($sql);
        $result =  $this->fetchData();
        if(!isset($result['ServiceGroupName']))
            return false;
        return $result['ServiceGroupName'];
    }
    
    
    public function getusermode($serviceid) {
        $sql = 'SELECT UserMode FROM ref_services WHERE ServiceID = ?';
        $this->prepare($sql);
        $this->bindparameter(1, $serviceid);
        $this->execute();
        $usermode = $this->fetchData();
        return $usermode['UserMode'];
    }
    
}
?>