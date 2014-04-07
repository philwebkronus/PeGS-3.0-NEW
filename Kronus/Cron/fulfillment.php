<?php
    include_once "init.php";
    require_once 'helper/common.class.php';
    include_once 'CasinoAPIHandler.class.php';
      
    mt_srand( (double) microtime() * 1000000 );
    $dtTransactionId = date("YmdHis") . mt_rand( 10000, 99999 );

    $conn = explode( ",", $_DBConnectionString[0]);
    $oconnectionstring1 = $conn[0];
    $oconnectionstring2 = $conn[1];
    $oconnectionstring3 = $conn[2];
    $err = 0;
    //select transaction with pending trans: one at the time only
    $dbh = new PDO( $oconnectionstring1, $oconnectionstring2, $oconnectionstring3);
    $stmt = "SELECT tl.TransactionRequestLogID,tl.TransactionReferenceID,"."
        tl.Amount,tl.StartDate,tl.EndDate,tl.TransactionType,tl.TerminalID,tl.Status,"."
        tl.SiteID,tl.ServiceID,t.TerminalCode FROM  "."
        transactionrequestlogs tl  inner join terminals t on t.TerminalID = tl.TerminalID "."
        where tl.Status = 0 order by StartDate ASC LIMIT 1";
    $sth = $dbh->prepare($stmt);
    $sth->execute();
    $result = $sth->fetch(PDO::FETCH_LAZY);
    if($result)
    {
        $serverid = $result['ServiceID'];
        $terminalid = $result['TerminalID'];
        $terminalcode = $result['TerminalCode'];
        $tramount = $result['Amount'];
        $trlogid = $result['TransactionRequestLogID'];
        $siteid = $result['SiteID'];
        $ttype = $result['TransactionType'];
        $trefid = $result['TransactionReferenceID'];

        switch($serverid )
        {
          case $serverid  >= 1 and $serverid  < 8 :
          case 10:
          case 11:
              $configuration = array( 'URI' => $_ServiceAPI[$serverid-1],
                            'isCaching' => FALSE,
                            'isDebug' => TRUE,
                            'certFilePath' => RTGCerts_DIR . $serverid . '/cert.pem',
                            'keyFilePath' => RTGCerts_DIR . $serverid . '/key.pem',
                            'depositMethodId' => 502,
                            'withdrawalMethodId' => 503 );
              $_CasinoAPIHandler = new CasinoAPIHandler( CasinoAPIHandler::RTG, $configuration );
              break;     
        }

        if ((bool)$_CasinoAPIHandler->IsAPIServerOK() )
        {
            $login = $terminalcode;            
            $tracking1 = $trlogid ;
            $tracking3 = $terminalid;
            $tracking4 = $siteid;
            switch ($ttype)
            {
//                case 'D' :
//                    //call deposit method
//                    $amount = $tramount;
//                    $tracking2 = 'D';
//                    $apiresult = $_CasinoAPIHandler->Deposit($login, $amount, $tracking1 , $tracking2 , $tracking3, $tracking4 );
//                    break;
                case 'R' :
                    //call deposit method
                    $amount = $tramount;
                    $tracking2 = 'R';
                    $apiresult = $_CasinoAPIHandler->Deposit($login, $amount, $tracking1 , $tracking2 , $tracking3, $tracking4 );
                    break;
                case 'W' :
                    //call withdrawal method
                    $balanceinfo = $_CasinoAPIHandler->GetBalance($login);
                    $amount = $balanceinfo['BalanceInfo']['Balance'];
                    $tracking2 = 'W';
                    $apiresult = $_CasinoAPIHandler->Withdraw($login, $amount, $tracking1 , $tracking2, $tracking3 , $tracking4 );
                    break;
            }

            if ( !is_null( $apiresult ) ) // check if $api returns result
            {
                 if ( $apiresult[ 'IsSucceed' ] == true ) // check if api returns successful trans
                 {                 
                    switch($serverid)
                    {
                      case $serverid  >= 1 and $serverid  < 8 :
                      case 10:
                      case 11:
                          switch($ttype)
                          {
                            //case 'D':
                            case 'R':
                                 $transrefid = $apiresult['TransactionInfo']['DepositGenericResult']['transactionID'];
                                 $apistat = $apiresult['TransactionInfo']['DepositGenericResult']['transactionStatus'];
                                 $apierrmsg = $apiresult['TransactionInfo']['DepositGenericResult']['errorMsg'];
                                 break;
                            case 'W':
                                 $transrefid = $apiresult['TransactionInfo']['WithdrawGenericResult']['transactionID'];
                                 $apistat = $apiresult['TransactionInfo']['WithdrawGenericResult']['transactionStatus'];
                                 $apierrmsg = $apiresult['TransactionInfo']['WithdrawGenericResult']['errorMsg'];
                                 break;

                          }              
                          if($apistat == 'TRANSACTIONSTATUS_APPROVED') 
                          {
                            $transstatus = '1';
                          }
                          else
                          {
                            $transstatus = '2';
                          }    
                      break;       
                    }           

                    if($apierrmsg) // if not an error update,insert records to tables
                    {
                        $res = 0;                        
                        switch ($ttype)
                         {
//                            case 'D' :                               
//                                 //insert to transaction summary
//                                 $stmt =  "INSERT INTO transactionsummary (SiteID, "."
//                                     TerminalID, Deposit, DateStarted, DateEnded,"."
//                                     CreatedByAID) VALUES (?,?,?,now_usec(),'0',?);";  
//                                 $sth = $dbh->prepare($stmt);
//                                 $sth->bindParam(1,$siteid); 
//                                 $sth->bindParam(2,$terminalid); 
//                                 $sth->bindParam(3,$amount); 
//                                 $sth->bindParam(4,$acctid); 
//                                 $res = $sth->execute();   
//                                 $tsummid = $dbh->lastInsertId(); 
//                            break;
                            case 'R' :                           
                                 //get transaction summary details
                                 $stmt = "SELECT TransactionsSummaryID, Reload "."
                                     FROM transactionsummary WHERE SiteID = ? "."
                                     AND TerminalID = ? AND DateEnded = 0 "."
                                     ORDER BY TransactionsSummaryID DESC LIMIT 1;";
                                 $sth = $dbh->prepare($stmt);
                                 $sth->bindParam(1,$siteid);
                                 $sth->bindParam(2,$terminalid); 
                                 $res = $sth->execute();  
                                 $oldreload = $sth->fetch(PDO::FETCH_LAZY); 

                                 //get total reload, transsummary id
                                 $tsummid = $oldreload['TransactionsSummaryID'];
                                 $tlreload = 0;
                                 $tlreload = $oldreload['Reload'] + $amount;

                                 //update transaction summary (reload)
                                 $stmt = "UPDATE transactionsummary SET Reload = ?  "."
                                     WHERE TransactionsSummaryID = ?";
                                 $sth = $dbh->prepare($stmt);
                                 $sth->bindParam(1,$tlreload);
                                 $sth->bindParam(2,$tsummid);
                                 $res = $sth->execute(); 
                                 
                            break;
                            case 'W' :                         
                                 //get transaction summary details
                                 $stmt = "SELECT TransactionsSummaryID,Withdrawal  "."
                                     FROM transactionsummary WHERE "."
                                     SiteID = ? AND TerminalID = ? AND DateEnded = 0 "."
                                     ORDER BY TransactionsSummaryID DESC LIMIT 1;";
                                 $sth = $dbh->prepare($stmt);
                                 $sth->bindParam(1,$siteid);
                                 $sth->bindParam(2,$terminalid); 
                                 $res = $sth->execute();  
                                 $withdrawal = $sth->fetch(PDO::FETCH_LAZY);                                 
                                 
                         
                                 $tramount = $amount;
                                 //update transaction summary (withdraw)
                                 $tsummid = $withdrawal['TransactionsSummaryID'];
                                 $stmt = "UPDATE transactionsummary SET Withdrawal = ?  "."
                                     WHERE TransactionsSummaryID = ?";
                                 $sth = $dbh->prepare($stmt);
                                 $sth->bindParam(1,$tramount);
                                 $sth->bindParam(2,$tsummid);
                                 $res = $sth->execute(); 

                            break;                            
                         }                    
                         if($res > 0)
                         {
                             $vcreatedby = 1; //by system via cron
                             //insert in transaction details 
                               
                              $stmt = "INSERT INTO transactiondetails (TransactionReferenceID,"."
                              TransactionSummaryID, SiteID, TerminalID, TransactionType,"."
                              Amount, DateCreated, ServiceID, CreatedByAID, Status) "."
                              VALUES (?,?,?,?,?,?,now_usec(),?,?,?);";
                              $sth = $dbh->prepare($stmt);
                              $sth->bindParam(1,$trefid );
                              $sth->bindParam(2,$tsummid);
                              $sth->bindParam(3,$siteid);
                              $sth->bindParam(4,$terminalid);
                              $sth->bindParam(5,$ttype);
                              $sth->bindParam(6,$amount);
                              $sth->bindParam(7,$serverid);
                              $sth->bindParam(8,$vcreatedby );
                              $sth->bindParam(9,$transstatus);
                              $sth->execute(); 
                             
                              if($transstatus == 1)
                              {
                                  switch($ttype)
                                  {
//                                    case 'D' :  
//                                          $stmt = "INSERT INTO terminalsessions (TerminalID, ".
//                                                  "ServiceID, DateStarted, LastBalance, LastTransactionDate, ".
//                                                  "TransactionSummaryID) VALUES (?,?,now_usec(),?,now_usec(),?)";
//                                          $sth = $dbh->prepare($stmt);
//                                          $sth->bindParam(1,$terminalid);
//                                          $sth->bindParam(2,$serverid);
//                                          $sth->bindParam(3,$tramount);
//                                          $sth->bindParam(4,$transsummaryid);
//                                          $sth->execute(); 
//                                    break;
                                    case 'R' :
                                          $balanceinfo = $_CasinoAPIHandler->GetBalance($login);
                                          if(!$balanceinfo['BalanceInfo']['Balance'])
                                          {
                                            $err = 3;
                                            $errmess = "failed insert record in terminalsession(reload)";
                                          } 
                                          else
                                          {
                                            $stmt = "UPDATE terminalsessions SET LastBalance =?".
                                                  ",LastTransactionDate = now_usec() WHERE".
                                                  "TerminalID = ? and TransactionSummaryID =?";
                                            $sth = $dbh->prepare($stmt);
                                            $sth->bindParam(1,$balanceinfo['BalanceInfo']['Balance']);
                                            $sth->bindParam(2,$terminalid);
                                            $sth->bindParam(3,$transsummaryid);
                                            $sth->execute();  
                                          }
                                    break;
                                    case 'W' :
                                        $stmt = "Delete from terminalsessions WHERE TerminalID =?";
                                        $sth = $dbh->prepare($stmt);
                                        $sth->bindParam(1,$terminalid);
                                        $sth->execute();     
                                    break;  
                                  }
                              }
                              else
                              {
                                 $err = 3;
                                 $errmess = "failed insert record in terminalsession ";
                              }                               
   
                         }
                         else // log error
                         {
                             //failed insert record in transactiondetails                         
                             $err = 4;
                             $errmess = "failed insert record in transactiondetails ";                             
                         }
                    }
                    else // log error
                    {
                        //$apierrmsg
                        $err = 5;  
                        $errmess = $apierrmsg;
                    }
                 }
                 else //log error
                 {
                     //is succeed = false
                     $err = 6;  
                     $errmess = "api-issucceed = false ";
                 }

                //update status in transactionrequestlog
                $stmt= "UPDATE transactionrequestlogs SET ServiceStatus = ?"."
                    ,ServiceTransactionID = ?,Status = ?,EndDate = now_usec()"." 
                    WHERE TransactionRequestLogID = ?";
                $sth = $dbh->prepare($stmt);
                $sth ->bindParam(1,$apistat);  
                $sth ->bindParam(2,$transrefid); 
                $sth ->bindParam(3,$transstatus); 
                $sth ->bindParam(4,$trlogid);                         
                $sth->execute();

                 //insert logs in fullfillment log table
                 if($err > 2)
                 {
                     $stmt = "INSERT into fulfillment (TransactionRequestLogID,LogsDesc,DateCreated)"."
                         VALUES(?,?,now_usec())";
                     $sth = $dbh->prepare($stmt);
                     $sth->bindParam(1,$trlogid);
                     $sth->bindParam(2,$err);
                     $sth->execute();
                 }
                 else
                 {
                     $stmt = "INSERT into fulfillment (TransactionRequestLogID,LogsDesc,DateCreated)"."
                         VALUES(?,?,now_usec())";
                     $sth = $dbh->prepare($stmt);
                     $sth->bindParam(1,$trlogid);
                     $sth->bindParam(2,$transstatus);
                     $sth->execute();                 
                 }             
            }
            else // log error
            {
              //if $apiresult is null
              $err = 7;
              $error = "api result is null";              
            }        
        }
        else //log error
        {
          //not connected 
          $err = 8;
          $error = "not connected";         
        }
    }
    else
    {
       $err = 9;
       $error = "Error in transactionrequestlog query";      
    }
    $dbh = null;
?>