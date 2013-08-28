<?php

/**
 * Date Created 10 28, 11 1:11:44 PM <pre />
 * Description of TerminalsModel
 * @author Bryan Salazar
 */
class TerminalsModel extends MI_Model {
    
    /**
     * Description: Terminal code want to start session
     * @var string terminal_code
     */
    public $terminal_code;
    
    /**
     *
     * @param type $siteid
     * @return type 
     */
    public function getNumberOfTerminalsPerSite($siteid) {
        $sql = "SELECT COUNT(t.TerminalID) as num_terminals FROM terminals t WHERE t.SiteID = :siteid";
        $param = array(':siteid'=>$siteid);
        $this->exec($sql,$param);
        $result = $this->find();
        return $result['num_terminals'];
    }
    
    public function getTerminalPerPage($siteid,$start,$terminal_per_page,$len) {
        
        //get all terminals under specific site
        $sql1 = "SELECT t.TerminalID,SUBSTR(t.TerminalCode,$len) AS tc,t.TerminalName, DATE_FORMAT(ts.DateStarted,'%Y-%m-%d %H:%i:%s') DateStarted, t.SiteID, t.Status,t.isVIP,ts.LastTransactionDate, DATE_FORMAT(ts.LastTransactionDate,'%m/%d/%Y %H:%i:%s') as ltd,
            TIMESTAMPDIFF(MINUTE,LastTransactionDate,NOW()) as minutes,TIMESTAMPDIFF(MINUTE,ts.DateStarted,NOW()) as dif , FORMAT(ts.LastBalance,2) as lastbalance,C.Status as ServiceStatus, C.ServiceID, ts.ServiceID as usedServiceID
            FROM terminals t LEFT JOIN terminalsessions ts ON (t.terminalID = ts.terminalID) INNER JOIN terminalservices AS C ON t.TerminalID = C.TerminalID " . 
            "WHERE t.SiteID = :siteid  AND C.isCreated = 1 
            GROUP BY t.TerminalCode ORDER BY CAST(`tc` AS SIGNED), TerminalID LIMIT :start,".$terminal_per_page;
        
        $param = array(':siteid'=>$siteid,':start'=>$start);
        $this->exec($sql1,$param);
        $results = $this->findAll();
        
        $terminalArray1 = array(); //returning array
        $terminalArray2 = array(); //for array with ServiceStatus is equal to 0
        
        //counter variables
        $counter = count($results);
        $ctr3 = 0;
        $ctr4 = 0;
        
        //separate terminals with service status equal to 0
        for($ctr = 0;$ctr < $counter;$ctr++){
            if($results[$ctr]['ServiceStatus'] == 0){
                $terminalArray2[$ctr3] = $results[$ctr]['TerminalID'];
                $ctr3++;
            }
        }
        
        $cnt2 = count($results);
        $termarray2Count = count($terminalArray2);
        
        //check if terminals  have inactive casinos.
        if( $termarray2Count != 0){
            $data = implode(",", $terminalArray2);
            
            //Check terminals with active service status.
            $sql2 = "SELECT C.TerminalID, C.ServiceID, C.Status  FROM terminalservices as C
                            WHERE C.Status = 1 AND TerminalID IN ($data)
                            GROUP BY TerminalID ";
             $this->exec($sql2);
             $serviceID = $this->findAll();
             
             $cnt1 = count($serviceID);
             
             //update Service ID and Service Status of terminal/s with active service/s.
            for($ctr = 0;$ctr < $cnt1;$ctr++){
                for($ctr1 = 0;$ctr1 < $cnt2;$ctr1++){
                    if($results[$ctr1]['TerminalID'] == $serviceID[$ctr]['TerminalID']){
                        $results[$ctr1]['ServiceID'] = $serviceID[$ctr]['ServiceID'];
                        $results[$ctr1]['ServiceStatus'] = 1;
                    }
                }
            }
        }
        
        //separate terminals with active service/s.
        for($ctr = 0;$ctr < $cnt2;$ctr++){
            if($results[$ctr]['ServiceStatus'] == 1){
                $terminalArray1[$ctr4] = $results[$ctr];
                $ctr4++;
            }
        }
        unset($terminalArray2,$results);
        return $terminalArray1;
    }
    /**
     * Description: use for get balance per page
     * @param type $siteid
     * @param type $start
     * @param type $terminal_per_page
     * @param type $len
     * @return type 
     */
    public function getAllActiveTerminalPerPage($siteid,$start,$terminal_per_page,$len) {
//        $sql = "SELECT t.TerminalID,SUBSTR(t.TerminalCode,$len) AS tc, t.SiteID, t.Status,t.isVIP,ts.LastTransactionDate, DATE_FORMAT(ts.LastTransactionDate,'%m/%d/%Y %H:%i:%s') as ltd,
//            TIMESTAMPDIFF(MINUTE,LastTransactionDate,NOW()) as minutes,TIMESTAMPDIFF(MINUTE,ts.DateStarted,NOW()) as dif , FORMAT(ts.LastBalance,2) as lastbalance, C.ServiceID, ts.ServiceID as usedServiceID
//            FROM terminals t LEFT JOIN terminalsessions ts ON t.terminalID = ts.terminalID INNER JOIN terminalservices AS C ON t.TerminalID = C.TerminalID " . 
//            "WHERE t.SiteID = :siteid AND C.Status = 1
//            GROUP BY t.TerminalCode ORDER BY CAST(`tc` AS SIGNED), TerminalID LIMIT :start,".$terminal_per_page;
        
        $sql = "SELECT t.TerminalID,SUBSTR(t.TerminalCode,$len) AS tc,t.TerminalName, DATE_FORMAT(ts.DateStarted,'%Y-%m-%d %H:%i:%s') DateStarted, t.SiteID, t.Status,t.isVIP,ts.LastTransactionDate, DATE_FORMAT(ts.LastTransactionDate,'%m/%d/%Y %H:%i:%s') as ltd,
            TIMESTAMPDIFF(MINUTE,LastTransactionDate,NOW()) as minutes,TIMESTAMPDIFF(MINUTE,ts.DateStarted,NOW()) as dif , FORMAT(ts.LastBalance,2) as lastbalance, C.ServiceID, ts.ServiceID as usedServiceID
            FROM terminals t LEFT JOIN terminalsessions ts ON (t.terminalID = ts.terminalID) INNER JOIN terminalservices AS C ON t.TerminalID = C.TerminalID " . 
            "WHERE t.SiteID = :siteid AND C.Status = 1 AND C.isCreated = 1 
            GROUP BY t.TerminalCode ORDER BY CAST(`tc` AS SIGNED), TerminalID LIMIT :start,".$terminal_per_page;        
        $param = array(':siteid'=>$siteid,':start'=>$start);
        $this->exec($sql,$param);
        return $this->findAll();
    }
    
    public function getDataByTerminalId($terminal_id) {
        $sql = 'SELECT isVIP, TerminalCode FROM terminals WHERE TerminalID = :terminal_id';
        $param = array(':terminal_id'=>$terminal_id);
        $this->exec($sql, $param);
        return $this->find();
    }
    
    public function getServices($siteid) {
        $sql = "SELECT ts.TerminalID, ts.ServiceID, rs.Code FROM terminals t INNER JOIN terminalservices ts ON t.TerminalID = ts.TerminalID " .
                "INNER JOIN ref_services rs ON rs.ServiceID = ts.ServiceID WHERE " . 
                "ts.Status = 1 AND ts.isCreated = 1  AND t.SiteID = :siteid";
        $param = array(':siteid'=>$siteid);
        $this->exec($sql,$param);
        return $this->findAll();
    }
    
    
    public function getServicesGroupByTerminal($siteid) {
        $terminals = array();
        $services = $this->getServices($siteid);
        foreach($services as $service) {
            if(!isset($terminals[$service['TerminalID']]))
                $terminals[$service['TerminalID']] = $service['Code'];
            else
                $terminals[$service['TerminalID']] .= ', ' . $service['Code'];
        }
        
        return $terminals;
    }
    
    public function getTerminalName($terminal_id) {
        $sql = 'SELECT TerminalCode FROM terminals WHERE TerminalID = :terminal_id';
        $param = array(':terminal_id'=>$terminal_id);
        $this->exec($sql,$param);
        $result = $this->find();
        if(!isset($result['TerminalCode']))
            return false;
        return $result['TerminalCode'];
    }
    
    public function getTerminalsToStartSession($site_id,$len) {
        $sql = "SELECT A.TerminalID TId,SUBSTR(A.TerminalCode,$len) AS tc, A.TerminalCode TCode FROM terminals A " . 
                "LEFT JOIN terminalsessions B ON A.TerminalID = B.TerminalID INNER JOIN terminalservices AS C ON A.TerminalID = C.TerminalID " . 
                "WHERE A.Status = '1' AND A.SiteID = :site_id  AND C.Status = 1 AND C.isCreated = 1 AND " . 
                "B.DateEnded IS NULL ORDER BY CAST(`tc` AS SIGNED), A.TerminalID";
        $param = array(':site_id'=>$site_id);
        $this->exec($sql,$param);
        return $this->findAll();
    }
    
    public function getAllActiveTerminals($site_id,$len) {
        $sql = "SELECT A.TerminalID TId,SUBSTR(A.TerminalCode,$len) AS tc, A.TerminalCode TCode FROM terminals A " . 
                "INNER JOIN terminalsessions B ON A.TerminalID = B.TerminalID " . 
                "WHERE A.Status = '1' AND A.SiteID = :site_id ORDER BY CAST(`tc` AS SIGNED), A.TerminalID";
        $param = array(':site_id'=>$site_id);
        $this->exec($sql,$param);
        $terminals = $this->findAll();
        $vips = array();
        $non_vips = array();
        foreach($terminals as $terminal) {
            $tid = $terminal['TId'];
            $tdesc = $terminal['tc'];            
            if(strpos(strtolower($tdesc), 'vip')) {
                $vips[] = array('id'=>$tid,'code'=>$tdesc);
            } else {
                $non_vips[] = array('id'=>$tid,'code'=>$tdesc);
            }
        }
        $ts = array_merge($non_vips, $vips);
        return $ts;
    }
    
    public function getAllNotActiveTerminals($site_id,$len) {
        $terminals = $this->getTerminalsToStartSession($site_id, $len);
        $vips = array();
        $non_vips = array();
//        $tcodes = array();
//        
//        // get all terminal code
//        foreach ($terminals as $terminal) {
//            $tcodes[] = $terminal['TCode']; 
//        }
//        
//        $not_allowed_tcode = array();
//        foreach($tcodes as $tcode) {
//            if(strpos(strtolower($tcode), 'vip') !== false) {
//                if(!in_array($tcode.'vip', $tcodes)) {
//                    $not_allowed_tcode[] = $tcode;
//                }
//            }
//        }
        
        $v = array();
        $nv = array();
//        debug($terminals); exit;
        // separate vip and non-vip
        foreach($terminals as $terminal) {
            $tid = $terminal['TId'];
            $tdesc = $terminal['TCode'];   
            if(strpos(strtolower($tdesc), 'vip')) {
                $v[$tid] = $tdesc;
            } else {
                $nv[$tid] = $tdesc;
            }
        }
//        debug($v);
//        debug($nv);
//        exit;
        $allowed = array();
        // remove vip if partner regular not exist
        foreach($v  as $key => $value) {
            if(!in_array(substr($value, 0,-3), $nv)) {
                unset($v[$key]);
            }
        }
        // remove regular if partner vip not exist
        foreach($nv as $key => $value) {
            if(!in_array($value.'VIP',$v)) {
                unset($nv[$key]);
            }
        }

        foreach($terminals as $terminal) {
            $tid = $terminal['TId'];
            $tdesc = $terminal['TCode'];      
//            if(in_array($tdesc,$allowed)) {
//                if(strpos(strtolower($tdesc), 'vip')) {
//                    $vips[] = array('id'=>$tid,'code'=>substr($tdesc, $len - 1));
//                } else {
//                    $non_vips[] = array('id'=>$tid,'code'=>substr($tdesc, $len - 1));
//                }
//            }
            if(in_array($tdesc,$v) || in_array($tdesc, $nv)) {
                if(strpos(strtolower($tdesc), 'vip')) {
                    $vips[$tid] = array('id'=>$tid,'code'=>substr($tdesc, $len - 1));
                } else {
                    $non_vips[$tid] = array('id'=>$tid,'code'=>substr($tdesc, $len - 1));
                }
            }
        }
//        debug($non_vips); 
//        debug($vips);
//        exit;
        
        $ts = array_merge($non_vips, $vips);
//        debug($ts); exit;
        return $ts;
    }
    
    public function isPartnerAlreadyStarted($terminal_id) {
        $sql = 'SELECT TerminalCode FROM terminals WHERE TerminalID = :terminal_id';
        $param = array(':terminal_id'=>$terminal_id);
        $this->exec($sql,$param);
        $result = $this->find();
        $terminal_code = $result['TerminalCode'];
        $len = strlen($_SESSION['site_code']);
        $this->terminal_code = substr($terminal_code, $len);
        if(stripos($terminal_code, 'VIP') !== false) {
            $terminal_code = str_replace('VIP', '', $terminal_code);
        }
        $sql1 = "SELECT t.TerminalID FROM terminals t INNER JOIN terminalsessions ts ON ts.TerminalID = t.TerminalID " . 
                "WHERE t.TerminalCode LIKE '%$terminal_code' AND t.TerminalID != :terminal_id";
        $this->exec($sql1, $param);
        $res = $this->find();
        if(isset($res['TerminalID']) && $res['TerminalID']) {
            return true;
        }
        return false;
    }
   
    public function getTerminalPassword($terminal_id, $service_id){
        $sql = "SELECT t.ServicePassword, t.HashedServicePassword FROM terminalservices t 
                WHERE t.TerminalID = :terminal_id AND ServiceID = :service_id AND t.Status = 1";
        $param = array(':terminal_id'=>$terminal_id,':service_id'=>$service_id);
        $this->exec($sql,$param);
        return $this->find();
    }
    
    public function insertserviceTransRef($service_id, $origin_id)
    {
        try {
           $this->dbh->beginTransaction();
           $smt = $this->dbh->prepare("INSERT INTO servicetransactionref (ServiceID, TransactionOrigin, DateCreated) VALUES (?, ?, now_usec())");
           $smt->bindValue(1, $service_id, PDO::PARAM_INT);
           $smt->bindValue(2, $origin_id, PDO::PARAM_INT);
           if(!$smt->execute()) {
               $this->dbh->rollBack();
               return false;
           }  
           $transaction_id = $this->dbh->lastInsertId();
           $this->dbh->commit();
           return $transaction_id;
       } catch(PDOException $e) {
           $this->dbh->rollBack();
           return false;
       }
    }
    
}

