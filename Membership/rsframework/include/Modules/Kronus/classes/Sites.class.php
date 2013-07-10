<?php

/*
 * @author : owliber
 * @date : 2013-04-30
 */

class Sites extends BaseEntity
{
    public function Sites()
    {
        $this->ConnString = "kronus";
        $this->TableName = "sites";
    }
    
    /*
     * Description: Get All sites
     * @author: gvjagolino
     * result: object array
     * DateCreated: 2013-07-01
     */
    public function getAllSite()
    {
        $query = "SELECT SiteID, SUBSTRING_INDEX(SUBSTRING_INDEX(SiteCode, '-', 2), '-', -1) 
            AS SiteCode FROM sites ORDER BY SiteCode ASC";
        
        return parent::RunQuery($query);
    }
    
    public function getSite( $siteid )
    {
        $where = " WHERE SiteID = $siteid";
        return parent::SelectByWhere($where);
    }
    
    public function getSiteByCode($sitecode)
    {
        $where = " where SiteCode = '$sitecode'";
        return parent::SelectByWhere($where);
    }
    
    
    /*
     * Description: Get only the Site Name by SiteID
     * @author: Junjun S. Hernandez
     * result: object array
     * DateCreated: 2013-07-01
     */
    public function getSiteName( $siteid )
    {
        $query = "SELECT SiteName FROM sites WHERE SiteID = $siteid";
        return parent::RunQuery($query);
    }
}
?>
