<?php

class TicketsModel extends MI_Model{
    
    public function getTicketCancelledAmount($ticketcodes){
        $sql = "SELECT Amount FROM tickets WHERE TicketCode IN ($ticketcodes);";
        $this->exec3($sql);
        $result = $this->findAll3();
        return $result;
    }
    
    
    public function getTicketUnusedAmount($ticketcodes){
        $sql = "SELECT Amount FROM tickets WHERE TicketCode IN ($ticketcodes) AND Status In (1,2);";
        $this->exec3($sql);
        $result = $this->findAll3();
        return $result;
    }
    
    public function getEncashedTickets($date, $enddate){
        $cutoff_time = Mirage::app()->param['cut_off'];
        $sql = "SELECT Amount FROM ticketencashment WHERE DateCreated >= :start_date 
            AND DateCreated < :end_date;";
        $param = array(':start_date'=>$date . ' ' . $cutoff_time, ':end_date'=>$enddate . ' ' . $cutoff_time);
        $this->exec3($sql, $param);
        $result = $this->findAll3();
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
