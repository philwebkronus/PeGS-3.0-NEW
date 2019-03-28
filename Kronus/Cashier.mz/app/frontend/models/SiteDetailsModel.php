<?php

/**
 * Date Created 02 20 , 18 1:50:09 AM <pre />
 * Description of SiteDetails
 * @author John Aaron Vida
 */
class SiteDetailsModel extends MI_Model{

    public function getSiteDetailsBySiteID($site_id) {
        $sql = 'SELECT * FROM sitedetails WHERE SiteID = :siteid';
        $param = array(':siteid'=>$site_id);
        $this->exec($sql,$param);
        return $this->find();
    }
    
}


