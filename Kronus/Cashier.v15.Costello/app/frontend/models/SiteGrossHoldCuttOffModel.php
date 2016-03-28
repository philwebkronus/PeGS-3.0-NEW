<?php
 

class SiteGrossHoldCuttOffModel extends MI_Model{
    
    public function getrunningActiveTickets($enddate, $siteid) {
        
        $sql = 'SELECT SUM(RunningActiveTickets) AS RunningActiveTicketsTotal FROM sitegrossholdcutoff WHERE SiteID = :site_id AND DateCutOff = :enddate';
        $param = array(':site_id'=>$siteid, ':enddate'=>$enddate);
        $this->exec($sql, $param);
        $result = $this->find();
        return $result['RunningActiveTicketsTotal'];
    }
    
}
?>
