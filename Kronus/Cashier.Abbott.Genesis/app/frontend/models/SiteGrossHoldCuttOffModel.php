<?php
 

class SiteGrossHoldCuttOffModel extends MI_Model{
    
    public function getrunningActiveTickets($date, $enddate, $siteid) {
        
        $sql = 'SELECT SUM(RunningActiveTickets) AS RunningActiveTicketsTotal FROM npos.sitegrossholdcutoff WHERE SiteID = :site_id AND DateCutOff >= :startdate AND DateCutOff < :enddate';
        $param = array(':site_id'=>$siteid, ':startdate'=>$date, ':enddate'=>$enddate);
        $this->exec($sql, $param);
        $result = $this->find();
        return $result['RunningActiveTicketsTotal'];
    }
    
}
?>
