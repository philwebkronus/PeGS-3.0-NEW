<?php

/*
 * @Date Nov 20, 2012
 * @Author owliber
 */
?>
<?php
class Stacker extends CFormModel
{
    CONST CASH_TYPE_ALL = 'All';    
    CONST CASH_TYPE_TICKET = 1;
    CONST CASH_TYPE_COUPON = 2;
    CONST CASH_TYPE_BILL = 3;
    
    public function getStackerSessions($egmmachines)
    {
                
        if(count($egmmachines) > 1)
        {
            $egmmachines = implode(', ',$egmmachines);  

        }
                              
        $query = "SELECT a.EGMStackerSessionID
                        , a.EGMMachineInfoID
                        , b.ComputerName
                        , a.DateStarted
                        , a.DateEnded
                        , a.Quantity
                        , a.CashAmount
                        , a.TotalAmount
                   FROM
                     egmstackersessions a
                   INNER JOIN egmmachineinfo b
                   ON a.EGMMachineInfoID = b.EGMMachineInfoId_PK
                   WHERE EGMMachineInfoID IN ($egmmachines)
                     AND IsEnded = 0";
         
            $sql = Yii::app()->db->createCommand($query);
        
       $result = $sql->queryAll();
       return $result;       
        
    }
    
    public function getAllStackerSessions($datefrom,$dateto,$egmmachines,$session)
    {
        //Add 1 day to dateto to return all records for the current date
        $newdate = strtotime ( '+1 day' , strtotime ( $dateto ) ) ;
        $dateto = date ( 'Y-m-d' , $newdate );
        
        if($session)
            $session = '0,1';
        else
            $session = 0;
        
        if(count($egmmachines) > 1)
        {
            $egmmachines = implode(', ',$egmmachines);  

        }
                              
        $query = "SELECT a.EGMStackerSessionID
                        , a.EGMMachineInfoID
                        , b.ComputerName
                        , a.DateStarted
                        , a.DateEnded
                        , a.Quantity
                        , a.CashAmount
                        , a.TotalAmount
                   FROM
                     egmstackersessions a
                   INNER JOIN egmmachineinfo b
                   ON a.EGMMachineInfoID = b.EGMMachineInfoId_PK
                   WHERE a.DateStarted >=:datefrom
                     AND a.DateStarted <:dateto
                     AND a.EGMMachineInfoID IN ($egmmachines)
                     AND a.IsEnded IN ($session)";
         
            $sql = Yii::app()->db->createCommand($query);
            
            $sql->bindValues(array(
                    ':datefrom'=>$datefrom,
                    ':dateto'=>$dateto,  
            ));
        
       $result = $sql->queryAll();
       return $result;       
        
    }
    
    public function cashTypes()
    {
        return array(
            self::CASH_TYPE_ALL => 'All',
            self::CASH_TYPE_BILL =>  'Cash',
            self::CASH_TYPE_TICKET => 'Ticket',
            self::CASH_TYPE_COUPON => 'Coupon',
        );
    }
    
    public function getMachineIDByTerminal($terminalid)
    {
        $query = "SELECT EGMMachineInfoId_PK
                    FROM egmmachineinfo
                    WHERE TerminalID =:terminalid
                    OR TerminalIDVIP =:terminalid";
        
        $sql = Yii::app()->db->createCommand($query);
        $sql->bindValue(":terminalid", $terminalid);
        $result = $sql->queryRow();
        return $result['EGMMachineInfoId_PK'];
    }
    
    public function getMachineNameBySession($sessionid)
    {
        $query = "SELECT ComputerName
                    FROM egmmachineinfo a
                        INNER JOIN egmstackersessions b
                        ON a.EGMMachineInfoId_PK = b.EGMMachineInfoId
                    WHERE b.EGMStackerSessionID =:sessionid";
        
        $sql = Yii::app()->db->createCommand($query);
        $sql->bindValue(":sessionid", $sessionid);
        $result = $sql->queryRow();
        return $result['ComputerName'];
    }
    
    public function getTerminalsByMachine($machineid)
    {
        $query = "SELECT TerminalID,TerminalIDVIP
                    FROM egmmachineinfo
                    WHERE EGMMachineInfoId_PK =:machineid";
        $sql = Yii::app()->db->createCommand($query);
        $sql->bindValue(":machineid", $machineid);
        $result = $sql->queryRow();
        return $result;
    }
    
    public function activeEGMMachinesBySite($siteid)
    {
        $query = "SELECT EGMMachineInfoId_PK,ComputerName 
                    FROM egmmachineinfo
                    WHERE POSAccountNo =:siteid
                    AND Status = 1";
        $sql = Yii::app()->db->createCommand($query);
        $sql->bindValue(":siteid", $siteid);
        $result = $sql->queryAll();
        return $result;
    }
    
    public function activeEGMMachines()
    {
        $query = "SELECT EGMMachineInfoId_PK,ComputerName 
                    FROM egmmachineinfo
                    WHERE Status = 1";
        $sql = Yii::app()->db->createCommand($query);
        $result = $sql->queryAll();
        return $result;
    }
    
    public function listActiveEGMMachines($siteid=null)
    {
        if(!empty($siteid))
            return array('All'=>'All')+CHtml::listData(Stacker::activeEGMMachinesBySite($siteid), 'EGMMachineInfoId_PK', 'ComputerName');
        else
            return array('empty'=>'Select a site')+CHtml::listData(Stacker::activeEGMMachines(), 'EGMMachineInfoId_PK', 'ComputerName');
    }
    
    public function activeSites()
    {
        $query = "SELECT SiteID,SiteCode
                  FROM sites
                  WHERE Status = 1
                  ORDER BY 2";
        $sql = Yii::app()->db->createCommand($query);
        $result = $sql->queryAll();
        return $result;
    }
    
    public function listActiveSites()
    {
        return CHtml::listData(Stacker::activeSites(), 'SiteID', 'SiteCode');
    }
    
    public function getStackerEntriesBySessionID($sessionid)
    {
                
        $query = "SELECT a.EGMStackerEntryID
                    , a.TransactionDate
                    , c.ComputerName
                    , a.TerminalID
                    , CASE a.CashType
                      WHEN 1 THEN
                        'Ticket'
                      WHEN 2 THEN
                        'Coupon'
                      WHEN 3 THEN
                        'Cash'
                      END `CashType`
                    , a.VoucherCode
                    , a.Amount
                    , CASE a.TransactionType
                      WHEN 1 THEN
                        'Deposit'
                      WHEN 2 THEN
                        'Reload'
                      END `TransactionType`
               --     , (SELECT SUM(d.Amount)
               --         FROM egmstackerentries d
               --          WHERE d.EGMStackerSessionID =:sessionid) AS `Totals`
               FROM
                 egmstackerentries a
                INNER JOIN egmstackersessions b
                 ON a.EGMStackerSessionID = b.EGMStackerSessionID
                 INNER JOIN egmmachineinfo c ON b.EGMMachineInfoId = c.EGMMachineInfoId_PK
                  WHERE a.EGMStackerSessionID =:sessionid";
        
        $sql = Yii::app()->db->createCommand($query);
        $sql->bindValue(":sessionid", $sessionid);
        $result = $sql->queryAll();
        return $result;
    }
    
    public function getTotals($sessionid)
    {
        $query = "SELECT SUM(Amount) as `Total`
               FROM
                 egmstackerentries
                  WHERE EGMStackerSessionID =:sessionid";
        
        $sql = Yii::app()->db->createCommand($query);
        $sql->bindValue(":sessionid", $sessionid);
        $result = $sql->queryAll();
        return $result[0]['Total'];
    }
    
   
}
?>
