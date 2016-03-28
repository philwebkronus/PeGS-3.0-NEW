<?php

class TicketsModel extends MI_Model{
    
    public function getExpiredTickets($date, $site){
        $cutoff_time = '23:59:59.0000';
        $startdate = '00:00:00';
        $sql = "SELECT SiteID, IFNULL(SUM(Amount), 0) AS ExpiredTickets FROM vouchermanagement.tickets
                WHERE SiteID = :site 
                AND (ValidToDate >= :date2 AND ValidToDate <= :date) AND ValidToDate <= NOW(6)
                AND Status IN (1,2,7)
                ORDER BY SiteID;";
        $param = array(':date'=>$date . ' ' . $cutoff_time, ':date2'=>$date . ' ' . $startdate, ':site'=>$site);
        $this->exec3($sql, $param);
        $result = $this->find3();
        
        if($result == false){
           $result = 0; 
        }
        else{
           $result = $result['ExpiredTickets']; 
        }
        return $result;
    }
    
    public function getrunningactivetickets($date, $enddate, $siteid){
        $sql = "SELECT SUM(Amount) AS DC FROM tickets WHERE SiteID = :site_id AND DateCreated >= :start_date 
            AND DateCreated < :end_date;";
        $param = array(':start_date'=>$date, ':end_date'=>$enddate, ':site_id'=>$siteid);
        $this->exec3($sql, $param);
        $resultz = $this->find3();
        
        $result1 = $resultz['DC'];
        
        $sql2 = "SELECT SUM(Amount) AS DU FROM tickets WHERE SiteID = :site_id AND DateUpdated >= :start_date 
            AND DateUpdated < :end_date AND Status = 3;";
        $param2 = array(':start_date'=>$date, ':end_date'=>$enddate, ':site_id'=>$siteid);
        $this->exec3($sql2, $param2);
        $resultz2 = $this->find3();
        
        $result2 = $resultz2['DU'];
        
        $sql3 = "SELECT SUM(Amount) AS DE FROM tickets WHERE SiteID = :site_id AND DateEncashed >= :start_date 
            AND DateEncashed < :end_date;";
        $param3 = array(':start_date'=>$date, ':end_date'=>$enddate, ':site_id'=>$siteid);
        $this->exec3($sql3, $param3);
        $resultz3 = $this->find3();
        
        $result3 = $resultz3['DE'];
        
        
        $tickettotal = $result1 - $result2 - $result3;
        
        return $tickettotal;
    }
    
}

?>
